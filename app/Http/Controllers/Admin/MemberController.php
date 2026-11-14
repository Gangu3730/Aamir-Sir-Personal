<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\ApplicationApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::where('status', '!=', User::STATUS_PENDING)
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%")))
            ->orderByRaw("FIELD(role,'admin','moderator','member')")->latest()->paginate(25)->withQueryString();

        return view('admin.members.index', compact('users', 'q'));
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        abort_if($user->id === $actor->id, 403, 'You cannot change your own role or status.');
        // Only admins can touch admins / promote moderators.
        abort_if($user->isAdmin() && ! $actor->isAdmin(), 403);

        $data = $request->validate([
            'action' => ['required', 'in:suspend,reactivate,make_moderator,make_member,resend_invite'],
        ]);

        match ($data['action']) {
            'suspend' => $user->forceFill(['status' => User::STATUS_SUSPENDED])->save(),
            'reactivate' => $user->forceFill(['status' => User::STATUS_APPROVED])->save(),
            'make_moderator' => abort_unless($actor->isAdmin(), 403) ?: $user->forceFill(['role' => User::ROLE_MODERATOR])->save(),
            'make_member' => abort_unless($actor->isAdmin(), 403) ?: $user->forceFill(['role' => User::ROLE_MEMBER])->save(),
            'resend_invite' => $user->notify(new ApplicationApproved(Password::broker()->createToken($user))),
        };

        AuditLog::record('member.'.$data['action'], $user);

        return back()->with('status', 'Member updated.');
    }
}
