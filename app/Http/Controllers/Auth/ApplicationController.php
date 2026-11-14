<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\NewApplication;
use Illuminate\Http\Request;
use App\Support\Countries;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/** Public "Apply for access" form. Creates a Pending account - no community access yet. */
class ApplicationController extends Controller
{
    public function create()
    {
        return view('auth.apply');
    }

    public function store(Request $request)
    {
        // Honeypot: real users leave this empty.
        if ($request->filled('website')) {
            return redirect()->route('apply.thanks');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+\-\s().]+$/'],
            'profession' => ['nullable', 'string', 'max:190'],
            'company' => ['nullable', 'string', 'max:190'],
            'country' => ['required', 'string', Rule::in(Countries::all())],
            'location' => ['required', 'string', 'max:190'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            // Do not reveal account state to the public; same screen either way.
            if ($existing->status === User::STATUS_REJECTED) {
                $existing->fill($data)->forceFill(['status' => User::STATUS_PENDING, 'reviewed_at' => null])->save();
                $this->notifyAdmins($existing);
            }

            return redirect()->route('apply.thanks');
        }

        $user = new User($data);
        $user->forceFill(['role' => User::ROLE_MEMBER, 'status' => User::STATUS_PENDING])->save();

        AuditLog::record('application.submitted', $user);
        $this->notifyAdmins($user);

        return redirect()->route('apply.thanks');
    }

    public function thanks()
    {
        return view('auth.apply-thanks');
    }

    private function notifyAdmins(User $applicant): void
    {
        $admins = User::where('role', User::ROLE_ADMIN)->where('status', User::STATUS_APPROVED)->get();
        Notification::send($admins, new NewApplication($applicant));
    }
}
