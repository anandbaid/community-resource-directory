<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\StaticPageItems;
use App\Models\StaticPages;
use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class StaticPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $legacySlugs = $this->getLegacySlugs();

        return Inertia::render('StaticPages/Index', [
            'createUrl' => route('admin.static-pages.create'),
            'staticPages' => StaticPages::orderBy('id', 'desc')
                ->get()
                ->map(fn (StaticPages $page) => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'showInHeader' => (bool) $page->show_in_header,
                    'headerParent' => $page->header_parent,
                    'showInFooter' => (bool) $page->show_in_footer,
                    'status' => $page->status,
                    'editUrl' => route('admin.static-pages.edit', $page->id),
                    'destroyUrl' => route('admin.static-pages.destroy', $page->id),
                    // Pages the site's own routes depend on cannot be removed.
                    'deletable' => !in_array($page->slug, $legacySlugs, true),
                ])
                ->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('StaticPages/Form', [
            ...$this->formOptions(),
            'mode' => 'create',
            'submitUrl' => route('admin.static-pages.store'),
            'isLegacy' => false,
            'values' => [
                'title' => '',
                'status' => 'active',
                'show_in_header' => false,
                'header_parent' => '',
                'header_menu_description' => '',
                'header_order' => '',
                'show_in_footer' => false,
                'footer_order' => '',
            ],
            'pageContent' => ['html' => '', 'css' => ''],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'indexUrl' => route('admin.static-pages.index'),
            'assetUploadUrl' => route('admin.static-pages.assets'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'menuParents' => collect($this->getHeaderMenuParents())
                ->map(fn ($label, $key) => ['value' => (string) $key, 'label' => $label])
                ->values(),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
            'header_menu_description' => 'nullable|string|max:140',
            'header_order' => 'nullable|integer|min:0',
            'footer_order' => 'nullable|integer|min:0',
        ]);

        $staticPage = new StaticPages();
        $staticPage->title = $request->title;
        $staticPage->slug = $this->generateUniqueSlug($request->title);
        $staticPage->status = $request->status;
        $staticPage->show_in_header = (bool) $request->show_in_header;
        $staticPage->header_parent = $request->header_parent ?: null;
        $staticPage->header_menu_description = $request->header_menu_description ?: null;
        $staticPage->header_order = $request->header_order ?: null;
        $staticPage->show_in_footer = (bool) $request->show_in_footer;
        $staticPage->footer_order = $request->footer_order ?: null;
        $staticPage->save();

        $this->storePageContent($staticPage, $request);

        return to_route('admin.static-pages.index')->with('success', 'Static page added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $staticPage = StaticPages::findOrFail($id);

        if ($this->isCareerPage($staticPage)) {
            return $this->renderCareerEditor($staticPage);
        }

        $isLegacy = in_array($staticPage->slug, $this->getLegacySlugs(), true);

        return Inertia::render('StaticPages/Form', [
            ...$this->formOptions(),
            'mode' => 'edit',
            'submitUrl' => route('admin.static-pages.update', $staticPage->id),
            // Legacy pages have hand-written Blade templates keyed on their
            // slug, so their routing fields and the page builder are off limits;
            // they edit their rich-text blocks and repeatable items instead.
            'isLegacy' => $isLegacy,
            'values' => [
                'title' => $staticPage->title ?? '',
                'status' => $staticPage->status ?? 'active',
                'show_in_header' => (bool) $staticPage->show_in_header,
                'header_parent' => (string) ($staticPage->header_parent ?? ''),
                'header_menu_description' => $staticPage->header_menu_description ?? '',
                'header_order' => $staticPage->header_order ?? '',
                'show_in_footer' => (bool) $staticPage->show_in_footer,
                'footer_order' => $staticPage->footer_order ?? '',
                'description' => $staticPage->description ?? '',
                'content_1' => $staticPage->content_1 ?? '',
                'content_2' => $staticPage->content_2 ?? '',
                'content_3' => $staticPage->content_3 ?? '',
                'content_4' => $staticPage->content_4 ?? '',
            ],
            'items' => $isLegacy
                ? StaticPageItems::where('page_id', $staticPage->id)
                    ->orderBy('order')
                    ->get()
                    ->map(fn (StaticPageItems $item) => [
                        'id' => $item->id,
                        'title' => $item->title ?? '',
                        'sub_title' => $item->sub_title ?? '',
                        'description' => $item->description ?? '',
                        'link' => $item->link ?? '',
                        'order' => $item->order ?? 0,
                        'image_existing' => $item->image ?? '',
                        'image_url' => $item->image ? asset($item->image) : '',
                        'image' => null,
                        'delete' => 0,
                    ])
                    ->values()
                : [],
            'pageContent' => $isLegacy
                ? ['html' => '', 'css' => '']
                : $this->loadPageContent($staticPage),
        ]);
    }

    private function renderCareerEditor(StaticPages $staticPage)
    {
        $defaults = [
            1 => ['text' => 'General Job Search Information', 'image' => 'assets/img/segment-3.png'],
            2 => ['text' => 'Where Can I Locate Job Openings', 'image' => 'assets/img/segment-4.jpg'],
            3 => ['text' => 'Interview Process & Success On The Job', 'image' => 'assets/img/segment-2.png'],
            4 => ['text' => 'How To Market Yourself', 'image' => 'assets/img/segment-1.jpg'],
        ];

        $topicsBySegment = $this->getCareerTopicsBySegment($staticPage->id);
        $segments = [];

        for ($i = 1; $i <= 4; $i++) {
            $current = $this->decodeCareerSegment((string) $staticPage->{"content_$i"});
            $image = !empty($current['image']) ? $current['image'] : $defaults[$i]['image'];

            $segments[] = [
                'index' => $i,
                'text' => !empty($current['text']) ? $current['text'] : $defaults[$i]['text'],
                'description' => $current['description'] ?? '',
                'imageUrl' => asset($image),
                'topics' => collect($topicsBySegment[$i] ?? [])
                    ->map(fn ($topic) => [
                        'id' => $topic->id,
                        'title' => $topic->title ?? '',
                        'description' => $topic->description ?? '',
                        'order' => $topic->order ?? 0,
                        'image_existing' => $topic->image ?? '',
                        'image_url' => $topic->image ? asset($topic->image) : '',
                        'image' => null,
                        'delete' => 0,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return Inertia::render('StaticPages/Career', [
            'indexUrl' => route('admin.static-pages.index'),
            'submitUrl' => route('admin.static-pages.update', $staticPage->id),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'values' => [
                'title' => $staticPage->title ?? '',
                'description' => $staticPage->description ?? '',
            ],
            'segments' => $segments,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $staticPage = StaticPages::findOrFail($id);

        if ($this->isCareerPage($staticPage)) {
            return $this->updateCareerPage($request, $staticPage);
        }

        $isLegacy = in_array($staticPage->slug, $this->getLegacySlugs(), true);
        $rules = ['title' => 'required|string|max:100'];

        if (!$isLegacy) {
            $rules['status'] = 'required|in:active,inactive';
            $rules['header_menu_description'] = 'nullable|string|max:140';
            $rules['header_order'] = 'nullable|integer|min:0';
            $rules['footer_order'] = 'nullable|integer|min:0';
        }

        $request->validate($rules);

        {
            try {
                $staticPage->title = $request->title;
                if (!$isLegacy) {
                    $staticPage->slug = $this->generateUniqueSlug($request->title, $staticPage->id);
                }
                if (!$isLegacy) {
                    $staticPage->status = $request->status;
                    $staticPage->show_in_header = (bool) $request->show_in_header;
                    $staticPage->header_parent = $request->header_parent ?: null;
                    $staticPage->header_menu_description = $request->header_menu_description ?: null;
                    $staticPage->header_order = $request->header_order ?: null;
                    $staticPage->show_in_footer = (bool) $request->show_in_footer;
                    $staticPage->footer_order = $request->footer_order ?: null;
                }
                if ($isLegacy) {
                    $this->fillLegacyFields($staticPage, $request);
                }
                $staticPage->save();

                if ($isLegacy) {
                    $this->syncLegacyItems($request, $staticPage);
                }

                if (!$isLegacy) {
                    $this->storePageContent($staticPage, $request);
                }

                return to_route('admin.static-pages.index')
                    ->with('success', 'Static page updated successfully');
            } catch (\Exception $e) {
                return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }
    }

    private function updateCareerPage(Request $request, StaticPages $staticPage)
    {
        $rules = [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string',
        ];
        for ($i = 1; $i <= 4; $i++) {
            $rules["segment_items.$i.text"] = 'nullable|string|max:255';
            $rules["segment_items.$i.description"] = 'nullable|string';
            $rules["segment_items.$i.image"] = 'nullable|image|max:2048';
        }
        $rules['topic_items.*.*.title'] = 'nullable|string|max:255';
        $rules['topic_items.*.*.description'] = 'nullable|string';
        $rules['topic_items.*.*.order'] = 'nullable|integer|min:0';
        $rules['topic_items.*.*.image'] = 'nullable|image|max:2048';
        $request->validate($rules);

        // Two cross-field checks the rule array cannot express: every segment
        // needs text and a picture, and every kept topic needs a title.
        foreach ([
            'segment_items' => $this->validateCareerSegmentItems($request, $staticPage),
            'topic_items' => $this->validateCareerTopicItems($request),
        ] as $field => $error) {
            if ($error) {
                return back()->withErrors([$field => $error]);
            }
        }

        try {
            $staticPage->title = $request->title;
            $staticPage->description = $request->input('description', '');
            for ($i = 1; $i <= 4; $i++) {
                $current = $this->decodeCareerSegment((string) $staticPage->{"content_$i"});
                $file = $request->file("segment_items.$i.image");
                $imagePath = $current['image'] ?? '';

                if ($file) {
                    if (!empty($imagePath)) {
                        CommonFunction::fileDeleteStorage($imagePath);
                    }
                    $imagePath = CommonFunction::fileUploadStorage($file, 'static-pages', "career-segment-$i");
                }

                $payload = [
                    'text' => trim((string) $request->input("segment_items.$i.text", $current['text'] ?? '')),
                    'description' => (string) $request->input("segment_items.$i.description", $current['description'] ?? ''),
                    'image' => $imagePath,
                ];
                $staticPage->{"content_$i"} = json_encode($payload);
            }
            $staticPage->save();
            $this->syncCareerTopics($request, $staticPage);

            return to_route('admin.static-pages.edit', $staticPage->id)
                ->with('success', 'Career page updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $staticPage = StaticPages::findOrFail($id);

        if (in_array($staticPage->slug, $this->getLegacySlugs(), true)) {
            return back()->with('error', 'Legacy static pages cannot be deleted.');
        }

        $pageSlug = $staticPage->slug;
        $pageTitle = $staticPage->title;
        $contentPath = $staticPage->content_path;

        $itemImages = StaticPageItems::where('page_id', $staticPage->id)->pluck('image')->all();
        StaticPageItems::where('page_id', $staticPage->id)->delete();

        foreach ($itemImages as $imagePath) {
            if (!empty($imagePath)) {
                CommonFunction::fileDeleteStorage($imagePath);
            }
        }

        if (!empty($contentPath)) {
            Storage::disk('public')->delete($contentPath);
        }

        $banners = Banners::where('page_slug', $pageSlug)
            ->orWhere('page_title', $pageTitle)
            ->get();
        foreach ($banners as $banner) {
            if (!empty($banner->image)) {
                CommonFunction::deleteUploadedImages($banner->image);
            }
            $banner->delete();
        }

        $staticPage->delete();

        return to_route('admin.static-pages.index')
            ->with('success', 'Static page deleted successfully.');
    }

    public function uploadAsset(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 422);
        }
        $files = $request->file('file');
        if (!is_array($files)) {
            $files = [$files];
        }

        $data = [];
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $path = CommonFunction::fileUploadStorage($file, 'static-pages', 'asset');
            $data[] = ['src' => asset($path)];
        }

        if (empty($data)) {
            return response()->json(['error' => 'No valid file uploaded'], 422);
        }

        return response()->json(['data' => $data]);
    }

    public function homeSections()
    {
        $settings = SiteSettings::whereIn('settings_name', $this->getHomeSectionKeys())
            ->pluck('settings_value', 'settings_name');

        $imageKeys = $this->getHomeSectionImageKeys();
        $values = [];
        $images = [];

        foreach ($this->getHomeSectionKeys() as $key) {
            if (in_array($key, $imageKeys, true)) {
                $images[$key] = !empty($settings[$key]) ? asset($settings[$key]) : '';

                continue;
            }

            $values[$key] = $settings[$key] ?? '';
        }

        return Inertia::render('Admin/HomeSections', [
            'submitUrl' => route('admin.home-sections.save'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'values' => $values,
            'images' => $images,
            // The four "What We Do" cards and four Career Hub icons were eight
            // hand-written blocks in the Blade view; the shape is the same for
            // each, so the page renders them from this.
            'whatWeDoItems' => collect(range(1, 4))->map(fn (int $i) => [
                'index' => $i,
                'title' => "home_what_we_do_item_{$i}_title",
                'desc' => "home_what_we_do_item_{$i}_desc",
                'image' => "home_what_we_do_item_{$i}_image",
                'icon' => "home_what_we_do_item_{$i}_icon",
                'link' => "home_what_we_do_item_{$i}_link",
            ])->values(),
            'careerIcons' => collect(range(1, 4))->map(fn (int $i) => [
                'index' => $i,
                'title' => "home_career_success_hub_icon_{$i}_title",
                'image' => "home_career_success_hub_icon_{$i}_image",
            ])->values(),
        ]);
    }

    public function saveHomeSections(Request $request)
    {
        $imageKeys = $this->getHomeSectionImageKeys();

        $request->validate(
            collect($imageKeys)->mapWithKeys(fn (string $key) => [$key => 'nullable|image|max:1024'])->all(),
        );

        try {
            $posted = $request->input('key', []);

            foreach ($this->getHomeSectionKeys() as $key) {
                if (in_array($key, $imageKeys, true)) {
                    continue;
                }

                SiteSettings::updateOrInsert(
                    ['settings_name' => $key],
                    ['settings_value' => $posted[$key] ?? '', 'updated_at' => now()],
                );
            }

            foreach ($imageKeys as $imageKey) {
                if (!$request->hasFile($imageKey)) {
                    continue;
                }

                $existing = SiteSettings::where('settings_name', $imageKey)->value('settings_value');
                $path = CommonFunction::fileUploadStorage($request->file($imageKey), 'home', $imageKey);

                if (empty($path)) {
                    continue;
                }

                if (!empty($existing)) {
                    CommonFunction::fileDeleteStorage($existing);
                }

                SiteSettings::updateOrInsert(
                    ['settings_name' => $imageKey],
                    ['settings_value' => $path, 'updated_at' => now()],
                );
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update home page sections. ' . $e->getMessage());
        }

        return to_route('admin.home-sections')->with('success', 'Home page sections updated successfully.');
    }

    private function getHomeSectionKeys(): array
    {
        return [
            'home_resource_block_1',
            'home_resource_block_2',
            'home_what_we_do_block',
            'home_what_we_do_item_1_title',
            'home_what_we_do_item_1_desc',
            'home_what_we_do_item_1_image',
            'home_what_we_do_item_1_icon',
            'home_what_we_do_item_1_link',
            'home_what_we_do_item_2_title',
            'home_what_we_do_item_2_desc',
            'home_what_we_do_item_2_image',
            'home_what_we_do_item_2_icon',
            'home_what_we_do_item_2_link',
            'home_what_we_do_item_3_title',
            'home_what_we_do_item_3_desc',
            'home_what_we_do_item_3_image',
            'home_what_we_do_item_3_icon',
            'home_what_we_do_item_3_link',
            'home_what_we_do_item_4_title',
            'home_what_we_do_item_4_desc',
            'home_what_we_do_item_4_image',
            'home_what_we_do_item_4_icon',
            'home_what_we_do_item_4_link',
            'home_shop_block',
            'home_about_block',
            'home_about_image',
            'home_career_success_hub_block',
            'home_career_success_hub_icon_1_title',
            'home_career_success_hub_icon_1_image',
            'home_career_success_hub_icon_2_title',
            'home_career_success_hub_icon_2_image',
            'home_career_success_hub_icon_3_title',
            'home_career_success_hub_icon_3_image',
            'home_career_success_hub_icon_4_title',
            'home_career_success_hub_icon_4_image',
            'home_career_success_hub_image',
            'home_support_block',
        ];
    }

    private function getHomeSectionImageKeys(): array
    {
        return [
            'home_what_we_do_item_1_image',
            'home_what_we_do_item_1_icon',
            'home_what_we_do_item_2_image',
            'home_what_we_do_item_2_icon',
            'home_what_we_do_item_3_image',
            'home_what_we_do_item_3_icon',
            'home_what_we_do_item_4_image',
            'home_what_we_do_item_4_icon',
            'home_about_image',
            'home_career_success_hub_icon_1_image',
            'home_career_success_hub_icon_2_image',
            'home_career_success_hub_icon_3_image',
            'home_career_success_hub_icon_4_image',
            'home_career_success_hub_image',
        ];
    }


    public function resources()
    {
        return $this->renderIntroEditor('resource_block', 'Resources Sections', 'Resource Directory', route('admin.resources.save'));
    }

    public function saveResources(Request $request)
    {
        return $this->saveIntroEditor($request, 'resource_block', 'admin.resources', 'Resources updated successfully.');
    }

    public function library()
    {
        return $this->renderIntroEditor('library_block', 'Library Sections', 'Library Content', route('admin.library.save'));
    }

    public function saveLibrary(Request $request)
    {
        return $this->saveIntroEditor($request, 'library_block', 'admin.library', 'Library content updated successfully.');
    }

    /**
     * The library and resources screens are one rich-text block each. They were
     * two Blade views identical apart from the setting name and the headings.
     */
    private function renderIntroEditor(string $setting, string $title, string $blockLabel, string $submitUrl)
    {
        return Inertia::render('Admin/IntroEditor', [
            'title' => $title,
            'blockLabel' => $blockLabel,
            'submitUrl' => $submitUrl,
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'field' => $setting,
            'content' => SiteSettings::where('settings_name', $setting)->value('settings_value') ?? '',
        ]);
    }

    private function saveIntroEditor(Request $request, string $setting, string $route, string $message)
    {
        $request->validate([$setting => 'nullable|string']);

        try {
            SiteSettings::updateOrInsert(
                ['settings_name' => $setting],
                ['settings_value' => $request->input($setting, ''), 'updated_at' => now()],
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to save. ' . $e->getMessage());
        }

        return to_route($route)->with('success', $message);
    }

    private function getHeaderMenuParents(): array
    {
        return $GLOBALS['STATIC_PAGE_HEADER_MENU_PARENTS'] ?? [];
    }

    private function getReservedSlugs(): array
    {
        return [
            '',
            'admin',
            'login',
            'logout',
            'register',
            'password-reset',
            'search-resources',
            'organization-details',
            'library',
            'saved-resources-view',
            'saved-search-view',
            'suggest-new-resources',
            'suggest-existing-resources',
            'dashboard',
            'profile',
            ...$this->getLegacySlugs(),
        ];
    }

    private function getLegacySlugs(): array
    {
        return $GLOBALS['LEGACY_STATIC_PAGE_SLUGS'] ?? [];
    }

    private function isCareerPage(StaticPages $staticPage): bool
    {
        return $staticPage->slug === 'career-success-hub';
    }

    private function fillLegacyFields(StaticPages $staticPage, Request $request): void
    {
        $staticPage->description = $request->input('description', '');
        $staticPage->content_1 = $request->input('content_1', '');
        $staticPage->content_2 = $request->input('content_2', '');
        $staticPage->content_3 = $request->input('content_3', '');
        $staticPage->content_4 = $request->input('content_4', '');
    }

    private function syncLegacyItems(Request $request, StaticPages $staticPage): void
    {
        $items = $request->input('items', []);
        $itemFiles = $request->file('items', []);
        foreach ($items as $index => $itemData) {
            $delete = ($itemData['delete'] ?? '0') === '1';
            $itemId = $itemData['id'] ?? null;
            if ($delete) {
                if ($itemId) {
                    $existing = StaticPageItems::find($itemId);
                    if ($existing) {
                        if (!empty($existing->image)) {
                            CommonFunction::fileDeleteStorage($existing->image);
                        }
                        $existing->delete();
                    }
                }
                continue;
            }

            $item = $itemId ? StaticPageItems::find($itemId) : new StaticPageItems();
            if (!$item) {
                $item = new StaticPageItems();
            }
            $item->page_id = $staticPage->id;
            $item->title = $itemData['title'] ?? '';
            $item->sub_title = $itemData['sub_title'] ?? '';
            $item->description = $itemData['description'] ?? '';
            $item->link = $itemData['link'] ?? '';
            $item->order = $itemData['order'] ?? 0;

            $file = $itemFiles[$index]['image'] ?? null;
            if ($file) {
                if (!empty($item->image)) {
                    CommonFunction::fileDeleteStorage($item->image);
                }
                $item->image = CommonFunction::fileUploadStorage($file, 'static-items', 'static-item');
            } elseif (!empty($itemData['image_existing']) && empty($item->image)) {
                $item->image = $itemData['image_existing'];
            }

            $item->save();
        }
    }

    private function decodeCareerSegment(?string $raw): array
    {
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            return ['text' => '', 'description' => '', 'image' => ''];
        }
        return [
            'text' => (string) ($data['text'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'image' => (string) ($data['image'] ?? ''),
        ];
    }

    private function getCareerTopicsBySegment(int $pageId): array
    {
        $segmentKeys = $this->getCareerSegmentKeys();
        $topics = StaticPageItems::where('page_id', $pageId)
            ->whereIn('sub_title', $segmentKeys)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $grouped = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
        ];

        foreach ($topics as $topic) {
            $segment = (int) str_replace('segment_', '', (string) $topic->sub_title);
            if ($segment < 1 || $segment > 4) {
                continue;
            }
            $grouped[$segment][] = $topic;
        }

        return $grouped;
    }

    private function syncCareerTopics(Request $request, StaticPages $staticPage): void
    {
        $topicItems = $request->input('topic_items', []);
        $topicFiles = $request->file('topic_items', []);
        $keepIds = [];

        for ($segment = 1; $segment <= 4; $segment++) {
            $segmentItems = $topicItems[$segment] ?? [];
            foreach ($segmentItems as $topicIndex => $topicData) {
                $delete = ($topicData['delete'] ?? '0') === '1';
                $topicId = $topicData['id'] ?? null;
                $topic = $topicId
                    ? StaticPageItems::where('page_id', $staticPage->id)->where('id', $topicId)->first()
                    : null;

                if ($delete) {
                    if ($topic) {
                        if (!empty($topic->image)) {
                            CommonFunction::fileDeleteStorage($topic->image);
                        }
                        $topic->delete();
                    }
                    continue;
                }

                if (!$topic) {
                    $topic = new StaticPageItems();
                }

                $topic->page_id = $staticPage->id;
                $topic->sub_title = "segment_$segment";
                $topic->title = trim((string) ($topicData['title'] ?? ''));
                $topic->description = (string) ($topicData['description'] ?? '');
                $topic->order = (int) ($topicData['order'] ?? 0);
                $topic->link = '';

                $file = $topicFiles[$segment][$topicIndex]['image'] ?? null;
                if ($file) {
                    if (!empty($topic->image)) {
                        CommonFunction::fileDeleteStorage($topic->image);
                    }
                    $topic->image = CommonFunction::fileUploadStorage($file, 'static-items', 'career-topic');
                } elseif (!empty($topicData['image_existing']) && empty($topic->image)) {
                    $topic->image = $topicData['image_existing'];
                }

                $topic->save();
                $keepIds[] = $topic->id;
            }
        }

        $segmentKeys = $this->getCareerSegmentKeys();
        $staleTopics = StaticPageItems::where('page_id', $staticPage->id)
            ->whereIn('sub_title', $segmentKeys)
            ->when(!empty($keepIds), function ($query) use ($keepIds) {
                $query->whereNotIn('id', $keepIds);
            })
            ->get();

        foreach ($staleTopics as $staleTopic) {
            if (!empty($staleTopic->image)) {
                CommonFunction::fileDeleteStorage($staleTopic->image);
            }
            $staleTopic->delete();
        }
    }

    private function validateCareerTopicItems(Request $request): ?string
    {
        $topicItems = $request->input('topic_items', []);
        $topicFiles = $request->file('topic_items', []);
        for ($segment = 1; $segment <= 4; $segment++) {
            $segmentItems = $topicItems[$segment] ?? [];
            foreach ($segmentItems as $topicIndex => $topicData) {
                $delete = ($topicData['delete'] ?? '0') === '1';
                if ($delete) {
                    continue;
                }

                $title = trim((string) ($topicData['title'] ?? ''));
                if ($title === '') {
                    return "Topic title is required in Segment $segment.";
                }

                $description = (string) ($topicData['description'] ?? '');
                if ($this->isRichTextEmpty($description)) {
                    return "Topic description is required in Segment $segment.";
                }

                $existingImage = trim((string) ($topicData['image_existing'] ?? ''));
                $file = $topicFiles[$segment][$topicIndex]['image'] ?? null;
                if (empty($existingImage) && !$file) {
                    return "Topic image is required in Segment $segment.";
                }
            }
        }

        return null;
    }

    private function validateCareerSegmentItems(Request $request, StaticPages $staticPage): ?string
    {
        for ($segment = 1; $segment <= 4; $segment++) {
            $current = $this->decodeCareerSegment((string) $staticPage->{"content_$segment"});
            $text = trim((string) $request->input("segment_items.$segment.text", $current['text'] ?? ''));
            if ($text === '') {
                return "Segment $segment text is required.";
            }
        }

        return null;
    }

    private function isRichTextEmpty(?string $value): bool
    {
        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace(['&nbsp;', '&#160;'], ' ', $decoded);
        return trim(strip_tags($decoded)) === '';
    }

    private function getCareerSegmentKeys(): array
    {
        return ['segment_1', 'segment_2', 'segment_3', 'segment_4'];
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $reserved = $this->getReservedSlugs();
        $counter = 1;
        while (
            in_array($slug, $reserved, true) ||
            StaticPages::where('slug', $slug)->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            })->exists()
        ) {
            $counter++;
            $slug = $base . '-' . $counter;
        }
        return $slug;
    }

    private function storePageContent(StaticPages $page, Request $request): void
    {
        $content = [
            'html' => $request->input('gjs-html', ''),
            'css' => $request->input('gjs-css', ''),
            'components' => $request->input('gjs-components', ''),
            'styles' => $request->input('gjs-styles', ''),
        ];
        $path = 'static-pages/page-' . $page->id . '.json';
        Storage::disk('public')->put($path, json_encode($content));
        $page->content_path = $path;
        $page->save();
    }

    private function loadPageContent(?StaticPages $page): array
    {
        if (!$page || empty($page->content_path)) {
            return [
                'html' => '',
                'css' => '',
                'components' => '',
                'styles' => '',
            ];
        }
        if (!Storage::disk('public')->exists($page->content_path)) {
            return [
                'html' => '',
                'css' => '',
                'components' => '',
                'styles' => '',
            ];
        }
        $raw = Storage::disk('public')->get($page->content_path);
        $data = json_decode($raw, true) ?: [];
        return [
            'html' => $data['html'] ?? '',
            'css' => $data['css'] ?? '',
            'components' => $data['components'] ?? '',
            'styles' => $data['styles'] ?? '',
        ];
    }
}
