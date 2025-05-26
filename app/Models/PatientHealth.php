<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientHealth extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'berat_badan',
        'tekanan_darah_sistole',
        'tekanan_darah_diastole',
        'created_by'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
