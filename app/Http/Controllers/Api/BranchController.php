<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\chi_nhanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\updateStoreBranch;
class BranchController extends Controller
{
    public function index()
    {
        $branches = chi_nhanh::orderBy('id_chinhanh', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'total'  => $branches->count(),
            'data'   => $branches,
        ], 200);
    }
    public function show($id)
    {
        $branch = chi_nhanh::find($id);
        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chi nhánh'
            ], 404);
        }
        return response()->json([
            'status' => 'success', 
            'data' => $branch
        ], 200);
    }
    public function store(StoreBranchRequest $request)
    {
        $branch = chi_nhanh::create($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Thêm chi nhánh thành công!',
            'data'    => $branch
        ], 201);
    }
    public function update(updateStoreBranch $request, $id)
    {
        $branch = chi_nhanh::find($id);
        if (!$branch) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy chi nhánh'], 404);
        }
        $branch->update($request->validated());
        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật chi nhánh thành công!',
            'data'    => $branch
        ], 200);
    }
    public function destroy(Request $request, $id)
    {
        $branch = chi_nhanh::find($id);
        if (!$branch) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không tìm thấy chi nhánh'
            ], 404);
        }
        $branch->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa chi nhánh thành công!'
        ], 200);
    }
}
