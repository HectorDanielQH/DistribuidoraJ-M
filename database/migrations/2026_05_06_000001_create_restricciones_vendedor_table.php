<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restricciones_vendedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('limite');
            $table->timestamps();

            $table->unique(['producto_id', 'vendedor_id'], 'restricciones_vendedor_producto_vendedor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restricciones_vendedor');
    }
};
