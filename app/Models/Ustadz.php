<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ustadz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'nama_ustadz', 'kelamin', 'tempat_lahir', 'tgl_lahir', 'no_hp'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

   public function subKelas()
    {
        return $this->belongsToMany(SubKelas::class,'ustadz_sub_kelas','ustadz_id','sub_kelas_id');
    }
}
