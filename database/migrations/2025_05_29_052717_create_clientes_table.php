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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cliente')->unique();
            $table->string('cedula_identidad')->unique()->nullable();
            $table->string('nombres');
            $table->string('apellidos')->nullable();
            $table->string('celular')->nullable();
            $table->string('calle_avenida')->nullable();
            $table->string('zona_barrio')->nullable();
            $table->string('referencia_direccion')->nullable();
            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();
            $table->unsignedBigInteger('ruta_id');
            $table->foreign('ruta_id')->references('id')->on('rutas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
