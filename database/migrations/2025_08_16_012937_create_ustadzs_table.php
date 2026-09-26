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
        Schema::create('ustadzs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('sub_kelas_id')->nullable();
            $table->foreign('sub_kelas_id')->references('id')->on('sub_kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('kelamin', ['laki-laki', 'perempuan']); 
            $table->string('tempat_lahir');
            $table->date('tgl_lahir');
            $table->string('no_hp');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ustadzs');
    }
};
