<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\PotonganModel;
use App\Models\Sp2dModel;
use App\Models\TbBelanjaLs;
use App\Models\TbPajakPotonganLs;
use App\Models\TbSp2d;
use Illuminate\Http\Request;

class Sp2dApi1Controller extends Controller
{
    public function index()
    {
        return response()->json(TbSp2d::all(), 200);
    }

    public function indexbelanja()
    {
        return response()->json(TbBelanjaLs::all(), 200);
    }

    public function indexpotongan_pajak()
    {
        return response()->json(TbPajakPotonganLs::all(), 200);
    }

    public function store(Request $request)
    {
        try {
            $urusan = TbSp2d::create($request->all());
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

    public function update(Request $request, TbSp2d $sp2d)
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

    public function show(TbSp2d $sp2d)
    {
        return response()->json($sp2d, 200);
    }

    public function destroy(TbSp2d $sp2d)
    {
        $sp2d->delete();
        return response()->json(null, 204);
    }

}
