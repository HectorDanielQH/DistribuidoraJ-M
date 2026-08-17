<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'reparto_programado_fecha')) {
                $table->date('reparto_programado_fecha')->nullable()->index();
            }

            if (! Schema::hasColumn('pedidos', 'reparto_programado_por')) {
                $table->unsignedBigInteger('reparto_programado_por')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'reparto_programado_por')) {
                $table->dropColumn('reparto_programado_por');
            }

            if (Schema::hasColumn('pedidos', 'reparto_programado_fecha')) {
                $table->dropColumn('reparto_programado_fecha');
            }
        });
    }
};
