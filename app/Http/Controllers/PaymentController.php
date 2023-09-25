<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function stripePayment()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        $checkout = $stripe->checkout->sessions->create([
            'success_url' => 'http://lar/success',
            'cancel_url' => 'http://lar/cancel',
//            'success_url' => redirect()->back()->getTargetUrl(),
//            'cancel_url' => redirect()->back()->getTargetUrl(),
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => 500,
                        'product_data' => [
                            'name' => 'testname'
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',

        ]);

        return ['oneItem' => $checkout];

    }

    public function stripeSub()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
        $sub = $stripe->checkout->sessions->create([
//            'success_url' => 'http://lar/success',
//            'cancel_url' => 'http://lar/cancel',
            'success_url' => url('/success'),
            'cancel_url' => url('/cancel'),
            'line_items' => [
                [
                    'price' => 'price_1N6xVKIyorCOiPiHun4TXW3s',

                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',

        ]);
        return ['sub' => $sub];
    }

    public function webhook(Request $request)
    {
        \Log::info("webhook");
        $endpoint_secret = env('webhook_key');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $payload=@file_get_contents('php://input');
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            echo 'Webhook error while validating signature.';
            http_response_code(400);
            exit();
        }
        if ($request->type ==='checkout.session.completed'){
            //validate the purchase
            //можно делать что хочешь тут ужже пользователь заплотил
        }
        return response()->json(["status" => 'success']);
    }
}
