<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Digikraaft\PaystackWebhooks\Http\Controllers\WebhooksController as PaystackWebhooksController;
class WebhooksController extends PaystackWebhooksController
{
    public function handleChargeSuccess($payload)
    {
        // Handle The Event
        dd($payload);
    }

    public function handleWebhook(Request $request)
    {
        file_put_contents(__DIR__.'/log.txt', json_encode($request->all(), JSON_PRETTY_PRINT), FILE_APPEND);
        return response()->json("OK", 200);
    }
}
