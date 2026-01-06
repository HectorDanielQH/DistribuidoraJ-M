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
        Schema::create('mayoristas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_venta');
            $table->decimal('precio_venta',8,2);
            $table->integer('equivalencia_cantidad');
            $table->boolean('estado')->default(true);
            $table->decimal('cantidad_minima_venta',5,2);
            $table->unsignedBigInteger('id_producto');
            $table->foreign('id_producto')->references('id')->on('productos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mayoristas');
    }
};
