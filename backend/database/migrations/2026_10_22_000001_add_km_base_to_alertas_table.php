<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->unsignedInteger('km_base')->nullable();
        });
        DB::table('alertas')->where('km_limite', '>', 0)->update(['km_base' => 0]);
        DB::table('alertas')->where('status', 'pendente')->where('km_limite', '>', 0)->update([
            'km_base' => DB::raw('(SELECT CASE WHEN motocicletas.km_atual < alertas.km_limite THEN motocicletas.km_atual ELSE 0 END FROM motocicletas WHERE motocicletas.id = alertas.motocicleta_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropColumn('km_base');
        });
    }
};
