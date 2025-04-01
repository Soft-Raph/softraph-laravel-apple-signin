<?php

namespace Softraph\LaravelAppleSignin\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Request;
use Softraph\LaravelAppleSignin\Services\AppleSignInService;

class AppleSignInController
{
    public function redirectToApple(Request $request)
    {
        $redirectUrl = $request->query('r', '/');
        $state = encrypt(json_encode(['redirect' => $redirectUrl]));

        $url = "https://appleid.apple.com/auth/authorize?" . http_build_query([
                'response_type' => 'code',
                'client_id' => config('applesignin.client_id'),
                'redirect_uri' => config('applesignin.redirect'),
                'scope' => 'name email',
                'state' => $state,
                'response_mode' => 'form_post'
            ]);

        return redirect()->away($url);
    }

    public function handleAppleCallback(Request $request, AppleSignInService $appleService)
    {
        return $appleService->handleCallback($request);
    }
}
