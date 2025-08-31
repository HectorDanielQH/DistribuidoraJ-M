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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad');
            $table->string('detalle_cantidad')->nullable();
            $table->decimal('precio_ingreso', 10, 2);
            $table->string('detalle_precio_ingreso')->nullable();
            $table->dateTime('ingreso_lote')->nullable();
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
