<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\PotonganModel;
use App\Models\Sp2dModel;
use Illuminate\Http\Request;

class Sp2dApiController extends Controller
{
    public function index()
    {
        return response()->json(Sp2dModel::all(), 200);
    }

    public function indexbelanja()
    {
        return response()->json(BelanjalsguModel::all(), 200);
    }

    public function indexpotongan_pajak()
    {
        return response()->json(PotonganModel::all(), 200);
    }

    public function store(Request $request)
    {
        try {
            $urusan = Sp2dModel::create($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil disimpan!',
                'data' => $urusan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Sp2dModel $sp2d)
    {
        try {
            $sp2d->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diperbarui!',
                'data' => $sp2d
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Sp2dModel $sp2d)
    {
        return response()->json($sp2d, 200);
    }

    public function destroy(Sp2dModel $sp2d)
    {
        $sp2d->delete();
        return response()->json(null, 204);
    }

}
