<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\CampaignArticleController;
use App\Http\Controllers\MediaProxyController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/campaigns/chunk', [HomeController::class, 'chunk'])->name('home.chunk');

Route::get('/campaign/{slug}', [CampaignController::class, 'show'])->name('campaign.show');
Route::get('/campaign/{slug}/donasi', [CampaignController::class, 'donateForm'])->name('campaign.donate.form');
Route::post('/campaign/{slug}/donasi', [CampaignController::class, 'donate'])->name('campaign.donate');
Route::get('/laporan/{id}/{slug?}', [CampaignArticleController::class, 'show'])->name('article.show');
Route::get('/media/{disk}', [MediaProxyController::class, 'show'])->name('media.proxy');

Route::view('/donasi/{reference}/terima-kasih', 'donation.thanks')->name('donation.thanks');

// Pembayaran (Midtrans)
Route::get('/donasi/{reference}/bayar', [PaymentController::class, 'pay'])->name('donation.pay');
Route::post('/midtrans/notify', [PaymentController::class, 'notify'])->name('midtrans.notify');
