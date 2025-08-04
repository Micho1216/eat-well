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

        $cat = PackageCategory::create([
            'categoryName' => $validated['categoryName'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        logActivity('Success', 'added category', $cat->categoryName);
        return redirect()->back()->with('success', __('admin/package_category.store_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = PackageCategory::findOrFail($id);

        if ($category->packages()->exists()) {
            logActivity('Failed', 'deleting category', $category->categoryName);
            return redirect()->back()->with('error',  __('admin/package_category.delete_failed'));
        }
        
        logActivity('Success', 'deleting category', $category->categoryName);
        $category->delete(); // Soft delete

        logActivity('Successfully', 'Deleted', 'Category');
        return redirect()->back()->with('success', __('admin/package_category.delete_success'));
    }
}
