<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Organization;
use App\Models\News;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');
        $q = trim((string) $request->query('q'));

        $perPage = 6;

        // Load organization for homepage settings
        $org = Organization::query()->first();

        // Homepage heroes: use Hero records (active) linked to campaigns
        $heroes = \App\Models\Hero::query()
            ->with(['campaign' => fn ($q) => $q->select('id','title','slug','status')])
            ->where('status', 'active')
            ->whereHas('campaign', fn ($q) => $q->where('status', 'active'))
            ->latest('updated_at')
            ->take(5)
            ->get(['id','campaign_id','image_path','status','updated_at']);

        $campaigns = Campaign::query()
            ->with(['categories:id,name,slug', 'media' => function ($q) { $q->orderBy('sort_order'); }])
            ->when($categorySlug, fn ($query) => $query->whereHas('categories', fn ($cq) => $cq->where('slug', $categorySlug)))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%$q%")
                       ->orWhere('summary', 'like', "%$q%")
                       ->orWhere('description_md', 'like', "%$q%");
                });
            })
            ->when($request->boolean('all') === false, fn ($q) => $q->where('status', 'active'))
            ->latest('updated_at')
            ->simplePaginate($perPage)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Latest published news for homepage (3 items)
        $latestNews = News::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->take(3)
            ->get(['id','title','slug','excerpt','cover_path','published_at','author_id']);

        return view('home', [
            'campaigns' => $campaigns,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
            'q' => $q,
            'perPage' => $perPage,
            'org' => $org,
            'heroes' => $heroes,
            'latestNews' => $latestNews,
        ]);
    }

    public function chunk(Request $request)
    {
        $categorySlug = $request->query('category');
        $q = trim((string) $request->query('q'));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('perPage', 6));

        $campaigns = Campaign::query()
            ->with(['categories:id,name,slug', 'media' => function ($q) { $q->orderBy('sort_order'); }])
            ->when($categorySlug, fn ($query) => $query->whereHas('categories', fn ($cq) => $cq->where('slug', $categorySlug)))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%$q%")
                       ->orWhere('summary', 'like', "%$q%")
                       ->orWhere('description_md', 'like', "%$q%");
                });
            })
            ->when($request->boolean('all') === false, fn ($q) => $q->where('status', 'active'))
            ->latest('updated_at')
            ->simplePaginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $html = view('partials.campaign-cards', [
            'campaigns' => $campaigns,
        ])->render();

        return response()->json([
            'html' => $html,
            'hasMore' => $campaigns->hasMorePages(),
            'nextPage' => $campaigns->currentPage() + 1,
        ]);
    }
}
