<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientMedicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'medicine_id',
        'diagnosis_id',
        'jumlah',
        'aturan_pakai',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
