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
            $table->string('nombre', 100);
            $table->integer('stock')->default(0);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('costo_unitario', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedors')->onDelete('set null');
            $table->string('estado', 10)->default('activo'); // enum sustituido por string
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
