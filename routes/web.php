<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Public\WorkerList;
use App\Livewire\Public\WorkerProfile;
use App\Livewire\Public\AgentProfilePage;
use App\Livewire\Public\AgentLeaderboard;
use App\Livewire\JobList;
use App\Livewire\JobDetail;
use App\Livewire\Public\Homepage;
use App\Http\Controllers\MilestoneReceiptController;
use App\Http\Controllers\Admin\DisputeEvidenceDownloadController;

/*
|--------------------------------------------------------------------------
| Web Routes
| AmeelHub — Step 1.5 Rate Limiters Applied
| Step 10.8a — All routes controller-backed (route:cache safe, no closures)
|--------------------------------------------------------------------------
*/

// -----------------------------------------------------------------------
// PUBLIC ROUTES
// -----------------------------------------------------------------------

Route::get('/jobs', JobList::class)->name('jobs.index');
Route::get('/jobs/{uuid}', JobDetail::class)->name('jobs.show');

Route::get('/deals/milestones/{milestone}/receipt', [MilestoneReceiptController::class, 'download'])
    ->middleware(['auth'])
    ->name('milestones.receipt.download');

Route::get('/admin/dispute-evidence/{evidence}/download', DisputeEvidenceDownloadController::class)
    ->middleware(['signed', 'auth'])
    ->name('dispute-evidence.download');

Route::get('/', Homepage::class)->name('home');

Route::get('/workers', WorkerList::class)->name('workers.index');
Route::get('/workers/{worker:uuid}', WorkerProfile::class)->name('workers.show');

// Agent leaderboard — Step 8.2 (must be registered BEFORE /agents/{uuid})
Route::get('/agents', AgentLeaderboard::class)->name('agents.leaderboard');

// Agent public profile — Step 3.3
Route::get('/agents/{agentProfile:uuid}', AgentProfilePage::class)->name('agents.show');

// -----------------------------------------------------------------------
// SOCIAL OAUTH — throttle:social-auth (10/min per IP)
// -----------------------------------------------------------------------
Route::middleware('throttle:social-auth')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect')
        ->where('provider', 'google|facebook');

    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('social.callback')
        ->where('provider', 'google|facebook');
});

// -----------------------------------------------------------------------
// AUTHENTICATED ROUTES
// -----------------------------------------------------------------------

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/lang/{locale}', LocaleController::class)->name('lang.switch');

// -----------------------------------------------------------------------
// CONTACT REVEAL — throttle:cv-reveal (10/day per user)
// -----------------------------------------------------------------------
// Route::middleware(['auth', 'verified', 'throttle:cv-reveal'])->group(function () {
//     Route::post('/workers/{uuid}/reveal-contact', [ContactRevealController::class, 'store'])
//         ->name('workers.reveal-contact');
// });

// -----------------------------------------------------------------------
// FEE REVEAL — throttle:fee-reveal (20/day per user)
// -----------------------------------------------------------------------
// Route::middleware(['auth', 'verified', 'throttle:fee-reveal'])->group(function () {
//     Route::post('/jobs/{uuid}/reveal-fee', [JobFeeRevealController::class, 'store'])
//         ->name('jobs.reveal-fee');
// });

// -----------------------------------------------------------------------
// NOK — throttle:nok (10/min per user)
// -----------------------------------------------------------------------
// Route::middleware(['auth', 'verified', 'throttle:nok'])->group(function () {
//     Route::post('/nok/send', [NokController::class, 'store'])->name('nok.send');
//     Route::post('/nok/bulk', [NokController::class, 'bulk'])->name('nok.bulk');
// });

// -----------------------------------------------------------------------
// DISPUTE — throttle:dispute (2/day per user)
// -----------------------------------------------------------------------
// Route::middleware(['auth', 'verified', 'throttle:dispute'])->group(function () {
//     Route::post('/deals/{deal}/dispute', [DisputeController::class, 'store'])
//         ->name('deals.dispute');
// });

// -----------------------------------------------------------------------
// WITHDRAWAL — throttle:withdrawal (3/day per user)
// -----------------------------------------------------------------------
// Route::middleware(['auth', 'verified', 'throttle:withdrawal'])->group(function () {
//     Route::post('/wallet/withdraw', [WithdrawalController::class, 'store'])
//         ->name('wallet.withdraw');
// });

// -----------------------------------------------------------------------
// BREEZE AUTH ROUTES
// -----------------------------------------------------------------------



Route::get('/panel-logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('web')->name('panel.logout');


require __DIR__ . '/auth.php';
