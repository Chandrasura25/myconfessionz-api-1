<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                return response()->json([
                    'message' => $response,
                    'recipient' => $result
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

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            //execute post
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
            // Validate the incoming request
            $request->validate([
                "source" => 'required',
                "reason" => 'required',
                "amount" => 'required',
                "recipient" => 'required',
            ]);

            // Get the request parameters from the request object
            $source = $request->source;
            $reason = $request->reason;
            $amount = $request->amount;
            $recipient = $request->recipient;

            $url = "https://api.paystack.co/transfer";

            // Set the fields for the transfer
            $fields = [
                "source" => $source,
                "reason" => $reason,
                "amount" => $amount,
                "recipient" => $recipient
            ];

            $fields_string = http_build_query($fields);

            // Open connection
            $ch = curl_init();

            // Set the URL, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ));

            // So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            // Execute post
            $result = curl_exec($ch);
            curl_close($ch);

            // Return the result with appropriate status code
            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response with appropriate status code
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        public function verifyPayment(Request $request){
          $url = "https://api.paystack.co/transfer/finalize_transfer";

          $fields = [
            "transfer_code" => "TRF_vsyqdmlzble3uii", 
            "otp" => "928783"
          ];

          $fields_string = http_build_query($fields);

          //open connection
          $ch = curl_init();
          
          //set the url, number of POST vars, POST data
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_POST, true);
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer SECRET_KEY",
            "Cache-Control: no-cache",
          ));
          
          //So that curl_exec returns the contents of the cURL; rather than echoing it
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
          
          //execute post
          $result = curl_exec($ch);
          echo $result;
    }
                
}
