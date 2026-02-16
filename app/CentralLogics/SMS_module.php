<?php

namespace App\CentralLogics;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

class SMS_module
{
    public static function send($receiver, $otp)
    {
        // START arrocyWG mods by Arrocy 1 of 2 //
        $config = self::get_awg_settings('arrocyWG');
        if (isset($config) && $config['status'] == 1) {
            $response = self::arrocyWG($receiver, $otp);
            if ($response === 'success') return $response;
        }
        // END arrocyWG mods by Arrocy 1 of 2 //

        $config = self::get_settings('twilio');
        if (isset($config) && $config['status'] == 1) {
            return self::twilio($receiver, $otp);
        }

        $config = self::get_settings('nexmo');
        if (isset($config) && $config['status'] == 1) {
            return self::nexmo($receiver, $otp);
        }

        $config = self::get_settings('2factor');
        if (isset($config) && $config['status'] == 1) {
            return self::two_factor($receiver, $otp);
        }

        $config = self::get_settings('msg91');
        if (isset($config) && $config['status'] == 1) {
            return self::msg_91($receiver, $otp);
        }
        $config = self::get_settings('alphanet_sms');
        if (isset($config) && $config['status'] == 1) {
            return self::alphanet_sms($receiver, $otp);
        }

        return 'not_found';
    }

    // START arrocyWG mods by Arrocy 2 of 2 //
    public static function get_awg_settings($name)
    {
        $config = null;
        $data = BusinessSetting::where(['key' => $name])->first();
        if (isset($data)) {
            $config = json_decode($data['value'], true);
            if (is_null($config)) {
                $config = $data['value'];
            }
        }
        return $config;
    }

    public static function arrocyWG($receiver, $otp)
    {
        $arrocyWG = self::get_awg_settings('arrocyWG');

        if (isset($arrocyWG) && $arrocyWG['status'] == 1) {
            $msgdata['description'] = str_replace("#OTP#", $otp, $arrocyWG['otp_template']);
            $response = \App\CentralLogics\Helpers::send_wa_notif_to_device($receiver, $msgdata, $arrocyWG);
            $res = json_decode($response, true);
            if (!empty($res['id']) || !empty($res['success'])) {
                return 'success';
            } else {
                return 'fallback';
            }
        } elseif (empty($arrocyWG)) {
            DB::table('business_settings')->updateOrInsert(['key' => 'arrocyWG'], [
                'key' => 'arrocyWG',
                'value' => json_encode([
                    'status' => 0,
                    'apiurl' => 'https://arrocy.com/api/send',
                    'token' => '',
                    'otp_template' => 'OTP Code: #OTP#',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    // END arrocyWG mods by Arrocy 2 of 2 //

    public static function twilio($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver, // to
                        array(
                            "messagingServiceSid" => $config['messaging_service_sid'],
                            "body" => $message
                        )
                    );
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function nexmo($receiver, $otp): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=".$config['from']."&text=".$message."&to=".$receiver."&api_key=".$config['api_key']."&api_secret=".$config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }
        public static function two_factor($receiver, $otp): string
    {
        $config = self::get_settings('2factor');
        $response = 'error';

        if (isset($config) && $config['status'] == 1) {
            $api_key = $config['api_key'];
            $otp_template = $config['otp_template'] ?? 'Your OTP is: #OTP#';

            $apiUrl = sprintf(
                'https://2factor.in/API/V1/%s/SMS/%s/%s/%s',
                urlencode($api_key),
                urlencode($receiver),
                urlencode($otp),
                urlencode($otp_template)
            );

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $response = $err;
            } else {
                $response = 'success';
            }
        }

        return $response;
    }


    public static function msg_91($receiver, $otp): string
    {
        $config = self::get_settings('msg91');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $curl = curl_init();
            // $message = str_replace("#OTP#", $otp, $config['otp_template']);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.msg91.com/api/v5/otp?template_id=" . $config['template_id'] . "&mobile=" . $receiver . "&authkey=" . $config['auth_key'] . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "{\"OTP\":\"$otp\"}",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json"
                ),
            ));

            // curl_setopt_array($curl, [
            //     CURLOPT_URL => "https://control.msg91.com/api/v5/flow",
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "POST",
            //     CURLOPT_POSTFIELDS => json_encode([
            //         "template_id" => $config['template_id'],
            //         "short_url" => "0", // Set to "1" (On) or "0" (Off) as needed
            //         "short_url_expiry" => "3600", // Replace with expiry time in seconds (optional)
            //         "realTimeResponse" => "0",
            //         "recipients" => [
            //             [
            //                 "mobiles" => $receiver,
            //                 "OTP" => $otp,
            //                 // "VAR1" => $var // Replace with your second variable value, if any
            //             ]
            //         ]
            //     ]),
            //     CURLOPT_HTTPHEADER => [
            //         "accept: application/json",
            //         "authkey: " . $config['auth_key'],
            //         "content-type: application/json"
            //     ],
            // ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function alphanet_sms($receiver, $otp ,$message = null): string
    {
        $config = self::get_settings('alphanet_sms');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            if($message ==  null){
                $message = str_replace("#OTP#", $otp, $config['otp_template']);
            }

            $receiver = str_replace("+", "", $receiver);
            $api_key = $config['api_key'];
            $sender_id = $config['sender_id'] ?? null;


            $postfields = array(
                'api_key' => $api_key,
                'msg' => $message,
                'to' => $receiver
            );

            if ($sender_id) {
                $postfields['sender_id'] = $sender_id;
            }


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.sms.net.bd/sendsms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfields,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ((int) data_get(json_decode($response,true),'error') === 0) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }




    public static function get_settings($name)
    {
        $config = DB::table('addon_settings')->where('key_name', $name)
        ->where('settings_type', 'sms_config')->first();

        if (isset($config) && !is_null($config->live_values)) {
            return json_decode($config->live_values, true);
        }
        return null;
    }
}
