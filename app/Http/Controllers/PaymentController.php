<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PaymentController extends Controller
{
   
    public function verifyPayment($reference){
        $secret = "sk_test_b5baca5cf564ac66a202bd05b8b47ac1ef7f710c";
        // sk_live_f7879a3439ecb235b7499c2451caf792b29a2905 //live_secret_key
        // pk_live_34f2c871a75656957e578fce135fcf955dad925f //live_pubic key
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,

            //TAKEOFF ON LIVE HOSTING
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,

            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $secret",
            "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $newData = json_decode($response);

        return [$newData];
    }
    public function paymentSuccess(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer',
            ]);

            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if($request->amount < 100){
                return response()->json(['error' => 'Min deposit is 100'], 401);
            }

            $user = User::where('id', auth()->id())->first();

            if($user){
                $deposit = User::where('id', auth()->id())->update(['balance' => $user->balance + ($request->input('amount'))]);
                if($deposit){
                    return response()->json(['success' => "Deposit successful"]);
                }else{
                    return response()->json(['success' => "Deposit unsuccessful, try again"]);
                }
            }else{
                return response()->json(['error' => "User not found"]);
            }

        } catch (\Exception $e) {
            // Log or output the exception message for debugging
            dd($e->getMessage());
        }
    }
    
   

}

