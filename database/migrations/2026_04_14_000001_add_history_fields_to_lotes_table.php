<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->integer('stock_antes')->nullable()->after('fecha_vencimiento');
            $table->integer('stock_despues')->nullable()->after('stock_antes');
            $table->string('observacion')->nullable()->after('stock_despues');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['stock_antes', 'stock_despues', 'observacion']);
            $table->dropSoftDeletes();
        });
    }
};
