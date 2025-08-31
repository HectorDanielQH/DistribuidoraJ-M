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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proveedor');
            $table->unsignedBigInteger('id_marca');
            $table->unsignedBigInteger('id_linea');
            $table->string('codigo')->unique();
            $table->string('nombre_producto');
            $table->string('descripcion_producto');
            $table->integer('cantidad');
            $table->string('detalle_cantidad');
            $table->decimal('precio_compra',8,2);
            $table->string('detalle_precio_compra');
            $table->string('presentacion')->nullable();
            $table->boolean('promocion')->default(false);
            $table->integer('descripcion_descuento_porcentaje')->nullable();
            $table->string('descripcion_regalo')->nullable();
            $table->string('foto_producto')->nullable();
            $table->foreign('id_proveedor')->references('id')->on('proveedors');
            $table->foreign('id_marca')->references('id')->on('marcas');
            $table->foreign('id_linea')->references('id')->on('lineas');
            $table->boolean('estado_de_baja')->default(false);
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
