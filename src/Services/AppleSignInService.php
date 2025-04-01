<?php

namespace Softraph\LaravelAppleSignin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class AppleSignInService
{
    public function handleCallback(Request $request)
    {
        try {
            $response = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                'client_id' => config('applesignin.client_id'),
                'client_secret' => $this->generateAppleClientSecret(),
                'code' => $request->code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('applesignin.redirect')
            ]);
            $socialAccountModel = config('apple-signin.social_account_model',  \App\Models\SocialAccount::class);

            $userModel = config('apple-signin.user_model', \App\Models\User::class);

            $user_columns = config('apple-signin.user_columns', []);

            $socialColumns = config('apple-signin.social_columns', []);


            $tokenData = $response->json();

            if (!isset($tokenData['id_token'])) {
                return Redirect::to('login')->with('error', __('Invalid Apple authentication'));
            }

            $appleKeys = json_decode(file_get_contents("https://appleid.apple.com/auth/keys"), true);
            $decodedKey = JWK::parseKeySet($appleKeys);
            $decodedToken = JWT::decode($tokenData['id_token'], $decodedKey);

            $state = json_decode(decrypt($request->state), true);
            $redirectUrl = $state['redirect'] ?? '/';

            $account = $socialAccountModel::where($socialColumns['provider'], 'AppleProvider')
                ->where($socialColumns['provider_user_id'], $decodedToken->sub)
                ->first();

            $email = $decodedToken->email ?? null;

            if (!$account) {
                $userName = json_decode($request->input('user'), true) ?? [];
                $fullName = $userName['name'] ?? '';
                $username = generate_username($fullName ?: 'user');

                if (!$email) {
                    return Redirect::to('login')->with('error', __('Auth error occurred, try again'));
                }
                $user = $userModel::where($user_columns['email'], $email)->first();
                if ($user){
                  //
                } else {
                    $userData = [
                        $user_columns['email'] => $email,
                    ];

                    // Check if optional columns exist before adding them
                    if (isset($columns['fullname']) && Schema::hasColumn((new $userModel)->getTable(), $user_columns['fullname'])) {
                        $userData[$user_columns['fullname']] = $fullName;
                    }

                    if (isset($user_columns['username']) && Schema::hasColumn((new $userModel)->getTable(), $user_columns['username'])) {
                        $userData[$user_columns['username']] = $username;
                    }
                    $user = $userModel::create($userData);

                }


                $account = new $socialAccountModel([
                    $socialColumns['provider_user_id'] => $decodedToken->sub,
                    $socialColumns['provider'] => 'AppleProvider',
                    $socialColumns['user_id'] => $user->id,
                ]);

                $account->save();
            } else {
                $user = $account->user;
            }

            auth()->login($user);
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            return Redirect::to('login')->with('error', __('Auth error occurred, try again'));
        }
    }

    private function generateAppleClientSecret()
    {
        $payload = [
            'iss' => config('applesignin.team_id'),
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => config('applesignin.client_id'),
        ];

        return JWT::encode($payload, base64_decode(config('applesignin.private_key')), 'ES256', config('applesignin.key_id'));
    }
}
