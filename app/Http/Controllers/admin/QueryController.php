<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Queries;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QueryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $queries = Queries::orderBy('id', 'desc')->get();

        return Inertia::render('Queries/Index', [
            'queries' => $queries->map(fn (Queries $query) => [
                'id' => $query->id,
                'name' => trim($query->first_name . ' ' . $query->last_name),
                'email' => $query->email,
                'organization' => $query->organization,
                'viewUrl' => route('admin.queries.edit', $query->id),
            ])->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return to_route('admin.queries.edit', $id);
    }

    /**
     * Queries are read-only; this is the detail screen, reached from the list.
     */
    public function edit(string $id)
    {
        $query = Queries::findOrFail($id);

        return Inertia::render('Queries/Show', [
            'indexUrl' => route('admin.queries.index'),
            'query' => [
                'first_name' => $query->first_name,
                'last_name' => $query->last_name,
                'email' => $query->email,
                'organization' => $query->organization,
                'message' => $query->message,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
