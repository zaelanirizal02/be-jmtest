<?php

namespace App\Http\Controllers;

use App\Models\PatientHealth;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PatientHealthController extends Controller
{
    private function checkPerawatRole()
    {
        if (!Auth::check() || Auth::user()->role !== 'perawat') {
            return response()->json([
                'pesan' => 'Unauthorized. Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], 403);
        }
        return null;
    }

    public function index($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $healthData = PatientHealth::with('user')
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json([
            'pesan' => 'Data kesehatan berhasil diambil',
            'data' => [
                'patient' => $patient,
                'health_records' => $healthData
            ]
        ]);
    }

    public function store(Request $request, $patientId)
    {
        // Check role
        $roleCheck = $this->checkPerawatRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        // Memastikan pasien ada
        $patient = Patient::findOrFail($patientId);

        $validator = Validator::make($request->all(), [
            'berat_badan' => 'required|numeric|min:0|max:500',
            'tekanan_darah_sistole' => 'required|integer|min:0|max:300',
            'tekanan_darah_diastole' => 'required|integer|min:0|max:200',
        ], [
            'required' => ':attribute wajib diisi',
            'numeric' => ':attribute harus berupa angka',
            'integer' => ':attribute harus berupa angka bulat',
            'min' => ':attribute minimal :min',
            'max' => ':attribute maksimal :max',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $healthData = PatientHealth::create([
            'patient_id' => $patientId,
            'berat_badan' => $request->berat_badan,
            'tekanan_darah_sistole' => $request->tekanan_darah_sistole,
            'tekanan_darah_diastole' => $request->tekanan_darah_diastole,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'pesan' => 'Data kesehatan berhasil ditambahkan',
            'data' => $healthData
        ], 201);
    }

    public function show($patientId, $id)
    {
        $healthData = PatientHealth::with(['user', 'patient'])
            ->where('patient_id', $patientId)
            ->findOrFail($id);

        return response()->json([
            'pesan' => 'Data kesehatan berhasil diambil',
            'data' => $healthData
        ]);
    }

    public function update(Request $request, $patientId, $id)
    {
        // Check role
        $roleCheck = $this->checkPerawatRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $healthData = PatientHealth::where('patient_id', $patientId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'berat_badan' => 'required|numeric|min:0|max:500',
            'tekanan_darah_sistole' => 'required|integer|min:0|max:300',
            'tekanan_darah_diastole' => 'required|integer|min:0|max:200',
        ], [
            'required' => ':attribute wajib diisi',
            'numeric' => ':attribute harus berupa angka',
            'integer' => ':attribute harus berupa angka bulat',
            'min' => ':attribute minimal :min',
            'max' => ':attribute maksimal :max',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $healthData->update($request->all());

        return response()->json([
            'pesan' => 'Data kesehatan berhasil diperbarui',
            'data' => $healthData
        ]);
    }

    public function destroy($patientId, $id)
    {
        // Check role
        $roleCheck = $this->checkPerawatRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $healthData = PatientHealth::where('patient_id', $patientId)->findOrFail($id);
        $healthData->delete();

        return response()->json([
            'pesan' => 'Data kesehatan berhasil dihapus'
        ]);
    }
}
