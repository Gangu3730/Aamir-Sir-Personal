<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(File::get(database_path('seed/site.json')), true);

        // --- Super admin (credentials come from .env, never hard-coded) ---
        $email = env('ADMIN_EMAIL', 'admin@aamirsheeraz.com');
        $admin = User::firstOrNew(['email' => $email]);
        $admin->fill(['name' => env('ADMIN_NAME', 'Aamir Sheeraz'), 'phone' => env('ADMIN_PHONE')]);
        if (! $admin->exists) {
            $admin->password = env('ADMIN_PASSWORD') ?: \Illuminate\Support\Str::random(24);
        }
        $admin->forceFill(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_APPROVED, 'email_verified_at' => now()])->save();

        // --- CMS pages (design copied 1:1 from the live Elementor site) ---
        $titles = ['home' => 'Home', 'about-me' => 'About Me', 'professional' => 'Professional'];
        foreach ($titles as $slug => $title) {
            Page::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'content' => $data['pages'][$slug]['html'],
                'meta_title' => 'Amir Sheeraz',
            ]);
        }

        // --- Header / footer / popup fragments stored as editable settings ---
        foreach (['header', 'footer', 'popup', 'auth_intro'] as $part) {
            Setting::put("part_{$part}", $data['parts'][$part] ?? '');
        }

        // --- Defaults ---
        foreach ([
            'site_name' => 'Aamir Sheeraz',
            'member_posts_enabled' => '0',
            'member_posts_moderated' => '1',
            'send_rejection_email' => '0',
        ] as $k => $v) {
            if (Setting::get($k) === null) {
                Setting::put($k, $v);
            }
        }

        if (MenuItem::count() === 0) {
            foreach ([['Home', '/'], ['About Me', '/about-me'], ['Professional', '/professional']] as $i => [$label, $url]) {
                MenuItem::create(['label' => $label, 'url' => $url, 'position' => $i]);
            }
        }

        // --- Existing articles become the first private community posts ---
        foreach ($data['posts'] as $p) {
            Post::updateOrCreate(['slug' => $p['slug']], [
                'user_id' => $admin->id,
                'type' => 'article',
                'title' => $p['title'],
                'excerpt' => $p['excerpt'] ?: null,
                'body' => $p['body'],
                'featured_image' => $p['image'],
                'status' => 'published',
                'published_at' => $p['date'],
            ]);
        }
    }
}
