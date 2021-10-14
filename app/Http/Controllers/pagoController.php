<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago;

class pagoController extends Controller
{

    public function pagar(Request $request) {
        try {
            MercadoPago\SDK::setAccessToken(config('services.mercadopago.token'));
            $payment = new MercadoPago\Payment();
            $payment->transaction_amount = (float)$request->transaction_amount;
            $payment->token = $request->token;
            $payment->description = $request->description;
            $payment->installments = (int)$request->installments;
            $payment->payment_method_id = $request->payment_method_id;
            $payment->issuer_id = (int)$request->issuer_id;

            $payer = new MercadoPago\Payer();
            $payer->email = $request->payer['email'];
            $payer->identification = array(
                "type" => $request->payer['identification']['type'],
                "number" => $request->payer['identification']['number']
            );
            $payment->payer = $payer;

            $payment->save();

            $response = array(
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'id' => $payment->id
            );

            return $response;

            if (is_null($response['status']))
                return response()->json(['status' => false, 'message' => 'Ocurrio un problema con el pago'], 404);

            return response()->json(['status' => true, 'message' => 'El pago sea acreditado con exito', 'data' => $response]);
        } catch (\Throwable $e) {
            logger($e);
            return response()->json(['status' => false, 'message' => 'Ocurrio un problema con el servidor'], 500);
        }

    }
}
