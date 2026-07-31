<?php

namespace App\Http\Controllers;

use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    /**
     * Safaricom Daraja STK Push callback.
     * URL: POST /mpesa/callback
     * This endpoint is CSRF-exempt (see bootstrap/app.php or Http/Middleware/VerifyCsrfToken.php).
     */
    public function callback(Request $request)
    {
        Log::info('MPesa callback received', ['body' => $request->all()]);

        try {
            app(MpesaService::class)->handleCallback($request->all());
        } catch (\Throwable $e) {
            Log::error('MPesa callback processing error', [
                'error'   => $e->getMessage(),
                'payload' => $request->all(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Processing failed — will retry'], 500);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
