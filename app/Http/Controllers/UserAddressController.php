<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return response()->json([
            'message' => 'success get addresses',
            'data'    => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'province'      => 'required|string|max:100',
            'city'          => 'required|string|max:100',
            'district'      => 'required|string|max:100',
            'postal_code'   => 'required|string|max:10',
            'address'       => 'required|string',
            'label'         => 'required|in:Rumah,Kantor',
            'is_default'    => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($request->all());

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan',
            'data'    => $address,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        return response()->json([
            'message' => 'success get address',
            'data'    => $address,
        ]);
    }

    public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        $request->validate([
            'receiver_name' => 'sometimes|string|max:100',
            'phone'         => 'sometimes|string|max:20',
            'province'      => 'sometimes|string|max:100',
            'city'          => 'sometimes|string|max:100',
            'district'      => 'sometimes|string|max:100',
            'postal_code'   => 'sometimes|string|max:10',
            'address'       => 'sometimes|string',
            'label'         => 'sometimes|in:Rumah,Kantor',
            'is_default'    => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'message' => 'Alamat berhasil diupdate',
            'data'    => $address,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'Alamat berhasil dihapus',
        ]);
    }
}
