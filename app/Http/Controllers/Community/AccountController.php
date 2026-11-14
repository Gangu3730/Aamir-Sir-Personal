<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('community.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'profession' => ['nullable', 'string', 'max:190'],
            'company' => ['nullable', 'string', 'max:190'],
            'country' => ['required', 'string', Rule::in(Countries::all())],
            'location' => ['required', 'string', 'max:190'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $request->password]);

        return back()->with('status', 'Password changed.');
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $items = $user->notifications()->latest()->paginate(20);
        $user->unreadNotifications->markAsRead();

        return view('community.notifications', ['items' => $items]);
    }
}
