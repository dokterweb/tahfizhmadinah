<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sabqi_history extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable=['siswa_id','ustadz_id','sub_kelas_id', 'surat_id', 'surat_no', 'dariayat', 'sampaiayat', 'tgl_sabqi', 'nilai','keterangan'];

   public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function ustadz()
    {
        return $this->belongsTo(Ustadz::class, 'ustadz_id');
    }

    public function subKelas()
    {
        return $this->belongsTo(SubKelas::class, 'sub_kelas_id');
    }

    public function surat()
    {
        return $this->belongsTo(Madina::class, 'surat_id');
    }

   
}
