<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motocicleta_id')->constrained('motocicletas')->cascadeOnDelete();
            $table->string('tipo_despesa');
            $table->decimal('valor', 10, 2);
            $table->decimal('litros', 8, 2)->nullable();
            $table->decimal('preco_litro', 10, 2)->nullable();
            $table->string('posto')->nullable();
            $table->date('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};