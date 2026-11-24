<?php

namespace Modules\Acl\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Acl\Services\PasswordTransportService;

/**
 * Issues the single-use nonce + RSA public key the browser uses to encrypt password fields.
 */
class PasswordChallengeController extends Controller
{
    public function __invoke(Request $request, PasswordTransportService $transport): JsonResponse
    {
        return response()
            ->json($transport->issueChallenge($request->session()))
            ->header('Cache-Control', 'no-store, private');
    }
}
