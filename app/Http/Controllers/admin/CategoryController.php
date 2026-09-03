<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Categories::orderBy('category_order', 'ASC')->paginate(20);

        return Inertia::render('Categories/Index', [
            'categories' => $categories->through(fn (Categories $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'category_order' => $category->category_order,
                'status' => $category->status,
                'editUrl' => route('admin.category.edit', $category->id),
                'deleteUrl' => route('admin.category.destroy', $category->id),
            ]),
            'createUrl' => route('admin.category.create'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Categories/Form', [
            'type' => 'Create',
            'submitUrl' => route('admin.category.store'),
            'indexUrl' => route('admin.category.index'),
            'values' => $this->formValues(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate($this->rules());

        try {
            $category = new Categories();
            $category->name = $request->name;
            $category->status = $request->status;
            $category->category_order = $request->category_order;
            $category->save();

            return to_route('admin.category.index')->with('success', 'Category Details added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Categories have no read-only screen; the resource route still points here,
     * so send it to the editor rather than 404ing.
     */
    public function show(string $id)
    {
        return to_route('admin.category.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Categories::findOrFail($id);

        return Inertia::render('Categories/Form', [
            'type' => 'Edit',
            'submitUrl' => route('admin.category.update', $category->id),
            'indexUrl' => route('admin.category.index'),
            'values' => $this->formValues($category),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->rules());

        try {
            $category = Categories::findOrFail($id);
            $category->name = $request->name;
            $category->status = $request->status;
            $category->category_order = $request->category_order;
            $category->save();

            return to_route('admin.category.index')->with('success', 'Category details updated successfully');
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
            $category = Categories::find($id);

            if (!$category) {
                return back()->with('error', 'Category not found');
            }

            $category->delete();

            return back()->with('success', 'Category details deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'name' => 'required',
            'status' => 'required',
            'category_order' => 'required|integer',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(?Categories $category = null): array
    {
        return [
            'name' => $category->name ?? '',
            'status' => $category->status ?? 'active',
            'category_order' => $category->category_order ?? '',
        ];
    }
}
