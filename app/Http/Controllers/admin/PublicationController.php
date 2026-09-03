<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\Organizations;
use App\Models\Publications;
use App\Models\States;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publications = Publications::orderBy('id', 'DESC')->get();
        $stateNames = States::pluck('name', 'id');

        return Inertia::render('Publications/Index', [
            'publications' => $publications->map(fn (Publications $publication) => [
                'id' => $publication->id,
                'title' => $publication->title,
                'state' => $this->stateLabel($publication->state, $stateNames),
                'description' => trim(strip_tags((string) $publication->description)),
                'imageUrl' => $publication->image
                    ? asset($publication->image)
                    : asset('/assets/img/placeholder.png'),
                'showUrl' => route('admin.publication.show', $publication->id),
                'editUrl' => route('admin.publication.edit', $publication->id),
                'deleteUrl' => route('admin.publication.destroy', $publication->id),
            ])->values(),
            'createUrl' => route('admin.publication.create'),
        ]);
    }

    /**
     * `state` holds either the literal 'national' or a states table id.
     */
    private function stateLabel(?string $state, $stateNames): string
    {
        if (!$state) {
            return '';
        }

        return $state === 'national' ? 'National' : (string) ($stateNames[$state] ?? '');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Publications/Form', array_merge($this->formOptions(), [
            'type' => 'Create',
            'submitUrl' => route('admin.publication.store'),
            'values' => $this->formValues(),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->rules());

        try {
            $publication = new Publications();
            $publication->title = $request->title;
            $publication->state = $request->state;
            $publication->description = $request->description;
            $publication->url = $request->publication_url;
            $publication->status = 'active';
            $publication->save();

            if ($request->hasFile('cover_image')) {
                $file = CommonFunction::fileUploadStorage($request->file('cover_image'), 'publictions', $publication->id . '-image-');
                if (!empty($file)) {
                    $publication->image = $file;
                }
            }
            if ($request->hasFile('publication_file')) {
                $file = CommonFunction::fileUploadStorage($request->file('publication_file'), 'publictions', $publication->id . '-file-');
                if (!empty($file)) {
                    $publication->file = $file;
                }
            }
            $publication->save();
            $publication->organizations()->sync($request->organization_ids ?? []);

            // The organization form creates publications inline over XHR and needs
            // the new row back to tick its checkbox.
            if ($request->expectsJson() && !$request->inertia()) {
                return response()->json([
                    'message' => 'Publication Details added successfully',
                    'status' => 'success',
                    'data' => ['id' => $publication->id, 'title' => $publication->title],
                    'redirect' => route('admin.publication.index'),
                ]);
            }

            return to_route('admin.publication.index')->with('success', 'Publication Details added successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson() && !$request->inertia()) {
                return response()->json([
                    'errors' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'status' => 'error',
                ], 500);
            }

            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $publication = Publications::with('organizations')->findOrFail($id);

        return Inertia::render('Publications/Show', array_merge($this->formOptions(), [
            'indexUrl' => route('admin.publication.index'),
            'editUrl' => route('admin.publication.edit', $publication->id),
            'fileUrl' => $publication->file ? url($publication->file) : '',
            'imageUrl' => $publication->image ? asset($publication->image) : '',
            'values' => $this->formValues($publication),
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $publication = Publications::with('organizations')->findOrFail($id);

        return Inertia::render('Publications/Form', array_merge($this->formOptions(), [
            'type' => 'Edit',
            'submitUrl' => route('admin.publication.update', $publication->id),
            'fileUrl' => $publication->file ? url($publication->file) : '',
            'imageUrl' => $publication->image ? asset($publication->image) : '',
            'values' => $this->formValues($publication),
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->rules($id));

        try {
            $publication = Publications::findOrFail($id);
            $publication->title = $request->title;
            $publication->state = $request->state;
            $publication->description = $request->description;
            $publication->url = $request->publication_url;
            $publication->status = 'active';
            $publication->save();

            if ($request->hasFile('cover_image')) {
                $file = CommonFunction::fileUploadStorage($request->file('cover_image'), 'publictions', $publication->id . '-image-');
                if (!empty($file)) {
                    CommonFunction::fileDeleteStorage($publication->image);
                    $publication->image = $file;
                }
            }
            if ($request->hasFile('publication_file')) {
                $file = CommonFunction::fileUploadStorage($request->file('publication_file'), 'publictions', $publication->id . '-file-');
                if (!empty($file)) {
                    CommonFunction::fileDeleteStorage($publication->file);
                    $publication->file = $file;
                }
            }
            $publication->save();
            $publication->organizations()->sync($request->organization_ids ?? []);

            return to_route('admin.publication.index')->with('success', 'Publication Details updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $publication = Publications::find($id);

            if (!$publication) {
                return back()->with('error', 'Publication not found');
            }

            $publication->organizations()->detach();
            CommonFunction::fileDeleteStorage($publication->image);
            CommonFunction::fileDeleteStorage($publication->file);
            $publication->delete();

            return back()->with('success', 'Publication details deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Cover image and file are required on create, optional on update.
     *
     * @return array<string, mixed>
     */
    private function rules(?string $id = null): array
    {
        return [
            'title' => 'required|unique:publications,title' . ($id ? ',' . $id : ''),
            'description' => 'required',
            'state' => 'required',
            'publication_url' => 'nullable|active_url',
            'cover_image' => ($id ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,webp|max:5120',
            'publication_file' => ($id ? 'nullable' : 'required') . '|file|max:10240',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'exists:organizations,id',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'indexUrl' => route('admin.publication.index'),
            'states' => States::orderBy('name', 'ASC')
                ->get()
                ->map(fn (States $state) => ['id' => (string) $state->id, 'name' => $state->name])
                ->values(),
            'organizations' => Organizations::where('status', 'active')
                ->orderBy('name', 'ASC')
                ->get()
                ->map(fn (Organizations $organization) => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                ])
                ->values(),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(?Publications $publication = null): array
    {
        return [
            'title' => $publication->title ?? '',
            'state' => (string) ($publication->state ?? ''),
            'description' => $publication->description ?? '',
            'publication_url' => $publication->url ?? '',
            'organization_ids' => $publication
                ? $publication->organizations->pluck('id')->values()->all()
                : [],
        ];
    }
}
