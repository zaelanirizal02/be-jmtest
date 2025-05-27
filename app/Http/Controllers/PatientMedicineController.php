<?php

namespace App\Http\Controllers;

use App\Models\PatientMedicine;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Diagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @group Patient Medicine
 * Menampilkan daftar patient medicine
 * Endpoint ini digunakan untuk mengambil semua data patient medicine.
 */
class PatientMedicineController extends Controller
{
    private function checkApotekerRole()
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['apoteker', 'superadmin'])) {
            return response()->json([
                'pesan' => 'Unauthorized. Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], 403);
        }
        return null;
    }

    public function index($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $medicines = PatientMedicine::with(['medicine', 'diagnosis', 'createdBy'])
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json([
            'pesan' => 'Data obat pasien berhasil diambil',
            'data' => [
                'patient' => $patient,
                'medicines' => $medicines
            ]
        ]);
    }

    public function store(Request $request, $patientId)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|exists:medicines,id',
            'diagnosis_id' => 'required|exists:diagnoses,id',
            'jumlah' => 'required|integer|min:1',
            'aturan_pakai' => 'required|string',
            'keterangan' => 'nullable|string'
        ], [
            'required' => ':attribute wajib diisi',
            'exists' => ':attribute tidak ditemukan',
            'integer' => ':attribute harus berupa angka bulat',
            'min' => ':attribute minimal :min',
            'string' => ':attribute harus berupa teks'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek stok obat
        $medicine = Medicine::findOrFail($request->medicine_id);
        if ($medicine->stok < $request->jumlah) {
            return response()->json([
                'pesan' => 'Stok obat tidak mencukupi',
                'errors' => ['stok' => ['Stok tersedia: ' . $medicine->stok]]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Kurangi stok obat
            $medicine->decrement('stok', $request->jumlah);
            $medicine->update(['updated_by' => Auth::id()]);

            // Buat resep
            $patientMedicine = PatientMedicine::create([
                'patient_id' => $patientId,
                'medicine_id' => $request->medicine_id,
                'diagnosis_id' => $request->diagnosis_id,
                'jumlah' => $request->jumlah,
                'aturan_pakai' => $request->aturan_pakai,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'pesan' => 'Resep obat berhasil ditambahkan',
                'data' => $patientMedicine
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'pesan' => 'Terjadi kesalahan saat menyimpan resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($patientId, $id)
    {
        $patientMedicine = PatientMedicine::with(['medicine', 'diagnosis', 'createdBy'])
            ->where('patient_id', $patientId)
            ->findOrFail($id);

        return response()->json([
            'pesan' => 'Data resep berhasil diambil',
            'data' => $patientMedicine
        ]);
    }

    public function destroy($patientId, $id)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        try {
            DB::beginTransaction();

            $patientMedicine = PatientMedicine::where('patient_id', $patientId)->findOrFail($id);

            // Kembalikan stok obat
            $medicine = Medicine::findOrFail($patientMedicine->medicine_id);
            $medicine->increment('stok', $patientMedicine->jumlah);
            $medicine->update(['updated_by' => Auth::id()]);

            // Hapus resep
            $patientMedicine->delete();

            DB::commit();

            return response()->json([
                'pesan' => 'Resep obat berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'pesan' => 'Terjadi kesalahan saat menghapus resep',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
