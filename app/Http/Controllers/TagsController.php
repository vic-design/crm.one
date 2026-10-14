<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tags\TagStoreRequest;
use App\Models\Tag;
use App\Http\Requests\Tags\TagUpdateRequest;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagsController extends Controller
{
    /**
     * Summary of index
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';

        $tags = Tag::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $tags->getCollection()->transform(function ($tag) {
            $tag->total_usage_count = DB::table('taggables')
                ->where('tag_id', $tag->id)
                ->count();
            return $tag;
        });

        return Inertia::render('Tags/Index', [
            'tagList' => $tags,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param TagStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TagStoreRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('tags.index')
            ->with('success', 'Тег создан!');
    }

    /**
     * Update the specified resource in storage.
     * @param TagUpdateRequest $request
     * @param Tag $tag
     * @return RedirectResponse
     */
    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        Tag::update($request->validated());

        return redirect()->route('tags.index')
            ->with('success', 'Тег обновлён!');
    }

    /**
     * Remove the specified resource from storage.
     * @param Tag $tag
     * @return RedirectResponse
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'Тег удалён!');
    }

    /**
     * Summary of all
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        return response()->json(Tag::select(['id', 'name', 'slug'])->get());
    }

    /**
     * Summary of show
     * @param Tag $tag
     * @return \Inertia\Response
     */
    public function show(Tag $tag): Response
    {
        $tag->load([
            'users' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            },
            'users.media',
        ]);

        $tag->users->each(function ($user) {
            $user->avatar_url = $user->avatar_url;
        });

        return Inertia::render('Tags/Show', [
            'tag' => $tag
        ]);
    }
}
