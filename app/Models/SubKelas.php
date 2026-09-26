<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubKelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sub_kelas';

    protected $fillable = [
        'kelas_id',
        'nama_sub_kelas',
    ];

    public function kelasnya()
    {
        return $this->belongsTo(Kelasnya::class, 'kelas_id', 'id');
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class, 'sub_kelas_id', 'id');
    }

   public function ustadzs()
    {
        return $this->belongsToMany(Ustadz::class,'ustadz_sub_kelas','sub_kelas_id','ustadz_id');
    }
}