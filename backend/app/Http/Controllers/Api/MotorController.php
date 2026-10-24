<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MotorController extends Controller
{
    // Menampilkan semua motor
    public function index()
    {
        return response()->json(Motor::latest()->get(), 200);
    }

    // Menampilkan detail satu motor
    public function show(Motor $motor)
    {
        return response()->json($motor, 200);
    }

    // Menambahkan motor baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:100',
            'plate_number' => 'required|string|max:20|unique:motors,plate_number',
            'price_per_day' => 'required|integer|min:1',
            'status' => 'sometimes|in:available,rented',
        ]);

        $motor = Motor::create($validated);

        return response()->json([
            'message' => 'Motor berhasil ditambahkan',
            'data' => $motor,
        ], 201);
    }

    // Memperbarui data motor
    public function update(Request $request, Motor $motor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'brand' => 'sometimes|required|string|max:100',
            'plate_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('motors', 'plate_number')->ignore($motor->id),
            ],
            'price_per_day' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:available,rented',
        ]);

        $motor->update($validated);

        return response()->json([
            'message' => 'Motor berhasil diperbarui',
            'data' => $motor,
        ], 200);
    }

    // Menghapus motor
    public function destroy(Motor $motor)
    {
        $motor->delete();

        return response()->json([
            'message' => 'Motor berhasil dihapus',
        ], 200);
    }
}
