<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banners;
use App\Models\Publications;
use App\Models\SiteSettings;
use App\Models\States;
use Illuminate\Http\Request;
use Jorenvh\Share\ShareFacade as Share;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $queryParam = $request->all();
        $queryParam['order'] = $queryParam['order'] ?? 'asc';

        $states = States::orderBy('name', 'ASC')->get();
        $publicationsQuery = Publications::with('organizations')->orderBy('title', $queryParam['order']);

        if ($request->filled('state')) {
            if ($request->state === 'national') {
                $publicationsQuery->where(function ($query) {
                    $query->whereNull('state')->orWhere('state', 'national');
                });
            } else {
                $publicationsQuery->where('state', $request->state);
            }
        }

        $publications = $publicationsQuery->paginate(20);

        $banners = Banners::where('page_slug', 'library')->where('status', 'active')->orderBy('order', 'asc')->get();
        $libraryContent = SiteSettings::where('settings_name', 'library_block')->first()->settings_value ?? '';
        return view('frontend.library.index', compact('states', 'publications', 'queryParam', 'banners', 'libraryContent'));
    }

    public function show(Request $request, $id)
    {
        $publication = Publications::with('organizations')->find($id);
        if (!$publication) {
            return redirect()->route('library')->with('error', 'Publication not found.');
        }

        $stateName = null;
        if ($publication->state) {
            if ($publication->state === 'national') {
                $stateName = 'National';
            } elseif (is_numeric($publication->state)) {
                $state = States::find($publication->state);
                $stateName = $state?->name;
            }
        }

        $banners = Banners::where('page_slug', 'library')->where('status', 'active')->orderBy('order', 'asc')->get();

        $filters = $request->query();
        unset($filters['id']);

        $shareLinks = Share::page(route('library.show', ['id' => $publication->id]), $publication->title)
            ->facebook()
            ->twitter()
            ->linkedin()
            ->whatsapp()
            ->getRawLinks();

        return view('frontend.library.show', compact('publication', 'stateName', 'banners', 'filters', 'shareLinks'));
    }

    public function downloadResource($id)
    {
        $publication = Publications::find($id);
        if (!$publication) {
            return back()->with('error', 'Publication file not found');
        } else {
            if (!file_exists(public_path($publication->file))) {
                return back()->with('error', 'Publication file not found');
            }
        }
        return response()->download(public_path($publication->file));
    }
}
