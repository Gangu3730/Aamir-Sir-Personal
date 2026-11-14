<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'pending' => User::pending()->count(),
            'members' => User::where('status', 'approved')->count(),
            'posts' => Post::where('status', 'published')->count(),
            'awaitingPosts' => Post::where('status', 'pending')->count(),
            'comments' => Comment::count(),
            'openReports' => Report::where('status', 'open')->count(),
            'latestApplicants' => User::pending()->latest()->take(5)->get(),
            'activity' => AuditLog::with('user')->latest('created_at')->take(10)->get(),
        ]);
    }
}
