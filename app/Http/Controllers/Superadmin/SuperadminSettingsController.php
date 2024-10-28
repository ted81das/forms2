<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\System;
use DateTimeZone;
use Illuminate\Http\Request;

class SuperadminSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $languages = config('constants.langs');
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        $settings = [
            'APP_NAME' => config('app.name'),
            'APP_TITLE' => config('app.title'),
            'CURRENCY_NAME' => env('CURRENCY_NAME'),
            'CURRENCY_SYMBOL' => env('CURRENCY_SYMBOL'),
            'CURRENCY_CODE' => env('CURRENCY_CODE'),
            'APP_TIMEZONE' => env('APP_TIMEZONE'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'ENABLE_REGISTRATION' => env('ENABLE_REGISTRATION'),
            'ENABLE_SAAS_MODULE' => env('ENABLE_SAAS_MODULE'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
            'APP_LOCALE' => $languages,
            'timezones' => $timezones,
            'PAYPAL_MODE' => config('paypal.mode'),
            'PAYPAL_SANDBOX_API_USERNAME' => config('paypal.sandbox.username'),
            'PAYPAL_SANDBOX_API_PASSWORD' => config('paypal.sandbox.password'),
            'PAYPAL_SANDBOX_API_SECRET' => config('paypal.sandbox.secret'),
            'PAYPAL_LIVE_API_USERNAME' => config('paypal.live.username'),
            'PAYPAL_LIVE_API_PASSWORD' => config('paypal.live.password'),
            'PAYPAL_LIVE_API_SECRET' => config('paypal.live.secret'),
            'STRIPE_PUB_KEY' => config('constants.STRIPE_PUB_KEY'),
            'STRIPE_SECRET_KEY' => config('constants.STRIPE_SECRET_KEY'),
            'ENABLE_OFFLINE_PAYMENT' => config('constants.ENABLE_OFFLINE_PAYMENT'),
            'ACELLE_MAIL_NAME' => config('constants.ACELLE_MAIL_NAME'),
            'ACELLE_MAIL_API' => config('constants.ACELLE_MAIL_API'),
        ];

        if ($this->isDemo()) {
            $settings['MAIL_USERNAME'] = '';
            $settings['MAIL_PASSWORD'] = '';
        }

        $date_formats = System::dateFormats();

        $additional_js = System::getValue('additional_js');
        $additional_css = System::getValue('additional_css');

        return view('superadmin.settings.create')
                ->with(compact('settings', 'date_formats', 'additional_js', 'additional_css'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //check for demo
            if ($this->isDemo()) {
                return redirect()
                    ->action([\App\Http\Controllers\HomeController::class, 'index'])
                    ->with('status', ['success' => false, 'msg' => __('messages.feature_disabled_in_demo')]);
            }

            //update or create system data
            $systems = $request->input('system');
            foreach ($systems as $key => $value) {
                System::updateOrCreate(['key' => $key], ['value' => ! empty($value) ? $value : '']);
            }

            //update env settings
            $env_settings = $request->only('APP_NAME', 'APP_TITLE', 'MAIL_HOST', 'MAIL_PORT',
                'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'APP_LOCALE',
                'APP_TIMEZONE', 'CURRENCY_NAME', 'CURRENCY_SYMBOL', 'MAIL_FROM_ADDRESS',
                'MAIL_FROM_NAME', 'CURRENCY_CODE', 'PAYPAL_MODE', 'PAYPAL_SANDBOX_API_USERNAME',
                'PAYPAL_SANDBOX_API_PASSWORD', 'PAYPAL_SANDBOX_API_SECRET', 'PAYPAL_LIVE_API_USERNAME',
                'PAYPAL_LIVE_API_PASSWORD', 'PAYPAL_LIVE_API_SECRET', 'STRIPE_PUB_KEY', 'STRIPE_SECRET_KEY',
                'APP_DATE_FORMAT', 'APP_TIME_FORMAT', 'ACELLE_MAIL_NAME', 'ACELLE_MAIL_API');

            //checkboxes values
            $env_settings['ENABLE_REGISTRATION'] = ! empty($request->input('ENABLE_REGISTRATION')) ? $request->input('ENABLE_REGISTRATION') : 0;
            $env_settings['ENABLE_SAAS_MODULE'] = ! empty($request->input('ENABLE_SAAS_MODULE')) ? $request->input('ENABLE_SAAS_MODULE') : 0;
            $env_settings['ENABLE_OFFLINE_PAYMENT'] = ! empty($request->input('ENABLE_OFFLINE_PAYMENT')) ? $request->input('ENABLE_OFFLINE_PAYMENT') : 0;

            $found_envs = [];
            $env_path = base_path('.env');
            $env_lines = file($env_path);
            foreach ($env_settings as $index => $value) {
                foreach ($env_lines as $key => $line) {
                    //Check if present then replace it.
                    if (strpos($line, $index) !== false) {
                        $env_lines[$key] = $index.'="'.$value.'"'.PHP_EOL;

                        $found_envs[] = $index;
                    }
                }
            }

            //Add the missing env settings
            $missing_envs = array_diff(array_keys($env_settings), $found_envs);
            if (! empty($missing_envs)) {
                $missing_envs = array_values($missing_envs);
                foreach ($missing_envs as $k => $key) {
                    if ($k == 0) {
                        $env_lines[] = PHP_EOL.$key.'="'.$env_settings[$key].'"'.PHP_EOL;
                    } else {
                        $env_lines[] = $key.'="'.$env_settings[$key].'"'.PHP_EOL;
                    }
                }
            }

            $env_content = implode('', $env_lines);

            if (is_writable($env_path) && file_put_contents($env_path, $env_content)) {
                $output = [
                    'success' => true,
                    'msg' => __('messages.saved_successfully'),
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => __('messages.env_permission'),
                ];
            }

            return redirect()
                ->action([\App\Http\Controllers\HomeController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            return redirect()
                ->action([\App\Http\Controllers\HomeController::class, 'index'])
                ->with('status',
                    [
                        'success' => false,
                        'msg' => __('messages.something_went_wrong'),
                    ]
                );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
