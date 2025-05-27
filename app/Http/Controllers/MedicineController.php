<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group OBAT
 * Endpoint ini digunakan untuk mengola data obat.
 */
class MedicineController extends Controller
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

    public function index(Request $request)
    {
        $query = Medicine::with(['createdBy', 'updatedBy']);

        // Filter berdasarkan kategori jika ada
        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter berdasarkan pencarian nama atau kode
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $medicines = $query->latest()->get();

        return response()->json([
            'pesan' => 'Data obat berhasil diambil',
            'data' => $medicines
        ]);
    }

    public function store(Request $request)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|unique:medicines',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0'
        ], [
            'required' => ':attribute wajib diisi',
            'string' => ':attribute harus berupa teks',
            'max' => ':attribute maksimal :max karakter',
            'unique' => ':attribute sudah terdaftar',
            'numeric' => ':attribute harus berupa angka',
            'integer' => ':attribute harus berupa angka bulat',
            'min' => ':attribute minimal :min'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $medicine = Medicine::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'pesan' => 'Data obat berhasil ditambahkan',
            'data' => $medicine
        ], 201);
    }

    public function show($id)
    {
        $medicine = Medicine::with(['createdBy', 'updatedBy'])->findOrFail($id);

        return response()->json([
            'pesan' => 'Data obat berhasil diambil',
            'data' => $medicine
        ]);
    }

    public function update(Request $request, $id)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $medicine = Medicine::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'sometimes|string|unique:medicines,kode,' . $id,
            'nama' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'sometimes|string|max:255',
            'satuan' => 'sometimes|string|max:50',
            'harga' => 'sometimes|numeric|min:0',
            'stok' => 'sometimes|integer|min:0'
        ], [
            'string' => ':attribute harus berupa teks',
            'max' => ':attribute maksimal :max karakter',
            'unique' => ':attribute sudah terdaftar',
            'numeric' => ':attribute harus berupa angka',
            'integer' => ':attribute harus berupa angka bulat',
            'min' => ':attribute minimal :min'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Only update fields that are present in the request
        $updateData = array_filter($request->only([
            'kode',
            'nama',
            'deskripsi',
            'kategori',
            'satuan',
            'harga',
            'stok'
        ]));

        // Always add updated_by
        $updateData['updated_by'] = Auth::id();

        $medicine->update($updateData);

        return response()->json([
            'pesan' => 'Data obat berhasil diperbarui',
            'data' => $medicine
        ]);
    }

    public function destroy($id)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $medicine = Medicine::findOrFail($id);
        $medicine->delete();

        return response()->json([
            'pesan' => 'Data obat berhasil dihapus'
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        // Check role
        $roleCheck = $this->checkApotekerRole();
        if ($roleCheck) {
            return $roleCheck;
        }

        $medicine = Medicine::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'adjustment' => 'required|integer',
            'keterangan' => 'required|string'
        ], [
            'required' => ':attribute wajib diisi',
            'integer' => ':attribute harus berupa angka bulat',
            'string' => ':attribute harus berupa teks'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'pesan' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $newStock = $medicine->stok + $request->adjustment;

        if ($newStock < 0) {
            return response()->json([
                'pesan' => 'Stok tidak mencukupi',
                'errors' => ['stok' => ['Stok tidak boleh kurang dari 0']]
            ], 422);
        }

        $medicine->update([
            'stok' => $newStock,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'pesan' => 'Stok obat berhasil diperbarui',
            'data' => $medicine
        ]);
    }
}
