<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    private function checkPendaftaranRole()
    {
        if (!Auth::check() || Auth::user()->role !== 'pendaftaran') {
            return response()->json([
                'pesan' => 'Unauthorized. Anda tidak memiliki akses untuk melakukan tindakan ini.',
            ], 403);
        }
        return null;
    }

    public function index()
{
    //nampilkan data pasien beserta data kesehatan pasien
    $pasien = Patient::with(['user', 'patientHealth'])->latest()->get();

    return response()->json([
        'pesan' => 'Data berhasil diambil',
        'data' => $pasien
    ]);
}


    public function store(Request $request)
    {
        // Check role
        $roleCheck = $this->checkPendaftaranRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:patients',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_telepon' => 'required|string|max:15'
        ], [
            'required' => ':attribute wajib diisi',
            'string' => ':attribute harus berupa teks',
            'max' => ':attribute maksimal :max karakter',
            'size' => ':attribute harus :size karakter',
            'unique' => ':attribute sudah terdaftar',
            'in' => ':attribute tidak valid',
            'date' => ':attribute harus berupa tanggal'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate nomor RM
        $latestPatient = Patient::latest()->first();
        $noRM = $latestPatient ? sprintf('%06d', intval(substr($latestPatient->no_rm, -6)) + 1) : '000001';

        $pasien = Patient::create([
            'no_rm' => $noRM,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'no_telepon' => $request->no_telepon,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'pesan' => 'Pasien berhasil didaftarkan',
            'data' => $pasien
        ], 201);
    }

    public function show($id)
    {
    // Check role
        $roleCheck = $this->checkPendaftaranRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $pasien = Patient::with('user')->findOrFail($id);
        return response()->json([
            'pesan' => 'Data berhasil diambil',
            'data' => $pasien
        ]);
    }

    public function update(Request $request, $id)
    {
        // Check role
        $roleCheck = $this->checkPendaftaranRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $pasien = Patient::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:patients,nik,' . $id,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_telepon' => 'required|string|max:15'
        ], [
            'required' => ':attribute wajib diisi',
            'string' => ':attribute harus berupa teks',
            'max' => ':attribute maksimal :max karakter',
            'size' => ':attribute harus :size karakter',
            'unique' => ':attribute sudah terdaftar',
            'in' => ':attribute tidak valid',
            'date' => ':attribute harus berupa tanggal'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $pasien->update($request->all());

        return response()->json([
            'pesan' => 'Data pasien berhasil diperbarui',
            'data' => $pasien
        ]);
    }

    public function destroy($id)
    {
        // Check role
        $roleCheck = $this->checkPendaftaranRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $pasien = Patient::findOrFail($id);
        $pasien->delete();

        return response()->json([
            'pesan' => 'Data pasien berhasil dihapus'
        ]);
    }
}
