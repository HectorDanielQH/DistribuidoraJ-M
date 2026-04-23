<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'precio_unitario')) {
                $table->decimal('precio_unitario', 10, 2)->nullable()->after('id_forma_venta');
            }
        });

        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'precio_unitario')) {
                $table->decimal('precio_unitario', 10, 2)->nullable()->after('id_forma_venta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'precio_unitario')) {
                $table->dropColumn('precio_unitario');
            }
        });

        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'precio_unitario')) {
                $table->dropColumn('precio_unitario');
            }
        });
    }
};
