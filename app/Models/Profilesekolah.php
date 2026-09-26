<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profilesekolah extends Model
{
    protected $fillable = ['nama_sekolah', 'alamat', 'phone', 'logo_path'];

    // helper accessor: full url for logo
    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }
}
