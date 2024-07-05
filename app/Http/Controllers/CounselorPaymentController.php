<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Counselor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CounselorPaymentController extends Controller
{
  
public function getBanks()
{
    $secretKey = env('PAYSTACK_SECRET_KEY');

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/bank",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return response()->json([
            'error' => 'cURL Error #: ' . $err,
        ], 500);
    } else {
        $response_data = json_decode($response, true);

        if ($response_data && isset($response_data['status']) && $response_data['status'] === true) {
            return response()->json([
                'success' => true,
                'data' => $response_data['data'],
            ], 200);
        } else {
            return response()->json([
                'error' => 'Failed to retrieve banks from Paystack.',
                'message' => $response_data['message'] ?? 'An error occurred.',
            ], 400);
        }
    }
}
public function verifyAccount(Request $request)
{
    try {
        $request->validate([
            'account_number' => 'required',
            'bank_code' => 'required',
        ]);

        $account_number = $request->account_number;
        $bank_code = $request->bank_code;

        $secret = env('PAYSTACK_SECRET_KEY');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/bank/resolve?account_number=$account_number&bank_code=$bank_code",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $secret",
                'Cache-Control: no-cache',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception("cURL Error #: $err");
        } else {
            $result = $this->createRecipient($account_number, $bank_code);

            $response_data = json_decode($response);
            $result_data = json_decode($result);

            // Save recipient_code to the database
            if (isset($result_data->data->recipient_code)) {
                $recipient_code = $result_data->data->recipient_code;

                // Assuming you have a Counselor model and authenticated user
                $counselor = Counselor::where('id', auth()->user()->id)->first();
                if ($counselor) {
                    $counselor->recipient_code = $recipient_code;
                    $counselor->save();
                }
            }

            return response()->json([
                'message' => $response_data->message,
                'result'=>$result_data,
            ], 200);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function createRecipient($accountNumber, $bankCode)
{
    $user = Auth::user();
    $name = $user->first_name . ' ' . $user->last_name;

    try {
        $url = 'https://api.paystack.co/transferrecipient';

        $fields = [
            'type' => 'nuban',
            'name' => $name,
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
            'currency' => 'NGN',
        ];

        $fields_string = http_build_query($fields);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
            'Cache-Control: no-cache',
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            throw new \Exception("cURL Error #: $err");
        }

        return $result;
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
private function generateReference()
    {
        // Generate a UUID v4
        return Str::uuid()->toString();
    }
public function initiateTransfer(Request $request)
{
    try {
        $request->validate([
            "reason" => 'required|string',
            "amount" => 'required|numeric|min:1',
        ]);

        $reason = $request->reason;
        $amount = $request->amount;
        $reference = $this->generateReference();
        // Get the authenticated user's earnings
        $userEarnings = auth()->user()->earnings;
        if ($amount > $userEarnings) {
            throw new \Exception('Withdrawal amount exceeds your earnings.');
        }

        // Get the recipient_code from the counselors table
        $counselor = Counselor::where('id', auth()->user()->id)->first();
        if (!$counselor || !$counselor->recipient_code) {
            throw new \Exception('Recipient not found.');
        }
        $recipient = $counselor->recipient_code;

        $url = "https://api.paystack.co/transfer";

        $fields = [
            "source" => 'balance', // Hardcode the source to 'balance'
            "reason" => $reason,
            "amount" => $amount * 100, // Paystack expects the amount in kobo
            "recipient" => $recipient,
            "reference"=> $reference,
        ];

        $fields_string = http_build_query($fields);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            throw new \Exception("cURL Error #: $err");
        }
        if ($counselor->earnings >= $amount) {
                $counselor->earnings -= $amount;
                $counselor->save();
            } else {
                // Handle case where earnings are insufficient
                Log::warning("Transfer amount exceeds counselor earnings: Amount: {$amount}, counselor ID: {$counselor->id}");
            }
        $data = json_decode($result);
        return response()->json($data, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
