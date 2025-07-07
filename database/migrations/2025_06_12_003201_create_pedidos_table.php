<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_forma_venta');
            $table->unsignedBigInteger('numero_pedido');
            $table->dateTime('fecha_pedido');
            $table->dateTime('fecha_entrega')->nullable();
            $table->integer('cantidad');
            $table->boolean('estado_pedido')->default(false);
            $table->boolean('promocion')->default(false);
            $table->integer('descripcion_descuento_porcentaje')->nullable();
            $table->string('descripcion_regalo')->nullable();
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_cliente')->references('id')->on('clientes');
            $table->foreign('id_producto')->references('id')->on('productos');
            $table->foreign('id_forma_venta')->references('id')->on('forma_ventas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
