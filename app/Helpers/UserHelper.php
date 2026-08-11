<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

class UserHelper
{
    private const PHONE_VERIFICATION_DAILY_LIMIT = 3;
    private const PHONE_VERIFICATION_COOLDOWN_MINUTES = 1;
    private const PHONE_VERIFICATION_LIMIT_MESSAGE = 'Закончились попытки, попробуйте снова завтра или обратитесь в поддержку';

    private function verificationRequestError(array &$verifyData): ?array
    {
        $today = Carbon::now()->toDateString();
        if (($verifyData['attempts_date'] ?? null) !== $today) {
            $verifyData['attempts_date'] = $today;
            $verifyData['attempts'] = 0;
            unset($verifyData['requested']);
        }

        if ((int) ($verifyData['attempts'] ?? 0) >= self::PHONE_VERIFICATION_DAILY_LIMIT) {
            return [
                'success' => false,
                'verified' => 0,
                'limit_reached' => true,
                'message' => self::PHONE_VERIFICATION_LIMIT_MESSAGE,
            ];
        }

        if (!empty($verifyData['requested'])) {
            $availableAt = Carbon::parse($verifyData['requested']);
            if ($availableAt->isFuture()) {
                return [
                    'success' => false,
                    'verified' => 0,
                    'retry_after' => max(1, Carbon::now()->diffInSeconds($availableAt)),
                    'message' => 'Повторный запрос подтверждения телефона можно отправить через ' . $availableAt->diff(Carbon::now())->format('%i мин %s сек') . '!',
                ];
            }
        }

        return null;
    }

    private function successfulVerificationData(array $verifyData, array $data): array
    {
        return array_merge($verifyData, $data, [
            'attempts_date' => Carbon::now()->toDateString(),
            'attempts' => (int) ($verifyData['attempts'] ?? 0) + 1,
            'requested' => Carbon::now()->addMinutes(self::PHONE_VERIFICATION_COOLDOWN_MINUTES),
        ]);
    }

