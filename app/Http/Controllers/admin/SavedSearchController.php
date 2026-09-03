<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\SavedSearchResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SavedSearchController extends Controller
{
    /**
     * Saved searches, paginated.
     *
     * This screen used to be a server-side DataTable whose endpoint returned
     * *rendered Blade partials* as HTML strings inside its JSON — the user cell
     * and the action buttons were markup built on the server and injected into
     * the page. It now returns data and the Vue table renders it.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = SavedSearchResources::with('user')->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('search_params', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $categoryMap = Categories::pluck('name', 'id');

        $savedSearches = $query->paginate(10)->withQueryString();
        $savedSearches->getCollection()->transform(
            fn (SavedSearchResources $savedSearch) => $this->row($savedSearch, $categoryMap),
        );

        return Inertia::render('SavedSearches/Index', [
            'savedSearches' => $savedSearches,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(SavedSearchResources $savedSearch, $categoryMap): array
    {
        return [
            'id' => $savedSearch->id,
            'user' => [
                'name' => $savedSearch->user->name ?? 'Unknown User',
                'email' => $savedSearch->user->email ?? '',
            ],
            'criteria' => $this->formatCriteria($savedSearch->search_params, $categoryMap),
            'createdAt' => $savedSearch->created_at ? $savedSearch->created_at->format('M d, Y h:i A') : '',
            // Only offered when the PDF is actually on disk.
            'downloadUrl' => $this->pdfExists($savedSearch)
                ? route('admin.saved-searches.download', $savedSearch->id)
                : null,
            'destroyUrl' => route('admin.saved-searches.destroy', $savedSearch->id),
        ];
    }

    private function pdfPath(SavedSearchResources $savedSearch): string
    {
        return 'search-resources/' . $savedSearch->id . '-' . $savedSearch->user_id . '-saved-search.pdf';
    }

    private function pdfExists(SavedSearchResources $savedSearch): bool
    {
        return Storage::disk('public')->exists($this->pdfPath($savedSearch));
    }

    public function download($id)
    {
        $savedSearch = SavedSearchResources::findOrFail($id);

        if (!$this->pdfExists($savedSearch)) {
            abort(404, 'Saved search PDF not found.');
        }

        return response()->download(
            Storage::disk('public')->path($this->pdfPath($savedSearch)),
            basename($this->pdfPath($savedSearch)),
        );
    }

    public function destroy($id)
    {
        $savedSearch = SavedSearchResources::findOrFail($id);
        $path = $this->pdfPath($savedSearch);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $savedSearch->delete();

        return back()->with('success', 'Saved search deleted successfully.');
    }

    private function formatCriteria($searchParams, $categoryMap): string
    {
        $params = is_string($searchParams) ? json_decode($searchParams, true) : $searchParams;
        if (!is_array($params)) {
            return '';
        }

        $keys = [
            'state' => 'State',
            'postal_code' => 'Postal Code',
            'category' => 'Category',
            'organization_type' => 'Organization Type',
            'target_population' => 'Target Population',
            'organization_name' => 'Organization Name',
        ];

        $parts = [];
        foreach ($keys as $key => $label) {
            if (!isset($params[$key]) || $params[$key] === '' || $params[$key] === null) {
                continue;
            }

            $value = $params[$key];
            if ($key === 'category' && isset($categoryMap[$value])) {
                $value = $categoryMap[$value];
            }

            $parts[] = $label . ': ' . $value;
        }

        return implode(' | ', $parts);
    }
}
