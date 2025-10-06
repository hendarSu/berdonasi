<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\CampaignArticleController;
use App\Http\Controllers\MediaProxyController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\WaController;
use App\Http\Controllers\DonationController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/campaigns/chunk', [HomeController::class, 'chunk'])->name('home.chunk');
Route::get('/program', [ProgramController::class, 'index'])->name('program.index');
Route::get('/programs/chunk', [ProgramController::class, 'chunk'])->name('program.chunk');

Route::get('/campaign/{slug}', [CampaignController::class, 'show'])->name('campaign.show');
Route::get('/campaign/{slug}/donasi', [CampaignController::class, 'donateForm'])->name('campaign.donate.form');
Route::post('/campaign/{slug}/donasi', [CampaignController::class, 'donate'])->name('campaign.donate');
Route::get('/laporan/{id}/{slug?}', [CampaignArticleController::class, 'show'])->name('article.show');
Route::get('/media/{disk}', [MediaProxyController::class, 'show'])->name('media.proxy');

Route::get('/donasi/{reference}/terima-kasih', [DonationController::class, 'thanks'])->name('donation.thanks');
Route::get('/donasi/{reference}/terima-kasih', [DonationController::class, 'thanks'])->name('donation.thanks');

// Pembayaran (Midtrans)
Route::get('/donasi/{reference}/bayar', [PaymentController::class, 'pay'])->name('donation.pay');
Route::post('/midtrans/notify', [PaymentController::class, 'notify'])->name('midtrans.notify');

// Donor CRM (public simple listing)
Route::get('/donatur', [DonorController::class, 'index'])->name('donor.index');

// Public AJAX endpoint to validate WA number from donation form
Route::post('/wa/validate-number', [WaController::class, 'validateNumber'])
    ->middleware(\App\Http\Middleware\InternalOnly::class)
    ->name('wa.validate');

// News (storefront)
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');

// Static Pages (storefront)
Route::get('/p/{slug}', [PageController::class, 'show'])->name('page.show');
