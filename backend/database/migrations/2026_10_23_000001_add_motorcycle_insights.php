<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('nome');
            $table->timestamps();
            $table->unique(['usuario_id', 'nome']);
        });
        Schema::create('posto_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posto_id')->constrained('postos')->cascadeOnDelete();
            $table->string('combustivel', 40);
            $table->decimal('preco_litro', 10, 2);
            $table->timestamps();
            $table->unique(['posto_id', 'combustivel']);
        });
        Schema::table('motocicletas', function (Blueprint $table) {
            $table->decimal('capacidade_tanque', 5, 2)->nullable();
            $table->decimal('volume_reserva', 5, 2)->nullable();
        });
        Schema::table('despesas', function (Blueprint $table) {
            $table->foreignId('posto_id')->nullable()->constrained('postos')->nullOnDelete();
            $table->string('combustivel', 40)->nullable();
            $table->unsignedInteger('km_abastecimento')->nullable();
            $table->boolean('tanque_cheio')->nullable();
        });
        Schema::table('manutencoes', function (Blueprint $table) {
            $table->string('natureza', 20)->nullable();
        });
        Schema::table('alertas', function (Blueprint $table) {
            $table->string('origem', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('alertas', fn (Blueprint $table) => $table->dropColumn('origem'));
        Schema::table('manutencoes', fn (Blueprint $table) => $table->dropColumn('natureza'));
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('posto_id');
            $table->dropColumn(['combustivel', 'km_abastecimento', 'tanque_cheio']);
        });
        Schema::table('motocicletas', fn (Blueprint $table) => $table->dropColumn(['capacidade_tanque', 'volume_reserva']));
        Schema::dropIfExists('posto_precos');
        Schema::dropIfExists('postos');
    }
};
