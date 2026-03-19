<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();
        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label'     => ['required', 'in:home,work,other'],
            'full_name' => ['required', 'string', 'max:255'],
            'line1'     => ['required', 'string', 'max:255'],
            'line2'     => ['nullable', 'string', 'max:255'],
            'city'      => ['required', 'string', 'max:100'],
            'postcode'  => ['required', 'string', 'max:20'],
            'country'   => ['required', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_default' => ['boolean'],
        ]);

        if (!empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($data);

        return response()->json(['address' => $address], 201);
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        $this->authorizeAddress($request, $address);

        $data = $request->validate([
            'label'     => ['sometimes', 'in:home,work,other'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'line1'     => ['sometimes', 'string', 'max:255'],
            'line2'     => ['nullable', 'string', 'max:255'],
            'city'      => ['sometimes', 'string', 'max:100'],
            'postcode'  => ['sometimes', 'string', 'max:20'],
            'country'   => ['sometimes', 'string', 'max:100'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'is_default' => ['boolean'],
        ]);

        if (!empty($data['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json(['address' => $address->fresh()]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeAddress($request, $address);
        $address->delete();
        return response()->json(['message' => 'Address deleted.']);
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 403);
    }
}
