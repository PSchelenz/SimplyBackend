<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizationRequest;
use Illuminate\Support\Str;

class AuthorizationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AuthorizationRequest $request)
    {
        $validated = $request->validated();

        $accounts = json_decode(file_get_contents(storage_path('app/accounts.json')), true);

        foreach ($accounts as $account) {
            if($account['secret'] === $validated['secret']) {
                return response()->json([
                    'status' => 'success',
                    'token' => Str::random(24),
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials',
        ], 401);
    }
}
