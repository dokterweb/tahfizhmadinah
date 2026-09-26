<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelasnya extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_kelas',
    ];

    public function subKelas()
    {
        return $this->hasMany(SubKelas::class, 'kelas_id', 'id');
    }

    public function siswas()
    {
        return $this->hasManyThrough(
            Siswa::class,
            SubKelas::class,
            'kelas_id',
            'sub_kelas_id',
            'id',
            'id'
        );
    }

    public function ustadzs()
    {
        return $this->hasManyThrough(
            Ustadz::class,
            SubKelas::class,
            'kelas_id',
            'sub_kelas_id',
            'id',
            'id'
        );
    }
}