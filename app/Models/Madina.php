<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Madina extends Model
{
    protected $table = 'madina';

   public function sabaqHistories()
    {
        return $this->hasMany(
            Sabaq_history::class,
            'surat_id',
            'sura_no'
        );
    }

    public function sabqiHistories()
    {
        return $this->hasMany(
            Sabqi_history::class,
            'surat_id',
            'sura_no'
        );
    }

    public function manzilHistories()
    {
        return $this->hasMany(
            Manzil_history::class,
            'surat_id',
            'sura_no'
        );
    }
}
