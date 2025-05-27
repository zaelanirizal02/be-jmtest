<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DiagnosisController extends Controller
{
    private function checkDokterRole()
    {
         if (!Auth::check() || !in_Array(Auth::user()->role, ['dokter','superadmin'])) {
            return response()->json([
                'pesan' => 'Unauthorized. Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], 403);
        }
        return null;
    }

    public function index($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $diagnoses = Diagnosis::with('user')
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json([
            'pesan' => 'Data diagnosis berhasil diambil',
            'data' => [
                'patient' => $patient,
                'diagnoses' => $diagnoses
            ]
        ]);
    }

    public function store(Request $request, $patientId)
    {
        // Check role
        $roleCheck = $this->checkDokterRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        // Memastikan pasien ada
        $patient = Patient::findOrFail($patientId);

        $validator = Validator::make($request->all(), [
            'keluhan' => 'required|string',
            'diagnosis' => 'required|string',
            'tindakan' => 'nullable|string',
            'catatan' => 'nullable|string'
        ], [
            'required' => ':attribute wajib diisi',
            'string' => ':attribute harus berupa teks'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $diagnosis = Diagnosis::create([
            'patient_id' => $patientId,
            'keluhan' => $request->keluhan,
            'diagnosis' => $request->diagnosis,
            'tindakan' => $request->tindakan,
            'catatan' => $request->catatan,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'pesan' => 'Data diagnosis berhasil ditambahkan',
            'data' => $diagnosis
        ], 201);
    }

    public function show($patientId, $id)
    {
        $diagnosis = Diagnosis::with(['user', 'patient'])
            ->where('patient_id', $patientId)
            ->findOrFail($id);

        return response()->json([
            'pesan' => 'Data diagnosis berhasil diambil',
            'data' => $diagnosis
        ]);
    }

    public function update(Request $request, $patientId, $id)
    {
        // Check role
        $roleCheck = $this->checkDokterRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $diagnosis = Diagnosis::where('patient_id', $patientId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'keluhan' => 'required|string',
            'diagnosis' => 'required|string',
            'tindakan' => 'nullable|string',
            'catatan' => 'nullable|string'
        ], [
            'required' => ':attribute wajib diisi',
            'string' => ':attribute harus berupa teks'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $diagnosis->update($request->all());

        return response()->json([
            'pesan' => 'Data diagnosis berhasil diperbarui',
            'data' => $diagnosis
        ]);
    }

    public function destroy($patientId, $id)
    {
        // Check role
        $roleCheck = $this->checkDokterRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $diagnosis = Diagnosis::where('patient_id', $patientId)->findOrFail($id);
        $diagnosis->delete();

        return response()->json([
            'pesan' => 'Data diagnosis berhasil dihapus'
        ]);
    }
}
