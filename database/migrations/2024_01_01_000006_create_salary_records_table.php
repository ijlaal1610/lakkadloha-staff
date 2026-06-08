<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users');
            $table->enum('type', ['salary', 'advance', 'bonus', 'deduction'])->default('salary');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->date('record_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'upi', 'cheque'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_records');
    }
};
