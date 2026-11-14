<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ApplicationApproved;
use App\Notifications\NewApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return tap(new User(['name' => 'Aamir', 'email' => 'aamir@example.com', 'password' => 'secret-pass-123']), fn ($u) => $u->forceFill(['role' => 'admin', 'status' => 'approved'])->save());
    }

    private function member(string $email = 'm@example.com'): User
    {
        return tap(new User(['name' => 'Mia Member', 'email' => $email, 'password' => 'secret-pass-123']), fn ($u) => $u->forceFill(['role' => 'member', 'status' => 'approved'])->save());
    }

    private function makePost(User $author, array $o = []): Post
    {
        return Post::create($o + ['user_id' => $author->id, 'title' => 'Hello', 'slug' => 'hello', 'body' => '<p>Body</p>', 'status' => 'published', 'published_at' => now()->subMinute()]);
    }

    public function test_application_creates_pending_user_and_alerts_admin(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->post('/registration', ['name' => 'Jane Doe', 'email' => 'jane@example.com', 'phone' => '+971 50 123 4567', 'country' => 'India', 'location' => 'Mumbai'])
            ->assertRedirect(route('apply.thanks'));

        $jane = User::where('email', 'jane@example.com')->first();
        $this->assertSame('pending', $jane->status);
        $this->assertSame('member', $jane->role);
        $this->assertNull($jane->password);
        Notification::assertSentTo($admin, NewApplication::class);
    }

    public function test_required_fields_are_enforced_and_honeypot_ignored(): void
    {
        $this->post('/registration', ['name' => 'x'])->assertSessionHasErrors(['email', 'phone', 'country', 'location']);
        $this->post('/registration', ['name' => 'Bot', 'email' => 'b@example.com', 'phone' => '123456', 'country' => 'India', 'location' => 'X', 'website' => 'spam']);
        $this->assertDatabaseMissing('users', ['email' => 'b@example.com']);
    }

    public function test_pending_user_cannot_log_in_or_enter_community(): void
    {
        $u = new User(['name' => 'P', 'email' => 'p@example.com', 'password' => 'secret-pass-123']);
        $u->forceFill(['status' => 'pending'])->save();

        $this->post('/login', ['email' => 'p@example.com', 'password' => 'secret-pass-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get('/community')->assertRedirect(route('login'));
    }

    public function test_admin_approval_sends_secure_link_and_member_can_set_password_and_login(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $applicant = new User(['name' => 'Jane', 'email' => 'jane@example.com', 'phone' => '123456']);
        $applicant->forceFill(['status' => 'pending'])->save();

        $this->actingAs($admin)->post(route('admin.applications.approve', $applicant))->assertRedirect();
        $applicant->refresh();
        $this->assertSame('approved', $applicant->status);

        $token = null;
        Notification::assertSentTo($applicant, ApplicationApproved::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        auth()->logout();
        $this->post('/reset-password', ['token' => $token, 'email' => 'jane@example.com', 'password' => 'NewPassw0rd!!', 'password_confirmation' => 'NewPassw0rd!!'])
            ->assertRedirect(route('login'));

        $this->post('/login', ['email' => 'jane@example.com', 'password' => 'NewPassw0rd!!'])->assertRedirect(route('community.index'));
        $this->assertAuthenticatedAs($applicant);
    }

    public function test_rejected_and_suspended_cannot_log_in(): void
    {
        foreach (['rejected', 'suspended'] as $i => $status) {
            $u = new User(['name' => $status, 'email' => "$status@example.com", 'password' => 'secret-pass-123']);
            $u->forceFill(['status' => $status])->save();
            $this->post('/login', ['email' => "$status@example.com", 'password' => 'secret-pass-123'])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_members_cannot_reach_admin_but_moderators_can(): void
    {
        $member = $this->member();
        $this->actingAs($member)->get('/admin')->assertForbidden();

        $mod = $this->member('mod@example.com');
        $mod->forceFill(['role' => 'moderator'])->save();
        $this->actingAs($mod)->get('/admin')->assertOk();
        $this->actingAs($mod)->get('/admin/settings')->assertForbidden(); // super admin only
    }

    public function test_community_is_private_noindex_and_lists_posts(): void
    {
        $admin = $this->admin();
        $this->makePost($admin);
        $this->get('/community/hello')->assertRedirect(route('login'));

        $this->actingAs($this->member())->get('/community')
            ->assertOk()->assertSee('Hello')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    public function test_threaded_comments_notifications_and_moderation(): void
    {
        $admin = $this->admin();
        $post = $this->makePost($admin);
        $mia = $this->member();

        $this->actingAs($mia)->post(route('community.comments.store', $post), ['body' => 'Great point'])->assertRedirect();
        $comment = Comment::first();
        $this->assertSame(1, $admin->notifications()->count()); // post author alerted

        $this->actingAs($admin)->post(route('community.comments.store', $post), ['body' => 'Thanks!', 'parent_id' => $comment->id]);
        $this->assertSame($comment->id, Comment::latest('id')->first()->parent_id);
        $this->assertSame(1, $mia->notifications()->count()); // reply alerted

        // a member can't delete someone else's comment, a moderator can
        $other = $this->member('o@example.com');
        $this->actingAs($other)->delete(route('community.comments.destroy', $comment))->assertForbidden();
        $this->actingAs($admin)->delete(route('community.comments.destroy', $comment))->assertRedirect();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_closed_discussions_reject_comments(): void
    {
        $post = $this->makePost($this->admin(), ['comments_open' => false]);
        $this->actingAs($this->member())->post(route('community.comments.store', $post), ['body' => 'hi'])->assertForbidden();
    }

    public function test_member_posts_follow_settings_and_moderation(): void
    {
        $mia = $this->member();
        $this->actingAs($mia)->get('/community/new')->assertForbidden();

        Setting::put('member_posts_enabled', '1');
        Setting::put('member_posts_moderated', '1');
        $this->actingAs($mia)->post('/community/new', ['title' => 'My idea', 'body' => 'Thoughts'])->assertRedirect();
        $this->assertSame('pending', Post::where('title', 'My idea')->first()->status);

        $this->actingAs($mia)->get('/community')->assertDontSee('My idea');
    }

    public function test_save_and_report(): void
    {
        $post = $this->makePost($this->admin());
        $mia = $this->member();

        $this->actingAs($mia)->post(route('community.save', $post))->assertRedirect();
        $this->assertTrue($mia->savedPosts()->where('posts.id', $post->id)->exists());

        $this->actingAs($mia)->post(route('community.report', $post), ['reason' => 'spam'])->assertRedirect();
        $this->assertDatabaseHas('reports', ['reportable_id' => $post->id, 'status' => 'open']);
    }

    public function test_cms_page_editing_is_admin_only(): void
    {
        $admin = $this->admin();
        \App\Models\Page::create(['slug' => 'about-me', 'title' => 'About', 'content' => '<p>old</p>']);

        $this->actingAs($this->member())->put('/admin/pages/about-me', ['title' => 'x'])->assertForbidden();
        $this->actingAs($admin)->put('/admin/pages/about-me', ['title' => 'About', 'content' => '<p>new</p>', 'is_published' => 1])->assertRedirect();
        $this->assertSame('<p>new</p>', \App\Models\Page::first()->content);
    }

    public function test_only_author_can_edit_comment_and_it_is_marked_edited(): void
    {
        $admin = $this->admin();
        $mia = $this->member();
        $post = $this->makePost($admin);
        $c = Comment::create(['post_id' => $post->id, 'user_id' => $mia->id, 'body' => 'old']);

        $this->actingAs($admin)->put(route('community.comments.update', $c), ['body' => 'hax'])->assertForbidden();
        $this->actingAs($mia)->put(route('community.comments.update', $c), ['body' => 'new text'])
            ->assertRedirect(route('community.show', $post).'#comment-'.$c->id);
        $this->get(route('community.show', $post))->assertOk();

        $c->refresh();
        $this->assertSame('new text', $c->body);
        $this->assertNotNull($c->edited_at);
    }

    public function test_profile_requires_country_and_location(): void
    {
        $mia = $this->member();
        $this->actingAs($mia)->put(route('community.profile.update'), ['name' => 'Mia', 'phone' => '123456'])
            ->assertSessionHasErrors(['country', 'location']);
        $this->actingAs($mia)->put(route('community.profile.update'), ['name' => 'Mia', 'phone' => '123456', 'country' => 'Qatar', 'location' => 'Doha'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Qatar', $mia->fresh()->country);
    }

    public function test_admin_post_form_loads_html_widget(): void
    {
        $this->actingAs($this->admin())->get(route('admin.posts.create'))
            ->assertOk()->assertSee('data-html-widget', false)->assertSee('html-widget.js', false);
    }
}
