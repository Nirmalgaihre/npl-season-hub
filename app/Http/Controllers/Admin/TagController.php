<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('articles')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:tags,slug'],
        ]);

        $base = Str::slug($data['slug'] ?: $data['name']) ?: 'tag';
        $slug = $base;
        $n = 2;

        while (Tag::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        $data['slug'] = $slug;
        Tag::create($data);

        return back()->with('success', 'Tag created.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}