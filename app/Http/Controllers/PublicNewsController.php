<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class PublicNewsController extends Controller
{
    public function index(Request $request)
    {
        $articles = Article::published()
            ->with(['author', 'category', 'tags'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('news.index', compact('articles'));
    }

    public function show(Article $article)
    {
        abort_unless(
            $article->status === 'published'
                && $article->published_at
                && $article->published_at->lte(now()),
            404
        );

        $article->load(['author', 'category', 'tags']);

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->when(
                $article->category_id,
                fn ($q) => $q->where('category_id', $article->category_id)
            )
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('news.show', compact('article', 'related'));
    }

    public function category(Category $category)
    {
        $articles = Article::published()
            ->where('category_id', $category->id)
            ->with(['author', 'category', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('news.index', [
            'articles' => $articles,
            'heading' => $category->name,
        ]);
    }

    public function tag(Tag $tag)
    {
        $articles = Article::published()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->with(['author', 'category', 'tags'])
            ->latest('published_at')
            ->paginate(12);

        return view('news.index', [
            'articles' => $articles,
            'heading' => '#' . $tag->name,
        ]);
    }
}