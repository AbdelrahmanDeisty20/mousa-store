<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\pages\SavePageRequest;
use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('id')->paginate(10);

        return view('dashboard.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('dashboard.pages.create');
    }

    public function store(SavePageRequest $request)
    {
        Page::create($request->normalizedData());

        return redirect()
            ->route('pages.index')
            ->with('success', __('Page created successfully!'));
    }

    public function show(Page $page)
    {
        return redirect()->route('pages.edit', $page);
    }

    public function edit(Page $page)
    {
        return view('dashboard.pages.edit', compact('page'));
    }

    public function update(SavePageRequest $request, Page $page)
    {
        $page->update($request->normalizedData());

        return redirect()
            ->route('pages.index')
            ->with('success', __('Page updated successfully!'));
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()
            ->route('pages.index')
            ->with('success', __('Page deleted successfully!'));
    }
}
