<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Model
use App\Models\User;

// 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// Email Validation
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

class UsersAccessController extends Controller
{
    //
    /**
     * @group Admin Management
     *
     * Register a new Admin.
     *
     * This endpoint allows you to register a new admin user by providing a name, email, and password.
     *
     * @bodyParam name string required The name of the admin. Example: John Doe
     * @bodyParam email string required The email address of the admin. Must be a valid and unique email. Example: admin@example.com
     * @bodyParam password string required The password for the admin account. Must be at least 8 characters, include an uppercase letter, lowercase letter, digit, and special character. Example: Password123!
     *
     * @response 201 {
     *   "success": 1,
     *   "message": "Admin registered successfully",
     *   "admin": {
     *     "id": 1,
     *     "name": "John Doe",
    *     "email": "admin@example.com",
    *     "created_at": "2024-11-04T12:00:00.000000Z",
    *     "updated_at": "2024-11-04T12:00:00.000000Z"
    *   }
    * }
    *
    * @response 422 {
    *   "success": 0,
    *   "error": "The email field is required."
    * }
    *
    * @response 500 {
    *   "success": 0,
    *   "message": "Error while Register",
    *   "error": "Detailed error message."
    * }
    */
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
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first() // Get the first error message directly
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

    
    /**
     * @group Admin Management
     *
     * Admin Login
     *
     * This endpoint allows an admin user to log in using their email and password. Upon successful authentication, a token is returned.
     *
     * @bodyParam email string required The email address of the admin. Example: admin@example.com
     * @bodyParam password string required The password for the admin account. Must be at least 8 characters, include an uppercase letter, lowercase letter, digit, and special character. Example: Password123!
     *
     * @response 200 {
     *   "access_token": "your_jwt_token_here",
     *   "token_type": "bearer",
     *   "expires_in": 3600
     * }
     *
     * @response 404 {
     *   "success": 0,
     *   "error": "Email does not exist"
     * }
     *
     * @response 401 {
     *   "success": 0,
     *   "error": "Password does not match"
     * }
     *
     * @response 500 {
     *   "success": 0,
     *   "message": "Error while Login",
     *   "error": "Detailed error message"
     * }
     */   
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
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first() // Get the first error message directly
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
