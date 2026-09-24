<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manutencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motocicleta_id')->constrained('motocicletas')->cascadeOnDelete();
            $table->string('tipo_servico');
            $table->text('descricao')->nullable();
            $table->decimal('valor', 10, 2);
            $table->string('status');
            $table->date('data');
            $table->unsignedInteger('km_servico')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencoes');
    }
};