<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class FakeExternalServiceController extends Controller
{
    public function send(): JsonResponse
    {
        if (random_int(0, 1) === 1) {
            return response()->json([
                'message' => 'Ticket sent successfully.',
            ], 200);
        }

        return response()->json([
            'message' => 'Internal Server Error.',
        ], 500);
    }
}
