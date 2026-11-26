<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Return active policy/CMS pages localized by Accept-Language.
     */
    public function index()
    {
        $pages = Page::where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn (Page $page) => $page->toLocalizedPayload(includeSections: false))
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $pages,
        ]);
    }

    /**
     * Return a single active policy/CMS page by slug localized by Accept-Language.
     */
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $page) {
            return response()->json([
                'success' => false,
                'message' => __('Page not found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $page->toLocalizedPayload(),
        ]);
    }
}
