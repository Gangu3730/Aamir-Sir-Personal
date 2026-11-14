<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ApplicationApproved;
use App\Notifications\ApplicationRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        return view('admin.applications.index', [
            'users' => User::where('status', $status)->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => User::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function show(User $user)
    {
        return view('admin.applications.show', ['user' => $user]);
    }

    public function approve(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['nullable', 'in:member,moderator']]);

        $user->forceFill([
            'status' => User::STATUS_APPROVED,
            'role' => $data['role'] ?? User::ROLE_MEMBER,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        // Secure invitation: expiring token link, never a plain-text password.
        $token = Password::broker()->createToken($user);
        $user->notify(new ApplicationApproved($token));

        AuditLog::record('application.approved', $user, ['role' => $user->role]);

        return redirect()->route('admin.applications.index')->with('status', "{$user->name} approved - invitation email sent.");
    }

    public function reject(Request $request, User $user)
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:1000']]);

        $user->forceFill([
            'status' => User::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_note' => $data['review_note'] ?? null,
        ])->save();

        if (Setting::bool('send_rejection_email', false)) {
            $user->notify(new ApplicationRejected);
        }

        AuditLog::record('application.rejected', $user);

        return redirect()->route('admin.applications.index')->with('status', "{$user->name}'s application was rejected.");
    }
}
