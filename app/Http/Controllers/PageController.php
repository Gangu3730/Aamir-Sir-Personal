<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;

/** Public, CMS-driven pages: Home, About Me, Professional ... */
class PageController extends Controller
{
    public function home()
    {
        return $this->render(Page::where('slug', 'home')->firstOrFail());
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->first();

        if ($page) {
            return $this->render($page);
        }

        // Legacy WordPress article URLs (/post-slug) now live inside the private community.
        if ($post = Post::where('slug', $slug)->first()) {
            return redirect()->route('community.show', $post);
        }

        abort(404);
    }

    private function render(Page $page)
    {
        return view('site.page', ['page' => $page]);
    }
}
