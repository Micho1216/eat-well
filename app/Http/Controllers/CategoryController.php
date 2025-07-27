<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Models\PackageCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = PackageCategory::all()->sortBy('categoryId');

        return view('view-all-packages-category', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        $validated = $request->validated();

        PackageCategory::create([
            'categoryName' => $validated['categoryName'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Category added successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = PackageCategory::findOrFail($id);

        if ($category->packages()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete category with associated packages.');
        }

        $category->delete(); // Soft delete

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
