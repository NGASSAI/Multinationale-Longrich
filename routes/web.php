<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\AdminManagementController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use Illuminate\Support\Facades\Route;

// ==================== AUTH (invités uniquement) ====================
Route::middleware("guest")->group(function () {
    Route::get("/inscription", [AuthController::class, "showRegister"])->name("register");
    Route::post("/inscription", [AuthController::class, "register"]);

    Route::get("/connexion", [AuthController::class, "showLogin"])->name("login");
    Route::post("/connexion", [AuthController::class, "login"]);

    Route::get("/mot-de-passe-oublie", [PasswordResetController::class, "showEmailForm"])->name("password.request");
    Route::post("/verifier-identite", [PasswordResetController::class, "showVerifyForm"])->name("password.verify.form");
    Route::post("/confirmer-identite", [PasswordResetController::class, "verifyIdentity"])->name("password.verify");
    Route::get("/reinitialiser-mot-de-passe", [PasswordResetController::class, "showResetForm"])->name("password.reset.form");
    Route::post("/reinitialiser-mot-de-passe", [PasswordResetController::class, "reset"])->name("password.update");
});

// ==================== AUTH (connectés) ====================
Route::middleware("auth")->group(function () {
    Route::post("/deconnexion", [AuthController::class, "logout"])->name("logout");
    Route::put("/profil/nom-secret", [SecretNameController::class, "update"])->name("profile.secret-name.update");
    Route::get("/dashboard", fn () => \Inertia\Inertia::render("Client/Dashboard"))->name("dashboard");
});

// ==================== CATALOGUE (public) ====================
Route::get("/catalogue", [CatalogController::class, "index"])->name("catalog.index");
Route::get("/produit/{product:slug}", [CatalogController::class, "show"])->name("catalog.show");

// ==================== CLIENT (connectés) — likes, commentaires ====================
Route::middleware("auth")->group(function () {
    Route::post("/produit/{product}/like", [LikeController::class, "toggle"])->name("products.like");
    Route::post("/produit/{product}/commentaire", [CommentController::class, "store"])->name("comments.store");
    Route::delete("/produit/{product}/commentaire/{comment}", [CommentController::class, "destroy"])->name("comments.destroy");
});

// ==================== CLIENT (connectés) — commandes ====================
Route::middleware("auth")->prefix("mes-commandes")->name("client.orders.")->group(function () {
    Route::get("/", [ClientOrderController::class, "index"])->name("index");
    Route::post("/", [ClientOrderController::class, "store"])->middleware("throttle:orders")->name("store");
    Route::get("/{order}", [ClientOrderController::class, "show"])->name("show");
});

// ==================== CLIENT (connectés) — chat ====================
Route::middleware("auth")->group(function () {
    Route::get("/mon-chat", [ChatController::class, "startOrShow"])->name("chat.client");
    Route::post("/chat/{conversation}/message", [ChatController::class, "sendMessage"])->middleware("throttle:chat-messages")->name("chat.send");
    Route::post("/chat/{conversation}/lu", [ChatController::class, "markAsRead"])->name("chat.read");
});

// ==================== CLIENT (connectés) — notifications ====================
Route::middleware("auth")->prefix("notifications")->name("notifications.")->group(function () {
    Route::get("/", [NotificationController::class, "index"])->name("index");
    Route::get("/count", [NotificationController::class, "unreadCount"])->name("count");
    Route::patch("/{id}/lu", [NotificationController::class, "markAsRead"])->name("read");
    Route::patch("/tout-lire", [NotificationController::class, "markAllAsRead"])->name("read-all");
});

// ==================== ADMIN ====================
Route::middleware(["auth", "admin"])->prefix("admin")->name("admin.")->group(function () {
    Route::get("/dashboard", fn () => \Inertia\Inertia::render("Admin/Dashboard"))->name("dashboard");

    Route::resource("categories", CategoryController::class)->except(["create", "edit", "show"]);
    Route::resource("products", ProductController::class)->except(["create", "edit", "show"]);

    Route::get("/commentaires", [CommentModerationController::class, "index"])->name("comments.index");
    Route::patch("/commentaires/{comment}/approbation", [CommentModerationController::class, "toggleApproval"])->name("comments.toggle");
    Route::delete("/commentaires/{comment}", [CommentModerationController::class, "destroy"])->name("comments.destroy");

    Route::get("/chat", [ChatController::class, "index"])->name("chat.index");
    Route::get("/chat/{conversation}", [ChatController::class, "show"])->name("chat.show");

    Route::get("/statistiques", [StatsController::class, "index"])->name("stats.index");
});

Route::middleware(["auth", "admin"])->prefix("admin/commandes")->name("admin.orders.")->group(function () {
    Route::get("/", [AdminOrderController::class, "index"])->name("index");
    Route::post("/", [AdminOrderController::class, "store"])->name("store");
    Route::get("/{order}", [AdminOrderController::class, "show"])->name("show");
    Route::patch("/{order}/statut", [AdminOrderController::class, "updateStatus"])->name("status");
    Route::patch("/{order}/paiement", [AdminOrderController::class, "updatePaymentStatus"])->name("payment");
    Route::patch("/{order}/annuler", [AdminOrderController::class, "cancel"])->name("cancel");
});

// ==================== SUPER ADMIN ====================
Route::middleware(["auth", "super_admin"])->prefix("superadmin")->name("superadmin.")->group(function () {
    Route::get("/dashboard", [SuperAdminDashboard::class, "index"])->name("dashboard");

    Route::get("/admins", [AdminManagementController::class, "index"])->name("admins.index");
    Route::post("/admins", [AdminManagementController::class, "store"])->name("admins.store");
    Route::patch("/admins/{admin}/statut", [AdminManagementController::class, "toggleStatus"])->name("admins.status");
    Route::delete("/admins/{admin}", [AdminManagementController::class, "destroy"])->name("admins.destroy");

    Route::get("/parametres", [SettingsController::class, "index"])->name("settings.index");
    Route::put("/parametres", [SettingsController::class, "update"])->name("settings.update");
});
