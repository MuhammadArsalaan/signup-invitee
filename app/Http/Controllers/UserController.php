<?php

namespace App\Http\Controllers;

use App\Mail\PinVerificationEmail;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    function sendInvitation(Request $req){

        try{
            $rules = array(
                'email' => 'required|unique:users'
            );
            $validator = Validator::make($req->all(), $rules);
            
            if($validator->fails()){
                return $validator->errors();
            }
    
            $user = new User();
            $user->email = $req->email;
            $user->created_at = date("Y-m-d H:i:s");
            $isSaved = $user->save();
            if(!$isSaved){
                return response("Invitation Email snet successfully.");
            }
    
            $result = $this->sendEmail($req->email);
            if($result){
                return response("Invitation Email snet successfully.");
            }
            return response("Something went wrong.");
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    function getVerifyPin($email){
        try{
            return "Use this link in <b>Postman</b> with <b>POST</b> method.";
        }
        catch(\Exception $e){
            return $e->getMessage();
        }

    }

    function userInvitationMessage($email){
        
        try{
            return "Use this link in <b>Postman</b> with <b>POST</b> method.";
        }
        catch(\Exception $e){
            return $e->getMessage();
        }

    }
    
    function registerUser(Request $req, $email){

        try{
            // Check if Link is valid
            $email = Crypt::decrypt($email);
            $user = User::where(['email'=>$email])->first();
            if(!$user){
                return response("Invalid Email");
            }
    
            // Validations for Username and Password
            $rules = array(
                'username' => 'required|unique:users|min:4|max:20',
                'password' => 'required'
            );
    
            $validator = Validator::make($req->all(), $rules);
            if($validator->fails()){
                return $validator->errors();
            }
            
            $pin = random_int(100000, 999999);
            // Save data in users table
            $user->username = $req->username;
            $user->password = Hash::make($req->password);
            $user->pin = $pin;
            $user->registered_at = date("Y-m-d H:i:s");
            $result = $user->save();
    
            if($result){
                $this->sendEmail($email, $pin);
                return response("Registered Successfully.");
            }
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    function verifypin(Request $req, $email){

        try{

            $email = Crypt::decrypt($email);
            $user = User::where(['email'=>$email, 'pin_verified'=>'0'])->first();

            if(!$user){
                return response("invalid user");
            }

            if($user->pin !== $req->pin){
                return response("Invalid Pin");
            }

            $user->pin = $req->pin;
            $user->pin_verified = '1';
            $result = $user->save();

            if($result){
                return response("User registered successfully");
            }
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }
    
    function login(Request $req){

        try{

            $rules = array(
                'email' => 'required',
                'password' => 'required'
            );
    
            $validator = Validator::make($req->all(), $rules);
            if($validator->fails()){
                return $validator->errors();
            }
    
            $user = User::where(['email'=> $req->email])->first();
            
            if (!$user) {
                return response([
                    'message' => ['Email not found.']
                ], 404);
            }
            
            $passwordMatched = Hash::check($req->password, $user->password);
            if (!$passwordMatched) {
                return response([
                    'message' => ['Password is wrong.']
                ], 404);
            }
    
            $token = $user->createToken('my-app-token')->plainTextToken;
        
            $response = [
                'user' => $user,
                'token' => $token
            ];
        
            return response($response, 201);
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    function updateUser(Request $req, $id){
        
        try{

            $image = '';
            $rules = array(
                'email' => 'required|unique:users,email,'.$id,
                'username' => 'required|min:4|max:20|unique:users,username,'.$id,
                // 'avatar' => 'max_width:256px|max_height:256px'
            );
    
            $validator = Validator::make($req->all(), $rules);
            if($validator->fails()){
                return $validator->errors();
            }
    
            $user = User::find($id);

            if($user->profile){
                $filePath = public_path('uploads/users/'.$user->profile);
    
                if(File::exists($filePath)){
                    unlink($filePath);                
                }
            }

            if($req->hasfile('avatar')){
                $image =  time() .'_'. $req->file('avatar')->getClientOriginalName();
                $path = base_path() . '/public/uploads/users/';
                $req->file('avatar')->move($path, $image);
            }
            
            $user->name = $req->name;
            $user->email = $req->email;
            $user->username = $req->username;
            $user->user_role = $req->user_role;
            $user->profile = $image;
            $user->updated_at = date("Y-m-d H:i:s");
            $result = $user->save();
            
            if($result){
                return response("Successfuly Updated");
            }
        }
        catch(\Exception $e){
            return $e->getMessage();
        }
    }

    private function sendEmail($email=false, $pin=false){
        
        $encryptedEmail = Crypt::encrypt($email);

        $details = array(
            'title'=>'Invitation Email.',
            'email'=>$encryptedEmail
        );
        
        if($pin){
            $details['pin'] = $pin;
            Mail::to('hfzhma@gmail.com')->send(new PinVerificationEmail($details));
        }else{
            Mail::to('hfzhma@gmail.com')->send(new WelcomeEmail($details));
        }

        if(Mail::failures()){
            return false;
        }

        return true;
    }

}
