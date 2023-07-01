<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'usercode' => 'required|unique:users,usercode',
            'password' => 'required|string|min:8',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'country' => 'required|string',
            'state' => 'required|string',
            'recovery_question1' => 'required|string',
            'answer1' => 'required',
            'recovery_question2' => 'required|string',
            'answer2' => 'required',
            'recovery_question3' => 'required|string',
            'answer3' => 'required'
        ]);

        $formfields = ([
            'usercode' => $request->usercode,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'country' => $request->country,
            'state' => $request->state,
            'password' => bcrypt($request->password),
            'recovery_question1' => $request->recovery_question1,
            'answer1' => $request->answer1,
            'recovery_question2' => $request->recovery_question2,
            'answer2' => $request->answer2,
            'recovery_question3' => $request->recovery_question3,
            'answer3' => $request->answer3,
        ]);


        $user = User::create($formfields);

        $token = $user->createToken('novit17')->plainTextToken;

        $response = [
            'message' => $user,
            'token' => $token
        ];

        return response()->json($response, 201);
    }

    public function login(Request $request){
        $request->validate([
            'usercode' => 'required',
            'password' => 'required'
        ]);

        // Check if user exists
        $user = User::where('usercode', $request->usercode)->first();

        // Check password

        if(!$user || !Hash::check($request->password, $user->password)){
            $response = [
                'message' => 'Incorrect credentials'
            ];

            return response()->json($response, 401);
        }

            $token = $user->createToken('novit17')->plainTextToken;

            $response = [
                'message' => $user,
                'token' => $token
            ];

            return response()->json($response, 201);
    }


    public function logout(Request $request){
        auth()->user()->tokens()->delete();

        $response = [
            'message' => 'Logged out'
        ];

        return response()->json($response, 200);
    }

    public function passwordResetRequest(Request $request){

        $request->validate([
            'usercode' => 'required',
        ]);

        $user = User::where('usercode', $request->usercode)->first();

        if(!$user){
            $response = [
                "message" => "User does not exists"
            ];

            return response()->json($response, 401);
        }

        $token = $user->createToken('novit17')->plainTextToken;

        $response = [
            'message'=> $user,
            "token" => $token
        ];

        return response()->json($response, 200);
    }

    public function passwordRecoveryAnswer(Request $request){
            $request->validate([
                'usercode' => 'required',
                'recovery_question' => 'required',
                'answer' => 'required',
            ]);

            $user = User::where('usercode', $request->usercode)->first();

            if(!$user){
                $response = [
                    "message" => "User does not exists"
                ];

                return response()->json($response, 401);
            }

            if(
                (($request->recovery_question == $user->recovery_question1) && ($request->answer == $user->answer1)) ||
                (($request->recovery_question == $user->recovery_question2) && ($request->answer == $user->answer2)) ||
                (($request->recovery_question == $user->recovery_question3) && ($request->answer == $user->answer3))
                ){
                    return response()->json(["message" => "correct!"], 200);
                }
            return response()->json(["message" => "Recovery question and/or answer incorrect!"], 401);


        }
    public function passwordReset(Request $request){
        $request->validate([
            'usercode' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('usercode', $request->usercode)->first();

        if(!$user){
            $response = [
                "message" => "User does not exists"
            ];

            return response()->json($response, 401);
        }

        $password = bcrypt($request->password);

        User::where('usercode', $request->usercode)->update(['password' => $password]);

        $response = [
            'message' => "password changed successfully!"
        ];

        return response()->json($response, 200);

    }

    public function deleteAccount($id){
        // Find the user by ID
        $user = User::find($id);


        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if($user->usercode != auth()->user()->usercode){
                $response = [
                    'message' => "Unauthorized action!"
                ];

                return response()->json($response, 200);
            }

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User account deleted'], 200);
    }
}
