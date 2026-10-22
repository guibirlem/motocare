<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despesas', fn (Blueprint $table) => $table->text('descricao')->nullable());
    }

    public function down(): void
    {
        Schema::table('despesas', fn (Blueprint $table) => $table->dropColumn('descricao'));
    }
};
