<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->text('push_subscription')->nullable();
            $table->char('push_endpoint_hash', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique(['push_endpoint_hash']);
            $table->dropColumn(['push_subscription', 'push_endpoint_hash']);
        });
    }
};
