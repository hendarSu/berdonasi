<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CampaignController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/campaigns/chunk', [HomeController::class, 'chunk'])->name('home.chunk');

Route::get('/campaign/{slug}', [CampaignController::class, 'show'])->name('campaign.show');
Route::post('/campaign/{slug}/donasi', [CampaignController::class, 'donate'])->name('campaign.donate');

Route::view('/donasi/{reference}/terima-kasih', 'donation.thanks')->name('donation.thanks');