    public function sendCodeVerifyPhone($activation, $api = false)
    {
        $phone = $activation['phone'];
        $user_message = '';
        $resend = false;
        $phoneField = $activation['is_verified'];
        if (!$phoneField) {
            try {
                $string = rand(1000, 9999);
                $message = __($string . '');
                $to = (string)PhoneNumber::make($phone)->ofCountry('RU');
                $to = str_replace(array('+7'), '8', $to);
                $unique = Str::uuid()->toString();

                if (isset($activation->verify_phone_data['unique'])) {
//                    $unique = $activation->verify_phone_data['unique'];
//                    $message = $activation->token;
//                    $string = $activation->token;
                }

                $ucaller_id = null;
                $request_time = null;
                $verifyData = $activation['verify_phone_data'];
                if (!is_array($verifyData)) {
                    $verifyData = json_decode($verifyData, true);
                }
                $verifyData = $verifyData ?: [];
                if ($requestError = $this->verificationRequestError($verifyData)) {
                    return $api ? $requestError : response()->json($requestError);
                }
                if (!empty($verifyData)) {
                    $verification_data = $verifyData;
                    if (isset($verification_data['unique'])) {
                        $ucaller_id = $verification_data['ucaller_id'];
                    }

                }

                $endpoint = 'https://api.ucaller.ru/v1.0/initCall?phone=' . $to . '&code=' . $message . '&client=' . $activation['user_id'] . '&unique=' . $unique . '&voice=false&key=' . env('UCALLER_SECRET') . '&service_id=' . env('UCALLER_ID');

                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $endpoint, []);
                $content = $response->getBody()->getContents();
                if (!is_array($content)) {
                    $content = json_decode($content, true);
                }

                if ($content['status']) {
                    $request_time = Carbon::now()->addMinute();
                    $user_message .= 'На номер ' . $content['phone'] . ' совершен звонок в ' . Carbon::now()->format('d.m.y H:i:s') . '. Пожалуйста, введите последние 4 цифры номера телефона входящего звонка в соответствующее поле';

                    try {
                        $endpoint = 'https://api.ucaller.ru/v1.0/getBalance?key=' . env('UCALLER_SECRET') . '&service_id=' . env('UCALLER_ID');
                        $client = new \GuzzleHttp\Client();
                        $response = $client->request('GET', $endpoint, []);
                        $content = $response->getBody()->getContents();
                        if (!is_array($content)) {
                            $content = json_decode($content, true);
                        }
                        $balance = $content['rub_balance'];
                        if($balance < 200) {
                            $telegram = new AppHelper();
                            $telegram->telegram_group_message('⚠️ Внимание! UCALLER Баланс < 200 руб. ('.$balance.')');
                        }
                    } catch (\Throwable $e) {

                    }
                } else {
                    if ($content['code'] == 19) {
                        $error_message = 'Так часто запрашивать нельзя. Подождите одну минуту';
                    } elseif ($content['code'] == 3) {
                        $error_message = 'Некорректный номер телефона. Если это не так - свяжитесь с администрацией.';
                    } elseif ($content['code'] == 1) {
                        $error_message = 'Некорректный номер телефона. Если это не так - свяжитесь с администрацией.';
                    } elseif ($content['code'] == 1002) {
                        $error_message = 'Произошла ошибка. Свяжитесь с администрацией.';
                        $telegram = new AppHelper();
                        $telegram->telegram_group_message('⚠️ Внимание! UCALLER Кончился баланс');
                    } else {
                        $error_message = $content['error'] . '. Свяжитесь с администрацией.' ?? 'Произошла ошибка.';
                    }
                    $data = [
                        'success' => false,
                        'verified' => 0,
                        'action' => 'phone',
                        'phone' => $phone,
                        'message' => $error_message
                    ];
                    if ($api) {
                        return $data;
                    }
                    return response()->json($data);
                }
                unset($verifyData['type']);
                $activation->verify_phone_data = $this->successfulVerificationData($verifyData, [
                    'phone' => $phone,
                    'ucaller_id' => $content['ucaller_id'] ?? null,
                    'unique' => $content['unique_request_id'] ?? null,
                ]);
                $activation->token = $string;
                $activation->save();
                $data = [
                    'success' => true,
                    'action' => 'phone',
                    'time' => $request_time->timezone('Europe/Moscow')->format('Y/m/d H:i:s'),
                    'attempts_left' => max(0, self::PHONE_VERIFICATION_DAILY_LIMIT - (int) $activation->verify_phone_data['attempts']),
                    'message' => $user_message,
                    'phone' => $phone
                ];
                if ($api) {
                    return $data;
                }
                return response()->json($data);
            } catch (\Exception $e) {
                Log::error(json_encode($e));
                $data = [
                    'success' => false,
                    'action' => 'phone',
                    'phone' => $phone,
                    'message' => 'Произошла ошибка. Свяжитесь с администрацией.'
                ];
                if ($api) {
                    return $data;
                }
                return response()->json($data);
            }
        } else {
            $data = [
                'success' => true,
                'verified' => 1,
                'phone' => $phone,
                'message' => 'Ваш телефон подтвержден'
            ];
            if ($api) {
                return $data;
            }
            return response()->json($data);
        }
    }

    public function sendSMSCodeVerifyPhone($activation, $api = false, $logType = 'sms')
    {
        $phone = $activation['phone'];
        $user_message = '';
        $resend = false;
        $phoneField = $activation['is_verified'];
        if (!$phoneField) {
            try {
                $string = rand(1000, 9999);
                $message = __($string . '');
                $to = (string)PhoneNumber::make($phone)->ofCountry('RU');
                $to = str_replace(array('+7'), '8', $to);
                $unique = Str::uuid()->toString();

                $verifyData = $activation['verify_phone_data'];
                if (!is_array($verifyData)) {
                    $verifyData = json_decode($verifyData, true);
                }
                $verifyData = $verifyData ?: [];
                if ($requestError = $this->verificationRequestError($verifyData)) {
                    return $api ? $requestError : response()->json($requestError);
                }
                if (!empty($verifyData)) {
                    $verification_data = $verifyData;
                }
                $smsc = new SMSCHelper();
                $verify = $smsc->send_sms($to, "Vash kod DEELS: " . $message, 1);

                if ($verify[1] > 0) {
                    $request_time = Carbon::now()->addMinute();
                    $user_message .= 'На номер ' . $to . ' отправлено SMS-сообщение в ' . Carbon::now()->format('d.m.y H:i:s') . '. Пожалуйста, введите код соответствующее поле';
                } else {
                    $error_code = intval(abs($verify[1]));
                    if ($error_code == 9) {
                        $error_message = 'Так часто запрашивать нельзя. Подождите одну минуту';
                    } elseif ($error_code == 8) {
                        $error_message = 'Сообщение на указанный номер не может быть доставлено';
                    } elseif ($error_code == 3) {
                        $error_message = 'Не удается отправить запрос. Повторите запрос позже';
                        $telegram = new AppHelper();
                        $telegram->telegram_group_message('⚠️ SMSC! НЕДОСТАТОЧНО СРЕДСТВ НА СЧЕТЕ КЛИЕНТА!');
                    } else {
                        $error_message = 'Не удается отправить запрос. Повторите запрос позже';
                    }
                    $data = [
                        'success' => false,
                        'verified' => 0,
                        'action' => 'sms',
                        'phone' => $phone,
                        'message' => $error_message
                    ];
                    if ($api) {
                        return $data;
                    }
                    return response()->json($data);
                }
                $activation->verify_phone_data = $this->successfulVerificationData($verifyData, [
                    'phone' => $phone,
                    'type' => 'sms',
                ]);
                $activation->token = $string;
                $activation->save();
                $helper = new AppHelper();
                $ip = request()->ip();
                $helper->write_log($ip, $logType, 'Запрос sms-кода активации '.$phone);
                $data = [
                    'success' => true,
                    'action' => 'sms',
                    'time' => $request_time->timezone('Europe/Moscow')->format('Y/m/d H:i:s'),
                    'attempts_left' => max(0, self::PHONE_VERIFICATION_DAILY_LIMIT - (int) $activation->verify_phone_data['attempts']),
                    'message' => $user_message,
                    'phone' => $phone
                ];
                if ($api) {
                    return $data;
                }
                return response()->json($data);
            } catch (\Exception $e) {
                Log::error(json_encode($e));
                $data = [
                    'success' => false,
                    'action' => 'sms',
                    'phone' => $phone,
                    'message' => 'Произошла ошибка. Свяжитесь с администрацией.'
                ];
                if ($api) {
                    return $data;
                }
                return response()->json($data);
            }
        } else {
            $data = [
                'success' => true,
                'verified' => 1,
                'phone' => $phone,
                'message' => 'Ваш телефон подтвержден'
            ];
            if ($api) {
                return $data;
            }
            return response()->json($data);
        }
    }


}
