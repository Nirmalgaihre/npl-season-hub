<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('articles')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $base = Str::slug($data['slug'] ?: $data['name']) ?: 'category';
        $slug = $base;
        $n = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        $data['slug'] = $slug;
        Category::create($data);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:120',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->articles()->exists()) {
            return back()->withErrors([
                'category' => 'Reassign or remove this category’s articles first.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}