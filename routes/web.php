<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Community;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site (CMS-driven, design preserved from the original website)
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');

/*
| Auth + application flow. URLs mirror the old WordPress site
| (/login, /registration, /lost-password, /my-account).
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'show'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'store'])->middleware('throttle:20,1');
    Route::get('/registration', [Auth\ApplicationController::class, 'create'])->name('apply');
    Route::post('/registration', [Auth\ApplicationController::class, 'store'])->middleware('throttle:6,1')->name('apply.store');
    Route::get('/lost-password', [Auth\PasswordController::class, 'request'])->name('password.request');
    Route::post('/lost-password', [Auth\PasswordController::class, 'email'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [Auth\PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [Auth\PasswordController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');
});
Route::get('/registration/thanks', [Auth\ApplicationController::class, 'thanks'])->name('apply.thanks');
Route::redirect('/apply', '/registration');
Route::post('/logout', [Auth\LoginController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/my-account', fn () => auth()->check() ? redirect()->route('community.index') : redirect()->route('login'));
Route::get('/home-with-blogs', fn () => redirect()->route('community.index'));

/*
| Private community - approved members only, never indexed.
*/
Route::middleware(['auth', 'member'])->prefix('community')->name('community.')->group(function () {
    Route::get('/', [Community\PostController::class, 'index'])->name('index');
    Route::get('/new', [Community\PostController::class, 'create'])->name('create');
    Route::post('/new', [Community\PostController::class, 'store'])->name('store');
    Route::get('/profile', [Community\AccountController::class, 'edit'])->name('profile');
    Route::put('/profile', [Community\AccountController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Community\AccountController::class, 'password'])->name('profile.password');
    Route::get('/notifications', [Community\AccountController::class, 'notifications'])->name('notifications');
    Route::post('/comments/{comment}/report', [Community\PostController::class, 'reportComment'])->name('comments.report');
    Route::put('/comments/{comment}', [Community\CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [Community\CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/{post}', [Community\PostController::class, 'show'])->name('show');
    Route::post('/{post}/comments', [Community\CommentController::class, 'store'])->middleware('throttle:30,1')->name('comments.store');
    Route::post('/{post}/save', [Community\PostController::class, 'toggleSave'])->name('save');
    Route::post('/{post}/report', [Community\PostController::class, 'report'])->name('report');
});

/*
| Admin / moderation dashboard.
*/
Route::middleware(['auth', 'role:moderator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{user}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{user}/approve', [Admin\ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{user}/reject', [Admin\ApplicationController::class, 'reject'])->name('applications.reject');

    Route::get('/members', [Admin\MemberController::class, 'index'])->name('members.index');
    Route::post('/members/{user}', [Admin\MemberController::class, 'update'])->name('members.update');

    Route::resource('posts', Admin\PostController::class)->except('show');
    Route::post('/posts/{post}/toggle', [Admin\PostController::class, 'toggle'])->name('posts.toggle');

    Route::get('/reports', [Admin\ModerationController::class, 'reports'])->name('reports');
    Route::post('/reports/{report}', [Admin\ModerationController::class, 'resolve'])->name('reports.resolve');
    Route::get('/comments', [Admin\ModerationController::class, 'comments'])->name('comments');
    Route::delete('/comments/{comment}', [Admin\ModerationController::class, 'destroyComment'])->name('comments.destroy');
});

// Content + settings: Aamir (super admin) only.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/pages', [Admin\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}', [Admin\PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});

// Any other public page slug (about-me, professional, future CMS pages) - keep LAST.
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('page');
