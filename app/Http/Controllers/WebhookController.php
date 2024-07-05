<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handlePaystackWebhook(Request $request)
    {
          $counselor = Counselor::where('id', auth()->user()->id)->first();
        // Verify that the request is coming from Paystack
         $secret = env('PAYSTACK_SECRET_KEY');
        $signature = $request->header('x-paystack-signature');

        if ($signature !== hash_hmac('sha512', $request->getContent(), $secret)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->all();

        // Log the payload for debugging (optional)
        \Log::info('Paystack Webhook Payload: ', $payload);

        // Handle the event
        if ($payload['event'] == 'transfer.success') {
            $this->handleTransferSuccess($payload['data']);
        } elseif ($payload['event'] == 'transfer.failed') {
            $this->handleTransferFailed($payload['data']);
        }

        return response()->json(['status' => 'success'], 200);
    }

   protected function handleTransferSuccess($data)
    {
    // Implement your logic to handle successful transfer
    $transferId = $data['id'];
    $amount = $data['amount']; // Amount is typically in kobo from Paystack, convert to naira
    $recipient = $data['recipient'];

    // Convert amount from kobo to naira
    $amountInNaira = $amount / 100;

    // Get the authenticated user
    $user = auth()->user();

    // Deduct the amount from user's earnings
    // if ($user->earnings >= $amountInNaira) {
    //     $user->earnings -= $amountInNaira;
    //     $user->save();
    // } else {
    //     // Handle case where earnings are insufficient
    //     Log::warning("Transfer amount exceeds user earnings: Transfer ID {$transferId}, Amount: {$amountInNaira}, User ID: {$user->id}");
    // }


    \Log::info("Transfer successful: Transfer ID {$transferId}, Amount: {$amountInNaira}, Recipient: {$recipient}");
}


    protected function handleTransferFailed($data)
    {
        // Implement your logic to handle failed transfer
        $transferId = $data['id'];
        $reason = $data['reason'];

        // Update your database or perform any other necessary actions
        // Example: Mark the transfer as failed in your database
        \Log::info("Transfer failed: Transfer ID {$transferId}, Reason: {$reason}");
    }
}
