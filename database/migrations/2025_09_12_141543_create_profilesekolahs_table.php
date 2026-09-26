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
        Schema::create('profilesekolahs', function (Blueprint $table) {
            $table->tinyInteger('id');
            $table->string('nama_sekolah')->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo_path')->nullable(); // path file di storage
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profilesekolahs');
    }
};
