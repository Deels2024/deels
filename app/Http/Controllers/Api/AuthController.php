<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VkAuthService;
use Carbon\Carbon;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function vk_auth_link(Request $request, VkAuthService $vkAuthService)
    {
        $request->validate([
            'redirect_uri' => 'nullable|string',
            'scope' => 'nullable|string',
        ]);

        return response()->json($vkAuthService->makeAuthLink(
            $request->input('redirect_uri'),
            $request->input('scope')
        ));
    }

    public function vk_authenticate(Request $request, VkAuthService $vkAuthService)
    {
        $request->validate([
            'vk_token' => 'required_without:code|string',
            'code' => 'required_without:vk_token|string',
            'device_id' => 'required_with:code|string',
            'code_verifier' => 'required_with:code|string',
            'redirect_uri' => 'nullable|string',
        ]);

        try {
            $auth = $vkAuthService->authenticate($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $request->filled('vk_token')
                    ? 'Unable to retrieve user details from VK.'
                    : 'Unable to authorize with VK ID.',
                'details' => $e->getMessage(),
            ], 401);
        }

        $user = $auth['user'];

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        if ($banPayload = $vkAuthService->banPayload($user)) {
            return response()->json($banPayload, 403);
        }

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'found_users' => $auth['found_users'],
            'show_friends_popup' => $auth['show_friends_popup'],
            'email_required' => empty($user->email),
            'email_verification_required' => $user->emailVerificationPending(),
            'show_email_prompt' => $user->shouldShowEmailPrompt(),
            'next_email_prompt_at' => optional($user->emailPromptDueAt())->toIso8601String(),
            'phone_required' => empty($user->phone),
            'phone_verification_required' => $user->phoneVerificationPending(),
            'show_phone_prompt' => $user->shouldShowPhonePrompt(),
            'next_phone_prompt_at' => optional($user->phonePromptDueAt())->toIso8601String(),
        ]);
    }

    public function apple_authenticate(Request $request)
    {


        // Validate that an Apple token is provided.
        $request->validate([
            'apple_token' => 'required|string',
        ]);

        $appleToken = $request->input('apple_token');

        // 1. Retrieve Apple's public keys.
        $client = new Client();
        try {
            // Consider caching this result in production.
            $response = $client->get('https://appleid.apple.com/auth/keys');
            $bodyContents = $response->getBody()->getContents();
            $jwks = json_decode($bodyContents, true);

            if (!isset($jwks['keys'])) {
                return response()->json(['error' => 'Unable to retrieve Apple public keys.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching Apple public keys: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch Apple public keys.'], 500);
        }

        // 2. Decode and verify the Apple JWT using the full JWK set.
        try {
            // In firebase/php-jwt v6, do not pass a third parameter.
            $decoded = JWT::decode($appleToken, JWK::parseKeySet($jwks));
            $decoded = (array)$decoded; // Convert the decoded object into an associative array.
        } catch (\Exception $e) {
            Log::info('Apple token decoding failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid Apple token.'], 401);
        }

        // 3. Retrieve the user identifier and other details from the token payload.
        // The unique user identifier is in the "sub" claim.
        $appleUserId = $decoded['sub'] ?? null;
        if (!$appleUserId) {
            return response()->json(['error' => 'Invalid Apple token payload.'], 401);
        }

        // Apple may include the user's email in the token.
        $email = $decoded['email'] ?? ('apple_' . $appleUserId . '@deels.ru');

        // In many cases, Apple tokens do not include a user's name.
        // You might later prompt the user to enter their name.
        $name = $decoded['name'] ?? 'Apple User';

        // Check if a user with the same email already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // If a user with the same email exists, update the apple_id
            $existingUser->apple_id = $appleUserId;
            $existingUser->save();
            $user = $existingUser;
        } else {

            return response()->json([
                'success' => false,
                'error' => 'Метод авторизации больше недоступен в проекте'
            ]);

            // If no user with the same email exists, create a new user
            $user = User::firstOrCreate(
                ['apple_id' => $appleUserId],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => null,
                    'referral_code' => Str::uuid()->toString(),
                ]
            );
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        $banned_till = Carbon::parse($user->banned_till);
        if ($user->banned_till && Carbon::now()->lt($banned_till)) {
            return response()->json([
                'error' => 'Ваш аккаунт заблокирован до ' . $banned_till->format('Y-m-d H:i:s'),
                'banned' => true,
                'banned_till' => $banned_till->toDateTimeString()
            ], 403);
        }

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function yandex_authenticate(Request $request)
    {

        // Validate that an Apple token is provided.
        $request->validate([
            'yandex_token' => 'required|string',
        ]);

        $yandexToken = $request->input('yandex_token');

        // 1. Retrieve Apple's public keys.
        $client = new Client();
        try {
            // Consider caching this result in production.
            $response = $client->get('https://login.yandex.ru/info?oauth_token=' . $yandexToken);
            $bodyContents = $response->getBody()->getContents();
            $jwks = json_decode($bodyContents, true);

            if (!isset($jwks['id'])) {
                return response()->json(['error' => 'Unable to retrieve Yandex account.'], 500);
            }

            $yandexUserId = $jwks['id'];
        } catch (\Exception $e) {
            Log::error('Error fetching Yandex account: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch AYandex account.'], 500);
        }

        // Apple may include the user's email in the token.
        if (isset($jwks['login'])) {
            $email = $jwks['login'] . '@yandex.ru';
        } else {
            $email = 'yandex_' . $yandexUserId . '@deels.ru';
        }


        // In many cases, Apple tokens do not include a user's name.
        // You might later prompt the user to enter their name.
        $name = $jwks['real_name'] ?? $jwks['display_name'] ?? $jwks['login'] ?? 'Yandex User';

        // Check if a user with the same email already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // If a user with the same email exists, update the apple_id
            $existingUser->yandex_id = $yandexUserId;
            $existingUser->save();
            $user = $existingUser;
        } else {
            // If no user with the same email exists, create a new user
            $user = User::firstOrCreate(
                ['yandex_id' => $yandexUserId],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => null,
                    'referral_code' => Str::uuid()->toString(),
                    'is_activated' => true,
                ]
            );
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        $banned_till = Carbon::parse($user->banned_till);
        if ($user->banned_till && Carbon::now()->lt($banned_till)) {
            return response()->json([
                'error' => 'Ваш аккаунт заблокирован до ' . $banned_till->format('Y-m-d H:i:s'),
                'banned' => true,
                'banned_till' => $banned_till->toDateTimeString()
            ], 403);
        }

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }


    public function auth_mailru(Request $request)
    {

        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $data = [
                'client_id' => env('MAILRU_CLIENT_ID'),
                'client_secret' => env('MAILRU_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => env('MAILRU_MOBILE_REDIRECT_URI'),
            ];

            $options = [
                'http' => [
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "User-Agent: PHP Mail.ru OAuth Client\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $response = file_get_contents('https://oauth.mail.ru/token', false, $context);

            $tokenResponse = json_decode($response, true);

            if (isset($tokenResponse['access_token'])) {
                $accessToken = $tokenResponse['access_token'];
                $url = 'https://oauth.mail.ru/userinfo' . '?access_token=' . urlencode($accessToken);
                $userInfo = file_get_contents($url);

                $user_data = json_decode($userInfo, true);


                // Apple may include the user's email in the token.
                if (isset($user_data['email'])) {
                    $email = $user_data['email'];
                } else {
                    $email = $user_data['nickname'] ?? ('mailru_' . $user_data['id'] . '@deels.ru');
                }

                $mailru_name = [];
                if (isset($user_data['first_name'])) {
                    $mailru_name[] = $user_data['first_name'];
                }
                if (isset($user_data['last_name'])) {
                    $mailru_name[] = $user_data['last_name'];
                }

                if (!empty($mailru_name)) {
                    $name = implode(' ', $mailru_name);
                } else {
                    $name = $user_data['nickname'] ?? 'Mail.ru User';
                }


                // Check if a user with the same email already exists
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {
                    // If a user with the same email exists, update the apple_id
                    $existingUser->mailru_id = $user_data['id'];
                    $existingUser->save();
                    $user = $existingUser;
                } else {
                    // If no user with the same email exists, create a new user
                    $user = User::firstOrCreate(
                        ['mailru_id' => $user_data['id']],
                        [
                            'name' => $name,
                            'email' => $email,
                            'username' => $user_data['nickname'] ?? null,
                            'password' => bcrypt(Str::random(16)),
                            'avatar' => $user_data['image'] ?? null,
                            'referral_code' => Str::uuid()->toString(),
                            'is_activated' => true,
                        ]
                    );
                }

                $user->tokens()->delete();
                $token = $user->createToken('mobile')->plainTextToken;

                $banned_till = Carbon::parse($user->banned_till);
                if ($user->banned_till && Carbon::now()->lt($banned_till)) {
                    return response()->json([
                        'error' => 'Ваш аккаунт заблокирован до ' . $banned_till->format('Y-m-d H:i:s'),
                        'banned' => true,
                        'banned_till' => $banned_till->toDateTimeString()
                    ], 403);
                }

                return response()->json([
                    'success' => true,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]);
            } else {
                return response()->json(['error' => 'Failed to get access token.'], 500);
            }
        }


    }

    public function auth_ok(Request $request)
    {

        if (isset($_GET['code'])) {

            $code = $_GET['code'];
            $data = [
                'client_id' => env('ODNOKLASSNIKI_CLIENT_ID'),
                'client_secret' => env('ODNOKLASSNIKI_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => env('ODNOKLASSNIKI_MOBILE_REDIRECT_URI'),
            ];

            $options = [
                'http' => [
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "User-Agent: PHP Mail.ru OAuth Client\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];

            $context = stream_context_create($options);
            $response = file_get_contents('https://api.ok.ru/oauth/token.do', false, $context);

            $tokenResponse = json_decode($response, true);


            if (isset($tokenResponse['access_token'])) {
                $accessToken = $tokenResponse['access_token'];

                $api_url = 'https://api.odnoklassniki.ru/fb.do';

                $params = [
                    'application_key' => env('ODNOKLASSNIKI_CLIENT_PUBLIC'),  // Your app key
                    'access_token' => $accessToken,
                    'method' => 'users.getCurrentUser'    // The method to get the current user's info
                ];

                $query_string = http_build_query($params);

                $response = file_get_contents($api_url . '?' . $query_string);


                $user_data = json_decode($response, true);

                // Apple may include the user's email in the token.
                if (isset($user_data['email'])) {
                    $email = $user_data['email'];
                } else {
                    $email = $user_data['nickname'] ?? ('mailru_' . $user_data['id'] . '@deels.ru');
                }

                $ok_name = [];
                if (isset($user_data['first_name'])) {
                    $ok_name[] = $user_data['first_name'];
                }
                if (isset($user_data['last_name'])) {
                    $ok_name[] = $user_data['last_name'];
                }

                if (!empty($ok_name)) {
                    $name = implode(' ', $ok_name);
                } else {
                    $name = $user_data['nickname'] ?? 'OK.ru User';
                }


                // Check if a user with the same email already exists
                $existingUser = User::where('email', $email)->first();

                if ($existingUser) {
                    // If a user with the same email exists, update the apple_id
                    $existingUser->ok_id = $user_data['uid'];
                    $existingUser->save();
                    $user = $existingUser;
                } else {
                    // If no user with the same email exists, create a new user
                    $user = User::firstOrCreate(
                        ['ok_id' => $user_data['uid']],
                        [
                            'name' => $user_data['name'] ?? $name,
                            'email' => $email,
                            'username' => $user_data['nickname'] ?? null,
                            'password' => bcrypt(Str::random(16)),
                            'avatar' => $user_data['pic_1'] ?? $user_data['pic_2'] ?? $user_data['pic_3'] ?? null,
                            'referral_code' => Str::uuid()->toString(),
                            'is_activated' => true,
                        ]
                    );
                }

                $user->tokens()->delete();
                $token = $user->createToken('mobile')->plainTextToken;

                $banned_till = Carbon::parse($user->banned_till);
                if ($user->banned_till && Carbon::now()->lt($banned_till)) {
                    return response()->json([
                        'error' => 'Ваш аккаунт заблокирован до ' . $banned_till->format('Y-m-d H:i:s'),
                        'banned' => true,
                        'banned_till' => $banned_till->toDateTimeString()
                    ], 403);
                }

                return response()->json([
                    'success' => true,
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]);
            } else {
                return response()->json(['error' => 'Failed to get access token.'], 500);
            }

        }

    }


}
