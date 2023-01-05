<?php

namespace DD4You\Dpay\PG;

class PhonePe
{
    protected static string $end_point;

    public static function mTid($isRefund = false)
    {
        // Y - A four digit representation of a year
        // m - A numeric representation of a month (from 01 to 12)
        // d - The day of the month (from 01 to 31)
        // H - 24-hour format of an hour (00 to 23)
        // i - Minutes with leading zeros (00 to 59)
        // s - Seconds, with leading zeros (00 to 59)
        // I (capital i) - Whether the date is in daylights savings time (1 if Daylight Savings Time, 0 otherwise)
        // z - The day of the year (from 0 through 365)

        if ($isRefund) {
            return 'ROD' . date('YmdHisIz');
        }
        return 'MT' . date('YmdHisIz');
    }

    public function callPayApi($merchantTransactionId, $userId, $amount, $mobileNumber, $redirectUrl, $callbackUrl = null)
    {

        self::$end_point = "/pg/v1/pay";

        $payload = [
            'merchantId' => config('dpay.phonepe.merchant_id'),
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $userId,
            'amount' => $amount * 100,
            'redirectUrl' => $redirectUrl,
            'redirectMode' => 'GET',
            'callbackUrl' => $callbackUrl ?? $redirectUrl,
            'mobileNumber' => $mobileNumber,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE'
            ],
        ];

        $base64 = base64_encode(json_encode($payload));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => self::xVerify($base64),
        ])->post(config('dpay.phonepe.host') . self::$end_point, ['request' => $base64]);

        return $response->body();
    }

    public function callRefundApi($originalTransactionId, $merchantTransactionId, $userId, $amount, $callbackUrl)
    {
        self::$end_point = "/pg/v1/refund";

        $payload = [
            'merchantId' => config('dpay.phonepe.merchant_id'),
            'merchantUserId' => $userId,
            'originalTransactionId' => $originalTransactionId,
            'merchantTransactionId' => $merchantTransactionId,
            'amount' => $amount * 100,
            'callbackUrl' => $callbackUrl,
        ];

        $base64 = base64_encode(json_encode($payload));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => self::xVerify($base64),
        ])->post(config('dpay.phonepe.host') . self::$end_point, ['request' => $base64]);

        return $response->body();
    }

    public function callStatusApi($merchantTransactionId)
    {

        self::$end_point = '/pg/v1/status/' . config('dpay.phonepe.merchant_id') . '/' . $merchantTransactionId;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => self::xVerify(),
            'X-MERCHANT-ID' => config('dpay.phonepe.merchant_id'),
        ])->get(config('dpay.phonepe.host') . self::$end_point);

        return $response->body();
    }

    private static function xVerify($payload = '')
    {
        $data = $payload . self::$end_point . config('dpay.phonepe.salt_key');

        $sha256 = hash('sha256', $data);

        return $sha256 . "###" . config('dpay.phonepe.salt_index');
    }
}
