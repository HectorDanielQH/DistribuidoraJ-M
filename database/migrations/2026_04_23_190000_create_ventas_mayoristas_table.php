<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas_mayoristas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('id_cliente')->constrained('clientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('id_producto')->constrained('productos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('id_forma_venta')->constrained('forma_ventas')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('precio_unitario', 12, 2);
            $table->bigInteger('numero_venta')->index();
            $table->timestamp('fecha_venta');
            $table->integer('cantidad');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['id_usuario', 'fecha_venta']);
            $table->index(['id_cliente', 'fecha_venta']);
        });

        // Conserva los registros mayoristas ya creados en el flujo anterior.
        if (Schema::hasTable('pedidos') && Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
            $rolesMayorista = DB::table('roles')
                ->whereIn('name', ['mayorista', 'mayoristas'])
                ->pluck('id');

            if ($rolesMayorista->isNotEmpty()) {
                $pedidosMayoristas = DB::table('pedidos')
                    ->join('model_has_roles', function ($join) use ($rolesMayorista) {
                        $join->on('model_has_roles.model_id', '=', 'pedidos.id_usuario')
                            ->where('model_has_roles.model_type', '=', 'App\\Models\\User')
                            ->whereIn('model_has_roles.role_id', $rolesMayorista);
                    })
                    ->leftJoin('ventas_mayoristas', function ($join) {
                        $join->on('ventas_mayoristas.id_usuario', '=', 'pedidos.id_usuario')
                            ->on('ventas_mayoristas.id_cliente', '=', 'pedidos.id_cliente')
                            ->on('ventas_mayoristas.id_producto', '=', 'pedidos.id_producto')
                            ->on('ventas_mayoristas.id_forma_venta', '=', 'pedidos.id_forma_venta')
                            ->on('ventas_mayoristas.numero_venta', '=', 'pedidos.numero_pedido');
                    })
                    ->whereNull('ventas_mayoristas.id')
                    ->select(
                        'pedidos.id_usuario',
                        'pedidos.id_cliente',
                        'pedidos.id_producto',
                        'pedidos.id_forma_venta',
                        DB::raw('COALESCE(pedidos.precio_unitario, 0) AS precio_unitario'),
                        DB::raw('pedidos.numero_pedido AS numero_venta'),
                        DB::raw('COALESCE(pedidos.fecha_pedido, NOW()) AS fecha_venta'),
                        'pedidos.cantidad',
                        DB::raw('NULL::text AS observaciones'),
                        DB::raw('NOW() AS created_at'),
                        DB::raw('NOW() AS updated_at')
                    )
                    ->get();

                if ($pedidosMayoristas->isNotEmpty()) {
                    DB::table('ventas_mayoristas')->insert(json_decode(json_encode($pedidosMayoristas), true));
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas_mayoristas');
    }
};
