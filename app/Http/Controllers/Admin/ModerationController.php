<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;

/** Reported content queue + comment moderation. */
class ModerationController extends Controller
{
    public function reports(Request $request)
    {
        $status = $request->query('status', 'open');

        return view('admin.reports', [
            'reports' => Report::with(['reporter', 'reportable'])->where('status', $status)->latest()->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function resolve(Request $request, Report $report)
    {
        $data = $request->validate(['action' => ['required', 'in:dismiss,remove,resolve']]);

        if ($data['action'] === 'remove' && $report->reportable) {
            $report->reportable->delete();
        }

        $report->update([
            'status' => $data['action'] === 'dismiss' ? 'dismissed' : 'resolved',
            'resolved_by' => $request->user()->id,
        ]);

        AuditLog::record('report.'.$data['action'], $report);

        return back()->with('status', 'Report handled.');
    }

    public function comments(Request $request)
    {
        return view('admin.comments', [
            'comments' => Comment::with(['author', 'post'])->latest()->paginate(30),
        ]);
    }

    public function destroyComment(Comment $comment)
    {
        $comment->delete();
        AuditLog::record('comment.deleted', $comment);

        return back()->with('status', 'Comment deleted.');
    }
}
