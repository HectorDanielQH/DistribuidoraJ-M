<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'entregado_repartidor_at')) {
                $table->dateTime('entregado_repartidor_at')->nullable()->index();
            }

            if (! Schema::hasColumn('pedidos', 'entregado_repartidor_por')) {
                $table->unsignedBigInteger('entregado_repartidor_por')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'entregado_repartidor_por')) {
                $table->dropColumn('entregado_repartidor_por');
            }

            if (Schema::hasColumn('pedidos', 'entregado_repartidor_at')) {
                $table->dropColumn('entregado_repartidor_at');
            }
        });
    }
};
