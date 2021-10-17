<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;


class RecaptchaProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('recaptcha', function($attribute, $value) {
            $bypass_key = env('BYPASS_RECAPTCHA_KEY', '');
            if (!empty($bypass_key) && $value == $bypass_key) {
                return true;
            }
            return self::verify($value);
        });
        Validator::replacer('recaptcha', function($message, $attribute, $rule, $parameters) {
            return 'recaptcha 驗證失敗';
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * verify token
     * @param string $value
     * @return boolean
     */
    private static function verify($value)
    {
        $client = new \GuzzleHttp\Client();
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = $client->request('POST', $verifyUrl, [
            'form_params' => [
                'secret' => env('RECAPTCHA_SECRET_KEY', ''),
                'response' => $value,
            ]
        ]);

        $convertedResponse = json_decode($response->getBody()->getContents());

        // 取得 response 的 success 值
        return $convertedResponse->success;
    }
}
