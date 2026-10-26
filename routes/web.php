<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UiKitController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\BrochureController;
use App\Http\Controllers\Web\CareerController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\EnquiryController;
use App\Http\Controllers\Web\FaqController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocaleRedirectController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\ProjectController as WebProjectController;
use App\Http\Controllers\Web\RobotsController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\TeamController;
use App\Http\Controllers\Web\TestimonialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (server-side rendered, /en/... and /bn/...)
|--------------------------------------------------------------------------
*/

Route::middleware('website.redirects')->group(function (): void {
    Route::get('/', LocaleRedirectController::class)->name('home');
    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
    Route::get('/robots.txt', RobotsController::class)->name('robots');
    Route::get('/brochures/{project}/download', [BrochureController::class, 'download'])
        ->middleware('signed')
        ->name('web.brochures.download');

    // Retired URLs from the redirects manager land here before the 404.
    Route::fallback(fn () => abort(404))->middleware('website.redirects');

    Route::prefix('{locale}')
        ->whereIn('locale', ['en', 'bn'])
        ->middleware(['website.locale', 'website'])
        ->name('web.')
        ->group(function (): void {
            Route::get('/', HomeController::class)->name('home');
            Route::get('/about', AboutController::class)->name('about');

            Route::get('/projects', [WebProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{project:slug}', [WebProjectController::class, 'show'])->name('projects.show');

            Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
            Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

            Route::get('/news', [PostController::class, 'index'])->name('news.index');
            Route::get('/news/{post:slug}', [PostController::class, 'show'])->name('news.show');

            Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
            Route::get('/careers/{jobPost:slug}', [CareerController::class, 'show'])->name('careers.show');
            Route::post('/careers/{jobPost:slug}/apply', [CareerController::class, 'apply'])
                ->middleware('throttle:website-forms')
                ->name('careers.apply');

            Route::get('/team', TeamController::class)->name('team');
            Route::get('/testimonials', TestimonialController::class)->name('testimonials');
            Route::get('/faq', FaqController::class)->name('faq');
            Route::get('/contact', [ContactController::class, 'show'])->name('contact');

            Route::post('/enquiries', [EnquiryController::class, 'store'])
                ->middleware('throttle:website-forms')
                ->name('enquiries.store');
            Route::post('/brochure-requests', [BrochureController::class, 'store'])
                ->middleware('throttle:website-forms')
                ->name('brochures.store');

            Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
            Route::get('/terms', [PageController::class, 'terms'])->name('terms');
            Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');
        });
});

/*
|--------------------------------------------------------------------------
| Back office (client-side SPA, SSR disabled)
|--------------------------------------------------------------------------
*/

Route::middleware(['locale', 'no-ssr', 'inertia'])->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/admin/login', [LoginController::class, 'create'])->name('admin.login');
        Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.store');
        Route::post('/admin/login/demo', [LoginController::class, 'demo'])->name('admin.login.demo');
    });

    Route::get('/locale/{locale}', LocaleController::class)->name('locale.update');

    Route::middleware(['auth', 'idle'])->group(function (): void {
        Route::post('/admin/logout', [LoginController::class, 'destroy'])->name('admin.logout');

        Route::get('/admin', DashboardController::class)->name('admin.dashboard');
        Route::get('/admin/ui-kit', UiKitController::class)->name('admin.ui-kit');
        Route::get('/admin/branches/{branch}', [BranchController::class, 'show'])->name('admin.branches.show');

        Route::get('/admin/projects', [ProjectController::class, 'index'])->name('admin.projects.index');
        Route::get('/admin/projects/{project}', [ProjectController::class, 'show'])->name('admin.projects.show');
        Route::post('/admin/units/{unit}/hold', [UnitController::class, 'hold'])->name('admin.units.hold');
        Route::post('/admin/units/{unit}/release', [UnitController::class, 'release'])->name('admin.units.release');

        Route::get('/admin/leads', [LeadController::class, 'index'])->name('admin.leads.index');
        Route::post('/admin/leads', [LeadController::class, 'store'])->name('admin.leads.store');
        Route::get('/admin/leads/{lead}', [LeadController::class, 'show'])->name('admin.leads.show');
        Route::post('/admin/leads/{lead}/stage', [LeadController::class, 'stage'])->name('admin.leads.stage');
        Route::post('/admin/leads/{lead}/activity', [LeadController::class, 'activity'])->name('admin.leads.activity');
        Route::post('/admin/leads/{lead}/visits', [LeadController::class, 'visit'])->name('admin.leads.visits');
        Route::post('/admin/visits/{visit}/complete', [LeadController::class, 'completeVisit'])->name('admin.visits.complete');

        Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
        Route::post('/admin/customers', [CustomerController::class, 'store'])->name('admin.customers.store');

        Route::get('/admin/bookings', [BookingController::class, 'index'])->name('admin.bookings.index');
        Route::get('/admin/bookings/create', [BookingController::class, 'create'])->name('admin.bookings.create');
        Route::post('/admin/bookings', [BookingController::class, 'store'])->name('admin.bookings.store');
        Route::get('/admin/bookings/{booking}', [BookingController::class, 'show'])->name('admin.bookings.show');
        Route::post('/admin/bookings/{booking}/agreement', [BookingController::class, 'signAgreement'])->name('admin.bookings.agreement');
        Route::post('/admin/bookings/{booking}/receipts', [BookingController::class, 'collect'])->name('admin.bookings.receipts');
        Route::get('/admin/units-for-project', [BookingController::class, 'units'])->name('admin.booking.units');
        Route::get('/admin/unit-price', [BookingController::class, 'price'])->name('admin.booking.price');

        Route::get('/admin/collections', [CollectionController::class, 'index'])->name('admin.collections.index');
    });
});
