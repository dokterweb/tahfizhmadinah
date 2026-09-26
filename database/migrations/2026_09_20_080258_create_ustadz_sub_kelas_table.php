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
        Schema::create('ustadz_sub_kelas', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('ustadz_id')->constrained('ustadzs')->cascadeOnDelete();
            $table->unsignedInteger('sub_kelas_id');
            $table->foreign('sub_kelas_id')->references('id')->on('sub_kelas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['ustadz_id', 'sub_kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ustadz_sub_kelas');
    }
};
