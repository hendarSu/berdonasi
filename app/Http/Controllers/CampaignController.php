<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignArticle;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $campaign = Campaign::query()
            ->with(['categories:id,name,slug', 'media' => fn ($q) => $q->orderBy('sort_order')])
            ->where('slug', $slug)
            ->firstOrFail();

        $tab = in_array($request->query('tab'), ['detail', 'laporan', 'donatur']) ? $request->query('tab') : 'detail';

        $articles = CampaignArticle::query()
            ->with(['payout'])
            ->where('campaign_id', $campaign->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        $donations = Donation::query()
            ->where('campaign_id', $campaign->id)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('campaign.show', [
            'c' => $campaign,
            'tab' => $tab,
            'articles' => $articles,
            'donations' => $donations,
        ]);
    }

    public function donate(Request $request, string $slug)
    {
        $campaign = Campaign::query()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_email' => ['nullable', 'email', 'max:255'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        $ref = 'DN-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(6));

        $donation = Donation::create([
            'campaign_id' => $campaign->id,
            'user_id' => auth()->id(),
            'donor_name' => $data['donor_name'] ?? null,
            'donor_email' => $data['donor_email'] ?? null,
            'is_anonymous' => (bool)($data['is_anonymous'] ?? false),
            'amount' => $data['amount'],
            'currency' => 'IDR',
            'status' => 'initiated',
            'reference' => $ref,
            'message' => $data['message'] ?? null,
            'created_at' => now(),
        ]);

        return redirect()->route('donation.thanks', ['reference' => $donation->reference]);
    }
}
