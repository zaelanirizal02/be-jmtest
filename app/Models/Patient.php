<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'no_rm',
        'nama',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_telepon',
        'created_by'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dataDiagnosa()
{
    return $this->hasMany(Diagnosis::class);
}

    public function patientHealth()
{
    return $this->hasMany(PatientHealth::class);
}
    public function dataObat()
{
    return $this->hasMany(PatientMedicine::class);
}

public function dataKesehatanTerbaru()
{
    return $this->hasOne(PatientHealth::class)->latestOfMany();
}


    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
