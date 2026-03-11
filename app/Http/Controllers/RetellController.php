<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RetellController extends Controller
{
    /**
     * Initiate a Retell AI call to read the user's login credentials.
     * NOTE: This is experimental. Never use in production.
     */
    public function call(Request $request)
    {
        $user = $request->user();

        if (!$user->phone) {
            return response()->json([
                'error' => 'No phone number registered. Please update your profile.'
            ], 422);
        }

        $apiKey     = config('services.retell.api_key');
        $agentId    = config('services.retell.agent_id');
        $fromNumber = config('services.retell.from_number');

        if (!$apiKey || !$agentId || !$fromNumber) {
            return response()->json([
                'error' => 'Retell is not configured. Set RETELL_API_KEY, RETELL_AGENT_ID, RETELL_FROM_NUMBER in .env'
            ], 500);
        }

        $response = Http::withToken($apiKey)
            ->post('https://api.retellai.com/v2/create-phone-call', [
                'from_number' => $fromNumber,
                'to_number'   => $user->phone,
                'agent_id'    => $agentId,
                'retell_llm_dynamic_variables' => [
                    'user_name'     => $user->name,
                    'user_email'    => $user->email,
                    'temp_password' => 'Please log in using your registered password.',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Retell call failed', ['response' => $response->body()]);
            return response()->json(['error' => 'Call could not be initiated. Check logs.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Call initiated! Your phone will ring shortly.']);
    }
}
