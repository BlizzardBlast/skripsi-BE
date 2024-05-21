<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class PaypalController extends Controller
{
    //

    private function getAccessToken(): string
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        } 

        $clientId = config('paypal.client_id');
        $clientSecret = config('paypal.client_secret');
        $authString = base64_encode($clientId . ':' . $clientSecret);

        $headers = [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . $authString
        ];

        $response = Http::withHeaders($headers)
            ->withBody('grant_type=client_credentials', 'application/x-www-form-urlencoded')
            ->post(config('paypal.base_url') . '/v1/oauth2/token');

        return json_decode($response->body())->access_token;
    }

    /**
     * @return string
     */
    public function create(Request $request): string
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        } 

        $amount = floatval($request->amount);
        $id = uuid_create();

        $headers = [
            'Content-Type'      => 'application/json',
            'Authorization'     => 'Bearer ' . $this->getAccessToken(),
            'PayPal-Request-Id' => $id,
        ];

        $body = [
            "intent"         => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => $id,
                    "amount"       => [
                        "currency_code" => "GBP",
                        "value"         => number_format($amount, 2),
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders($headers)
            ->withBody(json_encode($body), 'application/json')
            ->post(config('paypal.base_url') . '/v2/checkout/orders');

        Session::put('request_id', $id);
        Session::put('order_id', json_decode($response->body())->id);

        return json_decode($response->body())->id;
    }

    /**
     * @return mixed
     */
    public function complete()
    {
        if (!Auth::check()) {
            return response()->json(null, 400);
        } 
        
        $url = config('paypal.base_url') . '/v2/checkout/orders/' . Session::get('order_id') . '/capture';

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];

        $response = Http::withHeaders($headers)
            ->post($url, null);

        return json_decode($response->body());
    }
}
