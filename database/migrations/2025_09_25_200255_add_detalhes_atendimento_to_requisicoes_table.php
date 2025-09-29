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
        Schema::table('requisicoes', function (Blueprint $table) {
        $table->tinyInteger('nota_cliente')->nullable()->comment('1 a 5');
        $table->string('assinatura_gerente')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisicoes', function (Blueprint $table) {
              $table->dropColumn([
            'nota_cliente',
            'assinatura_gerente',
        ]);
        });
    }
};
