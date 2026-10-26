<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::with(['author', 'category'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->input('status'))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.create', [
            'article' => new Article(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title']);
        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? now())
            : null;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('articles', 'public');
        }

        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $article = Article::create($data);
        $article->tags()->sync($tagIds);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $article->load('tags');

        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validatedData($request, $article);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?: $data['title'],
            $article->id
        );

        $data['published_at'] = $data['status'] === 'published'
            ? ($data['published_at'] ?? $article->published_at ?? now())
            : null;

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }

            $data['cover_image'] = $request->file('cover_image')
                ->store('articles', 'public');
        }

        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $article->update($data);
        $article->tags()->sync($tagIds);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }

    private function validatedData(
        Request $request,
        ?Article $article = null
    ): array {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:220',
                Rule::unique('articles', 'slug')->ignore($article?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'category_id' => [
                'nullable', 'integer', 'exists:categories,id',
            ],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'cover_image' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $counter = 2;

        while (
            Article::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}