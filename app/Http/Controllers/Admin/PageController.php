<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\Request;

/** The CMS: edit the public pages (Home, About Me, Professional...). */
class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('id')->get()]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $page->update($data + ['is_published' => $request->boolean('is_published')]);
        AuditLog::record('page.updated', $page);

        return back()->with('status', 'Page saved.');
    }
}
