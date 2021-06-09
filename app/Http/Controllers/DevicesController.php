<?php

namespace App\Http\Controllers;

use App\Entities\User;
use Illuminate\Http\Request;

class DevicesController extends Controller
{
    public function post(Request $request)
    {
        auth()->user()->update([
            'device_token' => $request->get('device_token'),
        ]);

        return response()->json([
            'response' => 'success',
            'message' => 'Token de dispositivo guardado.',
            'status' => 200,
        ], 200);
    }
}
