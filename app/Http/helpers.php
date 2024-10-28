<?php

use Illuminate\Support\Facades\Cache;

/**
 * boots pos.
 */
function pos_boot($ul, $pt, $lc, $em, $un, $type = 1)
{
    $ch = curl_init();
    $request_url = ($type == 1) ? base64_decode(config('author.lic1')) : base64_decode(config('author.lic2'));

    $curlConfig = [CURLOPT_URL => $request_url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => [
            'url' => $ul,
            'path' => $pt,
            'license_code' => $lc,
            'email' => $em,
            'username' => $un,
            'product_id' => config('author.pid'),
        ],
    ];
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = 'C'.'U'.'RL '.'E'.'rro'.'r: ';
        $error_msg .= curl_errno($ch);

        return redirect()->back()
            ->with('error', $error_msg);
    }
    curl_close($ch);

    if ($result) {
        $result = json_decode($result, true);

        if ($result['flag'] == 'valid') {
            if (! empty($result['data']['type'])) {
                Cache::forever('type_e', ($result['data']['type'] == base64_decode('RXh0ZW5kZWQgTGljZW5zZQ==')));
            }
        } else {
            return redirect()->back()
                ->with('error', 'I'.'nvali'.'d '.'Lic'.'ense Det'.'ails');
        }
    }
}

function is_saas_enabled()
{
    return Cache::get('type_e', false) && env('ENABLE_SAAS_MODULE');
}

/**
 * Check if an Application
   is installed or not
 */
function isAppInstalled()
{
    return file_exists(base_path('.env'));
}
