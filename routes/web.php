<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\SecretNameController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Client\CatalogController;
use App\Http\Controllers\Client\LikeController;
use App\Http\Controllers\Client\CommentController;
use App\Http\Controllers\Admin\CommentModerationController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\ChatController;

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);

    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);

    Route::middleware('guest')->group(function () {
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'showEmailForm'])->name('password.request');
    Route::post('/verifier-identite', [PasswordResetController::class, 'showVerifyForm'])->name('password.verify.form');
    Route::post('/confirmer-identite', [PasswordResetController::class, 'verifyIdentity'])->name('password.verify');

    Route::get('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::put('/profil/nom-secret', [SecretNameController::class, 'update'])->name('profile.secret-name.update');
});




});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => \Inertia\Inertia::render('Client/Dashboard'))->name('dashboard');

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn () => \Inertia\Inertia::render('Admin/Dashboard'))->name('admin.dashboard');
    });

    Route::middleware('super_admin')->prefix('superadmin')->group(function () {
        Route::get('/dashboard', fn () => \Inertia\Inertia::render('SuperAdmin/Dashboard'))->name('superadmin.dashboard');
    });
});

// Public — catalogue
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/produit/{product:slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Admin — CRUD
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
});

// Client — authentifié uniquement
Route::middleware('auth')->group(function () {
    Route::post('/produit/{product}/like', [LikeController::class, 'toggle'])->name('products.like');
    Route::post('/produit/{product}/commentaire', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/produit/{product}/commentaire/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// Admin — modération
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/commentaires', [CommentModerationController::class, 'index'])->name('comments.index');
    Route::patch('/commentaires/{comment}/approbation', [CommentModerationController::class, 'toggleApproval'])->name('comments.toggle');
    Route::delete('/commentaires/{comment}', [CommentModerationController::class, 'destroy'])->name('comments.destroy');
});

// Client
Route::middleware('auth')->prefix('mes-commandes')->name('client.orders.')->group(function () {
    Route::get('/', [ClientOrderController::class, 'index'])->name('index');
    Route::post('/', [ClientOrderController::class, 'store'])->name('store');
    Route::get('/{order}', [ClientOrderController::class, 'show'])->name('show');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin/commandes')->name('admin.orders.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::post('/', [AdminOrderController::class, 'store'])->name('store'); // création manuelle (WhatsApp/tel)
    Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
    Route::patch('/{order}/statut', [AdminOrderController::class, 'updateStatus'])->name('status');
    Route::patch('/{order}/paiement', [AdminOrderController::class, 'updatePaymentStatus'])->name('payment');
    Route::patch('/{order}/annuler', [AdminOrderController::class, 'cancel'])->name('cancel');
});

Route::middleware('auth')->group(function () {
    // Client
    Route::get('/mon-chat', [ChatController::class, 'startOrShow'])->name('chat.client');

    // Commun (vérifié dans le contrôleur)
    Route::post('/chat/{conversation}/message', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/{conversation}/lu', [ChatController::class, 'markAsRead'])->name('chat.read');

    // Admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    });
});
