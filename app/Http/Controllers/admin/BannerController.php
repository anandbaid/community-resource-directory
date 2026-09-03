<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\StaticPages;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banners::orderBy('id', 'desc')->paginate(10);

        return Inertia::render('Banners/Index', [
            'banners' => $banners->through(fn (Banners $banner) => [
                'id' => $banner->id,
                'page_title' => $banner->page_title,
                'status' => $banner->status,
                'imageUrl' => $banner->image ? asset($banner->image) : asset('assets/img/placeholder.png'),
                'editUrl' => route('admin.banner.edit', $banner->id),
                'deleteUrl' => route('admin.banner.destroy', $banner->id),
            ]),
            'createUrl' => route('admin.banner.create'),
        ]);
    }

    /**
     * The fixed page list from app/Helpers/helpers.php plus every dynamic static
     * page, keyed title => slug. Used by create, store and edit alike.
     *
     * @return array<string, string>
     */
    private function bannerPages(): array
    {
        $pages = $GLOBALS['PAGES_FOR_BANNERS'] ?? [];

        foreach (StaticPages::orderBy('title')->get(['title', 'slug']) as $page) {
            if (!array_key_exists($page->title, $pages)) {
                $pages[$page->title] = $page->slug;
            }
        }

        return $pages;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Banners/Form', [
            'type' => 'Create',
            'submitUrl' => route('admin.banner.store'),
            'indexUrl' => route('admin.banner.index'),
            'pages' => array_keys($this->bannerPages()),
            'values' => $this->formValues(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $pages = $this->bannerPages();

        $request->validate([
            'page_title' => ['required', Rule::in(array_keys($pages))],
            'banner_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $banner = new Banners();
            $banner->page_title = $request->page_title;
            $banner->page_slug = $pages[$request->page_title];
            if ($request->page_title == 'Home') {
                $banner->banner_heading = $request->banner_heading;
                $banner->banner_text = $request->banner_text;
            }
            $banner->status = $request->status;
            $banner->order = $request->banner_order ?? 99;
            $banner->save();

            if ($request->hasFile('banner_image')) {
                $file = CommonFunction::fileUploadResize($request->file('banner_image'), 'banner', $banner->id . '-image-');
                if (!empty($file)) {
                    $banner->image = $file;
                }
            }
            $banner->save();

            return to_route('admin.banner.index')->with('success', 'Banner Details added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Banners have no read-only screen; keep the resource route useful.
     */
    public function show(string $id)
    {
        return to_route('admin.banner.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $banner = Banners::findOrFail($id);

        return Inertia::render('Banners/Form', [
            'type' => 'Edit',
            'submitUrl' => route('admin.banner.update', $banner->id),
            'indexUrl' => route('admin.banner.index'),
            'pages' => array_keys($this->bannerPages()),
            'imageUrl' => $banner->image ? asset($banner->image) : '',
            'values' => $this->formValues($banner),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'page_title' => ['required', Rule::in(array_keys($this->bannerPages()))],
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $banner = Banners::findOrFail($id);
            $banner->page_title = $request->page_title;
            if ($request->page_title == "Home") {
                $banner->banner_heading = $request->banner_heading ?? '';
                $banner->banner_text = $request->banner_text ?? '';
            } else {
                $banner->banner_heading = '';
                $banner->banner_text = '';
            }
            $banner->status = $request->status;
            $banner->order = $request->banner_order ?? $banner->order;
            $banner->save();

            if ($request->hasFile('banner_image')) {
                $file = CommonFunction::fileUploadResize($request->file('banner_image'), 'banner', $banner->id . '-image-');
                if (!empty($file)) {
                    CommonFunction::deleteUploadedImages($banner->image);
                    $banner->image = $file;
                }
            }
            $banner->save();

            return to_route('admin.banner.index')->with('success', 'Banner Details updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(?Banners $banner = null): array
    {
        return [
            'page_title' => $banner->page_title ?? '',
            'banner_heading' => $banner->banner_heading ?? '',
            'banner_text' => $banner->banner_text ?? '',
            'status' => $banner->status ?? 'active',
            'banner_order' => $banner->order ?? '',
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $banner = Banners::find($id);

            if (!$banner) {
                return back()->with('error', 'Banner not found');
            }

            CommonFunction::deleteUploadedImages($banner->image);
            $banner->delete();

            return back()->with('success', 'Banner details deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }
}
