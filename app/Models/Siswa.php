<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'sub_kelas_id', 'ustadz_id', 'kelamin', 'tempat_lahir', 'tgl_lahir', 'alamat', 'no_hp'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subKelas()
    {
        return $this->belongsTo(SubKelas::class, 'sub_kelas_id', 'id');
    }

    public function kelasnya()
    {
        return $this->hasOneThrough(
            Kelasnya::class,
            SubKelas::class,
            'id',
            'id',
            'sub_kelas_id',
            'kelas_id'
        );
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id', 'id');
    }

    public function iqros()
    {
        return $this->hasMany(iqro::class, 'siswa_id', 'id');
    }

   public function sabaqHistories()
    {
        return $this->hasMany(Sabaq_history::class,'siswa_id','id');
    }

    public function sabqiHistories()
    {
        return $this->hasMany(Sabqi_history::class,'siswa_id','id');
    }

    public function manzilHistories()
    {
        return $this->hasMany(Manzil_history::class,'siswa_id','id');
    }

    public function iqroHistories()
    {
        return $this->hasManyThrough(
            Iqro_history::class,
            Iqro::class,
            'siswa_id',
            'iqro_id',
            'id',
            'id'
        );
    }
}
