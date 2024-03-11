<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounselorPaymentController extends Controller
{
   public function verifyAccount(Request $request)
    {
        try {
            $request->validate([
                "account_number" => 'required',
                "bank_code" => 'required',
            ]);

            $account_number = $request->account_number;
            $bank_code = $request->bank_code;

            $secret = env('PAYSTACK_SECRET_KEY');

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=".$account_number."&bank_code=".$bank_code,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
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

            if ($err) {
                throw new \Exception("cURL Error #: " . $err);
            } else {
                $result = $this->createRecipient($request->account_number, $request->bank_code);

                $res = json_decode($response);
                $resut = json_decode($result);

                return response()->json([
                    'message' => $res,
                    'recipient' => $resut
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    private function createRecipient($accountNumber, $bankCode)
    {
        $counselor = Auth::user();
        $name = $counselor->first_name. .$counselor->last_name;
        try {
            $request->validate([
                "account_number" => 'required',
                "bank_code" => 'required',
            ]);

            $url = "https://api.paystack.co/transferrecipient";

            $fields = [
                "type" => "nuban",
                "name" => $name,
                "account_number" => $request->account_number,
                "bank_code" => $request->bank_code,
                "currency" => 'NGN'
            ];

            $fields_string = http_build_query($fields);

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   public function initiateTransfer(Request $request)
    {
        try {
            $request->validate([
                "source" => 'required',
                "reason" => 'required',
                "amount" => 'required',
                "recipient" => 'required',
            ]);

            $source = $request->source;
            $reason = $request->reason;
            $amount = $request->amount;
            $recipient = $request->recipient;

            $userEarnings = auth()->user()->earnings;
            if ($amount > $userEarnings) {
                throw new \Exception('Withdrawal amount exceeds your earnings.');
            }

            $url = "https://api.paystack.co/transfer";

            $fields = [
                "source" => $source,
                "reason" => $reason,
                "amount" => $amount,
                "recipient" => $recipient
            ];

            $fields_string = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($result);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function finalizePayment(Request $request)
    {
        try {
            $request->validate([
                "transfer_code" => 'required',
                "otp" => 'required',
            ]);

            $transfer_code = $request->transfer_code;
            $otp = $request->otp;

            $url = "https://api.paystack.co/transfer/finalize_transfer";

            $fields = [
                "transfer_code" => $transfer_code,
                "otp" => $otp
            ];

            $fields_string = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            $resultArray = json_decode($result, true);

            if ($resultArray['status'] == true) {
                $transferredAmount = $resultArray['data']['amount']; 
                $user = Auth::user();
                $user->earnings -= $transferredAmount;
                $user->save();
            }

               $data = json_decode($result);
              return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   public function verifyPayment($reference)
{
    try {
        $secret = env('PAYSTACK_SECRET_KEY');
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
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

        if ($err) {
            throw new \Exception("cURL Error #: " . $err);
        }
        $data = json_decode($response);

        return response()->json($data, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
