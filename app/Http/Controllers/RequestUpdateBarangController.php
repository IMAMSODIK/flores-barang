<?php

namespace App\Http\Controllers;

use App\Models\RequestUpdateBarang;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestUpdateBarangRequest;
use App\Http\Requests\UpdateRequestUpdateBarangRequest;
use App\Models\Barang;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RequestUpdateBarangController extends Controller
{
    public function index()
    {
        try {
            $requestUpdate = RequestUpdateBarang::with(['barang', 'user'])->get();

            return response()->json([
                'success' => true,
                'data'    => $requestUpdate
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
                'barang_id'        => 'required|exists:barangs,id',
                'alasan'           => 'nullable|string',
            ]);

            $barang = Barang::findOrFail($validated['barang_id']);
            if ($barang->user_id != $validated['user_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke barang ini.'
                ], 403);
            }

            $requestUpdate = RequestUpdateBarang::create([
                'user_id' => $validated['user_id'],
                'barang_id' => $validated['barang_id'],
                'alasan' => $validated['alasan']
            ]);

            return response()->json([
                'success' => true,
                'data'    => $requestUpdate->load(['barang', 'user'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request update barang',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $requestUpdate = RequestUpdateBarang::with(['user', 'barang'])->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'data'    => $requestUpdate
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

    public function destroy($id)
    {
        try {
            $requestUpdate = RequestUpdateBarang::findOrFail($id);
            $user = auth()->user();

            if ($requestUpdate->user_id != $user->id && $user->role != 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus data ini.'
                ], 403);
            }

            $requestUpdate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Request Update Barang berhasil dihapus.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request Update Barang tidak ditemukan.'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Request Update barang.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
