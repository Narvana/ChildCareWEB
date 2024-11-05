<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Model
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class UsersAccessController extends Controller
{
    //
    public function AdminRegister(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'unique:users',
                'max:50',
                function ($attribute, $value, $fail) {
                    $validator = new EmailValidator();
                    if (!$validator->isValid($value, new RFCValidation())) {
                        $fail('The '.$attribute.' is invalid.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/'
            ]
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all(); // Get all error messages
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[] = $error;
            }
            return response()->json([
                'success' => 0,
                'error' => $formattedErrors[0]
            ], 422);
        }

        try {
            //code...
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            // $token = JWTAuth::fromUser($user);
            // $admin->assignRole('Admin');
    
            if ($admin) {
                return response()->json([
                    'success' => 1,
                    'message' => 'Admin registered successfully',
                    'admin' => $admin,
                ], 201);
            }
             else {
                return response()->json([
                    'success' => 0,
                    'error' => 'Failed to register Admin'
                ], 500);
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => '0',
                'message' => 'Error while Register',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function AdminLogin(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email' => 'required|email|max:50',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/'
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all(); // Get all error messages
            $formattedErrors = [];
            foreach ($errors as $error) {
                $formattedErrors[] = $error;
            }
            return response()->json([
                'success' => 0,
                'error' => $formattedErrors[0]
            ], 422);
        }

            //code...
            $credentials = $request->only('email','password');
            $admin = User::where('email',$credentials['email'])->first();
            
            if(!$admin){
                return response()->json(
                    [
                        'success' => 0,
                        'error' => 'Email does not exist'
                    ], 404);
            }
            if(!Hash::check($credentials['password'],$admin->password))
            {
                return response()->json(
                    [
                        'success' => 0,
                        'error' => 'Password does not match'
                    ], 401);
            }

            // if (!$admin->hasRole('Admin')) 
            // {
            //     // User has the 'admin' role
            //     return response()->json([ 'success' => 0,'error' => 'Unauthorized Login Role. Only User can Login'], 401);  
            // }    

        try {
                $token=Auth::guard('api')->login($admin);
                return $this->respondWithToken($token);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => 0,
                'message' => 'Error while Login',
                'error' => $e->getMessage()
            ], 500);
        }
            //  //  to check roles
            // $roles = $user->getRoleNames();
            // print_r($roles->toArray());die();
            
            // $verificationStatus=UserVerification::where('userID',$user->id)->get();
            // if($verificationStatus[0]->verified === 1)
            // {
            // }
            // return response()->json([ 'success' => 0,'error' => 'Please Before login verify your registration by clicking on the link you have been sent on your'], 401);  
    }

    protected function respondWithToken($token){
        return response()->json([
            'success' => 1,
            'user'=>Auth::guard('api')->user(),
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->guard('api')->factory()->getTTL()*60,
            
        ]);
    }

}
