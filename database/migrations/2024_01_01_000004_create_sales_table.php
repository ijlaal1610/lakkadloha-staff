<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('staff_id')->constrained('users');
            $table->integer('quantity');
            $table->decimal('selling_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['completed', 'cancelled', 'refunded'])->default('completed');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sold_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users');
            $table->integer('quantity');
            $table->decimal('refund_amount', 12, 2);
            $table->text('reason')->nullable();
            $table->timestamp('refunded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_refunds');
        Schema::dropIfExists('sales');
    }
};
