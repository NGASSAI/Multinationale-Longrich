<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            // Nullable : une commande peut être créée par l'admin pour un client
            // qui a contacté via WhatsApp sans avoir de compte sur le site
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Coordonnées du client, toujours renseignées (même si user_id existe,
            // utile pour livraison et historique si le compte est supprimé plus tard)
            $table->string('client_name');
            $table->string('client_phone');
            $table->string('client_address')->nullable();

            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');
            $table->enum('payment_method', ['cash_on_delivery', 'mobile_money', 'other'])
                ->default('cash_on_delivery');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');

            $table->enum('source', ['website', 'whatsapp', 'phone_call', 'admin'])
                ->default('website');

            $table->decimal('total', 12, 2);
            $table->text('notes')->nullable();

            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
