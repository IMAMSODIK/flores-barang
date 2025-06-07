<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\FotoBarang;
use App\Models\Kota;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

date_default_timezone_set('Asia/Jakarta');

class BarangController extends Controller
{
    public function index()
    {
        try {
            $barang = Barang::with(['fotoBarang', 'user'])->get();

            return response()->json([
                'success' => true,
                'data'    => $barang
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'          => 'required|exists:users,id',
                'kota_asal'        => 'required|exists:kotas,id',
                'kota_tujuan'      => 'required|exists:kotas,id',
                'deskripsi_barang' => 'required|string',
                'nama_pengirim'    => 'required|string|max:255',
                'hp_pengirim'      => 'required|string|max:20',
                'nama_penerima'    => 'required|string|max:255',
                'hp_penerima'      => 'required|string|max:20',
                'harga_awal'       => 'required|numeric|min:0',
                'status_bayar' => 'required|string|in:Lunas,Belum Bayar,Transfer',
                'status_barang' => 'required|string|in:Diterima,Belum Diterima',
                'foto_barang.*'    => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            DB::beginTransaction();

            $kotaTujuan = Kota::where('id', $request->kota_tujuan)->pluck('nama')->first()[0];
            $tanggalToday = date('dmy');

            $lastResi = Barang::where('kode_barang', 'like', $kotaTujuan . '-' . $tanggalToday . '-%')
                ->orderBy('kode_barang', 'desc')
                ->pluck('kode_barang')
                ->first();

            $nextNumber = 1;

            if ($lastResi) {
                $parts = explode('-', $lastResi);
                $lastNumber = intval($parts[count($parts) - 1]);
                $nextNumber = $lastNumber + 1;
            }

            $nextNumberFormatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $resi = $kotaTujuan . '-' . $tanggalToday . '-' . $nextNumberFormatted;

            $barang = Barang::create([
                'kode_barang'      => $resi,
                'user_id'          => $validated['user_id'],
                'kota_asal'        => $validated['kota_asal'],
                'kota_tujuan'      => $validated['kota_tujuan'],
                'deskripsi_barang' => $validated['deskripsi_barang'],
                'nama_pengirim'    => $validated['nama_pengirim'],
                'hp_pengirim'      => $validated['hp_pengirim'],
                'nama_penerima'    => $validated['nama_penerima'],
                'hp_penerima'      => $validated['hp_penerima'],
                'harga_awal'       => $validated['harga_awal'],
                'status_bayar'     => $validated['status_bayar'],
                'status_barang'    => $validated['status_barang']
            ]);

            if ($request->hasFile('foto_barang')) {
                foreach ($request->file('foto_barang') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/foto_barang', $filename);

                    FotoBarang::create([
                        'barang_id' => $barang->id,
                        'nama_file' => $filename
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $barang->load('fotoBarang')
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create barang',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $barang = Barang::with(['user', 'fotoBarang'])->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'data'    => $barang
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch barang'
            ], 500);
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'nama' => 'required|string|max:255|unique:kotas,nama,' . $id,
    //     ]);

    //     try {
    //         $kota = Kota::findOrFail($id);
    //         $kota->nama = $request->nama;
    //         $kota->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Kota updated successfully',
    //             'data' => $kota,
    //         ]);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Kota not found',
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update kota',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function destroy($id)
    // {
    //     try {
    //         $kota = Kota::findOrFail($id);
    //         $kota->delete();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Kota deleted'
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Kota not found'
    //         ], 404);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create kota',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
