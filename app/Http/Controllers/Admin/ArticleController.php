<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    /**
     * Display articles with search and status filters.
     */
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
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the create article form.
     */
    public function create()
    {
        return view('admin.articles.create', [
            'article' => new Article(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new article.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $data['user_id'] = $request->user()->id;

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? $data['title'],
            null
        );

        $data = $this->preparePublicationData($request, $data);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('articles', 'public');
        }

        $tagIds = $data['tag_ids'] ?? [];

        unset($data['tag_ids']);

        $article = Article::create($data);

        $article->tags()->sync($tagIds);

        $message = match ($article->status) {
            'published' => 'Article published successfully.',
            'scheduled' => 'Article scheduled successfully.',
            default => 'Article saved as draft successfully.',
        };

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', $message);
    }

    /**
     * Show the edit article form.
     */
    public function edit(Article $article)
    {
        $article->load('tags');

        return view('admin.articles.edit', [
            'article' => $article,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Update an existing article.
     */
    public function update(Request $request, Article $article)
    {
        $data = $this->validatedData($request, $article);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? $data['title'],
            $article->id
        );

        $data = $this->preparePublicationData(
            $request,
            $data,
            $article
        );

        if ($request->hasFile('cover_image')) {
            $newCover = $request->file('cover_image')
                ->store('articles', 'public');

            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }

            $data['cover_image'] = $newCover;
        }

        $tagIds = $data['tag_ids'] ?? [];

        unset($data['tag_ids']);

        $article->update($data);

        $article->tags()->sync($tagIds);

        $message = match ($article->status) {
            'published' => 'Article updated and published successfully.',
            'scheduled' => 'Article schedule updated successfully.',
            default => 'Article saved as draft successfully.',
        };

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', $message);
    }

    /**
     * Delete an article and its stored cover image.
     */
    public function destroy(Article $article)
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        $article->tags()->detach();

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }

    /**
     * Validate the request and sanitize the rich-text HTML.
     */
    private function validatedData(
        Request $request,
        ?Article $article = null
    ): array {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:220',
                Rule::unique('articles', 'slug')->ignore($article?->id),
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],

            'body' => [
                'required',
                'string',
                'max:100000',
            ],

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            /*
             * The Blade form uses name="tags[]".
             * Convert that field to tag_ids internally for syncing.
             */
            'tags' => [
                'nullable',
                'array',
            ],

            'tags.*' => [
                'integer',
                'distinct',
                'exists:tags,id',
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'published', 'scheduled']),
            ],

            'publish_now' => [
                'nullable',
                'boolean',
            ],

            'published_date' => [
                'nullable',
                'required_if:status,scheduled',
                'date_format:Y-m-d',
            ],

            'published_time' => [
                'nullable',
                'required_if:status,scheduled',
                'date_format:H:i',
            ],

            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:4096',
            ],
        ]);

        $data['tag_ids'] = $data['tags'] ?? [];

        unset($data['tags']);

        $data['body'] = $this->sanitizeHtml($data['body']);

        $plainText = html_entity_decode(
            strip_tags($data['body']),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        if (trim($plainText) === '') {
            throw ValidationException::withMessages([
                'body' => 'Please enter a valid article description.',
            ]);
        }

        return $data;
    }

    /**
     * Determine publication status and timestamp.
     *
     * Publish Now always overrides the chosen status/date/time.
     * Drafts have no publication timestamp.
     * Scheduled articles require a date and time.
     */
    private function preparePublicationData(
        Request $request,
        array $data,
        ?Article $article = null
    ): array {
        if ($request->boolean('publish_now')) {
            $data['status'] = 'published';
            $data['published_at'] = now();

            unset(
                $data['published_date'],
                $data['published_time'],
                $data['publish_now']
            );

            return $data;
        }

        $status = $data['status'];

        if ($status === 'draft') {
            $data['published_at'] = null;

            unset(
                $data['published_date'],
                $data['published_time'],
                $data['publish_now']
            );

            return $data;
        }

        $existingPublishedAt = $article?->published_at;

        $date = $request->input('published_date');
        $time = $request->input('published_time');

        if ($status === 'scheduled') {
            if (!$date || !$time) {
                throw ValidationException::withMessages([
                    'published_date' => 'Select a publication date and time for a scheduled article.',
                ]);
            }
        } else {
            $date = $date
                ?: $existingPublishedAt?->format('Y-m-d')
                ?: now()->format('Y-m-d');

            $time = $time
                ?: $existingPublishedAt?->format('H:i')
                ?: now()->format('H:i');
        }

        try {
            $publishedAt = Carbon::createFromFormat(
                '!Y-m-d H:i',
                $date . ' ' . $time,
                config('app.timezone')
            );
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'published_date' => 'The publication date or time is invalid.',
            ]);
        }

        if (!$publishedAt) {
            throw ValidationException::withMessages([
                'published_date' => 'The publication date or time is invalid.',
            ]);
        }

        if (
            $status === 'scheduled' &&
            $publishedAt->lessThanOrEqualTo(now())
        ) {
            throw ValidationException::withMessages([
                'published_date' => 'Choose a future date and time for scheduled publication.',
            ]);
        }

        $data['published_at'] = $publishedAt;

        unset(
            $data['published_date'],
            $data['published_time'],
            $data['publish_now']
        );

        return $data;
    }

    /**
     * Sanitize Quill HTML while retaining common formatting.
     */
    private function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            'p',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'strike',
            'h1',
            'h2',
            'h3',
            'h4',
            'blockquote',
            'pre',
            'code',
            'ol',
            'ul',
            'li',
            'a',
            'span',
            'div',
            'sub',
            'sup',
        ];

        $dangerousTags = [
            'script',
            'style',
            'iframe',
            'object',
            'embed',
            'svg',
            'math',
            'form',
            'input',
            'button',
            'textarea',
            'select',
            'video',
            'audio',
            'source',
            'link',
            'meta',
            'base',
        ];

        $previousLibxmlState = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');

            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">' .
                '<div id="article-content-root">' .
                $html .
                '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            if (!$loaded) {
                return e(strip_tags($html));
            }

            $xpath = new \DOMXPath($document);

            $root = $xpath->query(
                '//*[@id="article-content-root"]'
            )->item(0);

            if (!$root) {
                return e(strip_tags($html));
            }

            $this->sanitizeNode(
                $root,
                $allowedTags,
                $dangerousTags
            );

            $result = '';

            foreach ($root->childNodes as $child) {
                $result .= $document->saveHTML($child);
            }

            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
        }
    }

    /**
     * Recursively remove unsafe elements and attributes.
     */
    private function sanitizeNode(
        DOMNode $node,
        array $allowedTags,
        array $dangerousTags
    ): void {
        $children = [];

        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (!$child->parentNode) {
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            // Remove dangerous tags and their entire contents.
            if (in_array($tag, $dangerousTags, true)) {
                $child->parentNode->removeChild($child);
                continue;
            }

            $this->sanitizeNode(
                $child,
                $allowedTags,
                $dangerousTags
            );

            // Unwrap unsupported tags but preserve their text content.
            if (!in_array($tag, $allowedTags, true)) {
                $parent = $child->parentNode;

                while ($child->firstChild) {
                    $parent->insertBefore(
                        $child->firstChild,
                        $child
                    );
                }

                $parent->removeChild($child);
                continue;
            }

            $attributes = [];

            foreach ($child->attributes as $attribute) {
                $attributes[] = $attribute->name;
            }

            foreach ($attributes as $attributeName) {
                $value = $child->getAttribute($attributeName);

                if ($attributeName === 'style') {
                    $safeStyle = $this->sanitizeStyle($value);

                    if ($safeStyle !== '') {
                        $child->setAttribute('style', $safeStyle);
                    } else {
                        $child->removeAttribute('style');
                    }

                    continue;
                }

                if ($attributeName === 'class') {
                    $safeClasses = [];

                    foreach (
                        preg_split('/\s+/', trim($value)) as $class
                    ) {
                        if (preg_match(
                            '/^ql-(?:align-(?:center|right|justify)|size-(?:small|large|huge)|font-(?:serif|monospace))$/',
                            $class
                        )) {
                            $safeClasses[] = $class;
                        }
                    }

                    if ($safeClasses) {
                        $child->setAttribute(
                            'class',
                            implode(' ', $safeClasses)
                        );
                    } else {
                        $child->removeAttribute('class');
                    }

                    continue;
                }

                if (
                    $tag === 'a' &&
                    in_array(
                        $attributeName,
                        ['href', 'title', 'target', 'rel'],
                        true
                    )
                ) {
                    if ($attributeName === 'href') {
                        if (!$this->isSafeUrl($value)) {
                            $child->removeAttribute('href');
                        }
                    } elseif ($attributeName === 'target') {
                        if ($value !== '_blank') {
                            $child->removeAttribute('target');
                        }
                    } else {
                        // Add a safe rel attribute below when required.
                        $child->removeAttribute($attributeName);
                    }

                    continue;
                }

                if ($tag === 'li' && $attributeName === 'data-list') {
                    if (!in_array($value, ['ordered', 'bullet'], true)) {
                        $child->removeAttribute($attributeName);
                    }

                    continue;
                }

                // Remove event handlers and all unapproved attributes.
                $child->removeAttribute($attributeName);
            }

            if ($tag === 'a' && $child->hasAttribute('target')) {
                $child->setAttribute('rel', 'noopener noreferrer');
            }
        }
    }

    /**
     * Accept safe web URLs, mailto links, fragments and local paths.
     */
    private function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '#')) {
            return true;
        }

        if (
            str_starts_with($url, '/') &&
            !str_starts_with($url, '//')
        ) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) &&
            in_array(
                strtolower($scheme),
                ['http', 'https', 'mailto'],
                true
            );
    }

    /**
     * Retain only safe text formatting styles.
     */
    private function sanitizeStyle(string $style): string
    {
        $safe = [];

        foreach (explode(';', $style) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map(
                'trim',
                explode(':', $declaration, 2)
            );

            $property = strtolower($property);
            $value = strtolower($value);

            if (
                in_array(
                    $property,
                    ['color', 'background-color'],
                    true
                )
            ) {
                $isHexColor = preg_match(
                    '/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i',
                    $value
                );

                $isRgbColor = preg_match(
                    '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/',
                    $value
                );

                $isNamedColor = in_array(
                    $value,
                    [
                        'red', 'blue', 'green', 'black', 'white',
                        'gray', 'grey', 'yellow', 'orange', 'purple',
                        'maroon', 'navy', 'teal', 'silver', 'transparent',
                    ],
                    true
                );

                if ($isHexColor || $isRgbColor || $isNamedColor) {
                    $safe[] = $property . ': ' . $value;
                }

                continue;
            }

            if ($property === 'text-align') {
                if (in_array(
                    $value,
                    ['left', 'right', 'center', 'justify'],
                    true
                )) {
                    $safe[] = $property . ': ' . $value;
                }

                continue;
            }

            if ($property === 'font-size') {
                if (preg_match(
                    '/^(?:10|12|14|16|18|20|24|28|32|36|40|48|56|64)px$/',
                    $value
                )) {
                    $safe[] = $property . ': ' . $value;
                }
            }
        }

        return implode('; ', $safe);
    }

    /**
     * Generate a unique slug, including when the supplied slug is empty.
     */
    private function uniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $counter = 2;

        while (
            Article::where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
