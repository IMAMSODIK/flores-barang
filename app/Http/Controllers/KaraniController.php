<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class KaraniController extends Controller
{
    public function index()
    {
        try {
            $karanis = User::where('role', 'karani')->get();

            return response()->json([
                'success' => true,
                'data' => $karanis
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch karani list'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Request store karani', $request->all());
            $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'required|string|unique:users,username'
            ]);

            $karani = User::create([
                'name'     => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->username),
                'role'     => 'karani'
            ]);

            return response()->json([
                'success' => true,
                'data'    => $karani
            ], 201);
        } catch (ValidationException $e) {
            Log::info('Validation error store karani', [
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to create karani', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create karani',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $karani = User::where('role', 'karani')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => $karani
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karani not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch karani'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            Log::info('Request update karani', $request->all());

            $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'required|string|unique:users,username,' . $id,
                'password' => 'nullable|string|min:6'
            ]);

            $karani = User::where('role', 'karani')->findOrFail($id);

            $data = [
                'name'     => $request->name,
                'username' => $request->username
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $karani->update($data);

            return response()->json([
                'success' => true,
                'data'    => $karani
            ]);
        } catch (ModelNotFoundException $e) {

            Log::warning('Karani not found', [
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Karani not found'
            ], 404);
        } catch (ValidationException $e) {

            Log::warning('Validation error update karani', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {

            Log::error('Failed update karani', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update karani'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $karani = User::where('role', 'karani')->findOrFail($id);
            $karani->delete();

            return response()->json([
                'success' => true,
                'message' => 'Karani deleted'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Karani not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete karani'
            ], 500);
        }
    }
}
