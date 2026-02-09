<?php

namespace App\Helpers;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Http\Response;

class OtpHelper
{
    /**
     * @param string $identifier
     * @param int $digits
     * @param int $validity
     * @return mixed
     */
    public function generate(string $identifier, int $digits = 4, int $validity = 10) : object
    {
        Otp::where('identifier', $identifier)->where('valid', true)->delete();

        $token = str_pad($this->generatePin(), 4, '0', STR_PAD_LEFT);

        if ($digits == 5)
            $token = str_pad($this->generatePin(5), 5, '0', STR_PAD_LEFT);

        if ($digits == 6)
            $token = str_pad($this->generatePin(6), 6, '0', STR_PAD_LEFT);

        Otp::create([
            'identifier' => $identifier,
            'token' => $token,
            'validity' => $validity
        ]);

        return (object)[
            'token' => $token,
            'expires_in' => $validity,
        ];
    }

    /**
     * @param string $identifier
     * @param string $token
     * @param bool $isValidate
     * @return mixed
     */
    public function validate(string $identifier, string $token, bool $isValidate) : object
    {
        $otp = Otp::where('identifier', $identifier)->where('token', $token)->first();

        if ($otp == null) {
            return (object)[
                'status' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'The token does not exist.'
            ];
        } else {
            if ($otp->valid == true) {
                $carbon = new Carbon;
                $now = $carbon->now();
                $validity = $otp->created_at->addMinutes($otp->validity);

                if (strtotime($validity) < strtotime($now)) {
                    $otp->valid = false;
                    $otp->save();

                    return (object)[
                        'status' => false,
                        'code' => Response::HTTP_NOT_ACCEPTABLE,
                        'message' => 'The token has been expired or invalid.'
                    ];
                } else {
                    if ($isValidate) {
                        $otp->valid = false;
                        $otp->save();
                    }
                    
                    return (object)[
                        'status' => true,
                        'code' => Response::HTTP_OK,
                        'message' => 'The token is valid.'
                    ];
                }
            } else {
                return (object)[
                    'status' => false,
                    'code' => Response::HTTP_NOT_ACCEPTABLE,
                    'message' => 'The token has been expired or invalid.'
                ];
            }
        }
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function check(string $token) : object
    {
        $otp = Otp::where('token', $token)->first();

        if ($otp == null) {
            return (object)[
                'status' => false,
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'The token does not exist.',
                'data' => null,
            ];
        } else {
            return (object)[
                'status' => true,
                'code' => Response::HTTP_OK,
                'message' => 'The token does not exist.',
                'data' => $otp,
            ];
        }
    }

    /**
     * @param int $digits
     * @return string
     */
    private function generatePin($digits = 4)
    {
        $i = 0;
        $pin = "";

        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return $pin;
    }
}