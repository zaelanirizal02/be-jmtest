<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'visit_date'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class);
    }

    public function vitalSigns()
    {
        return $this->hasOne(VitalSign::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }
}
