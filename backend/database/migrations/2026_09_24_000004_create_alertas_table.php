<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motocicleta_id')->constrained('motocicletas')->cascadeOnDelete();
            $table->string('tipo_alerta');
            $table->string('nivel_urgencia');
            $table->unsignedInteger('km_limite')->nullable();
            $table->date('data_limite')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};