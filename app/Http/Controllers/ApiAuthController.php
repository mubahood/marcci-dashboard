<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sacco;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{

    use ApiResponser;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    /*  public function __construct()
    {

        $token = auth('api')->attempt([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        die($token);
        $this->middleware('auth:api', ['except' => ['login', 'register', 'password-reset']]);
    } */


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }


    public function password_reset(Request $r)
    {

        $r->validate([
            'phone_number' => 'required',
        ]);


        $phone_number = $r->phone_number;
        $isEmail = false;

        //check if $phone_number is email address
        if (filter_var($phone_number, FILTER_VALIDATE_EMAIL)) {
            $isEmail = true;
        }

        if (!$isEmail) {
            $phone_number = Utils::prepare_phone_number($r->phone_number);
            if (!Utils::phone_number_is_valid($phone_number)) {
                return $this->error('Invalid phone number.');
            }
            $acc = User::where(['phone_number' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        } else {
            $acc = User::where(['email' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        }

        if ($acc == null) {
            return $this->error('Account not found.');
        }

        $code = $r->code;
        if ($code != $acc->intro) {
            return $this->error('Invalid OTP.');
        }
        $password = trim($r->password);
        if (strlen($password) < 4) {
            return $this->error('Password must be at least 4 characters.');
        }
        $acc->password = password_hash($password, PASSWORD_DEFAULT);
        $msg = '';
        try {
            $acc->save();
            $msg = 'Password reset successful. You can now use your new password to login.';
        } catch (Exception $e) {
            return $this->error('Failed to save password because ' . $e->getMessage() . '');
        }

        return $this->success(
            $acc,
            $message = $msg,
            200
        );
    }




    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Username is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $r->username = trim($r->username);

        $u = User::where('phone_number', $r->username)->first();
        if ($u == null) {
            $u = User::where('phone_number', $r->username)
                ->first();
        }
        if ($u == null) {
            $u = User::where('email', $r->username)->first();
        }


        if ($u == null) {

            $phone_number = Utils::prepare_phone_number($r->username);


            if (Utils::phone_number_is_valid($phone_number)) {

                $u = User::where('phone_number', $phone_number)->first();

                if ($u == null) {
                    $u = User::where('username', $phone_number)
                        ->first();
                }
            }
        }


        if ($u == null) {
            return $this->error('User account not found (' . $phone_number . '.)');
        }


        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }



        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }



    public function update_user(Request $request)
    {
        $admin = auth('api')->user();
        if ($admin == null) {
            return $this->error('User not found.');
        }

        $loggedIn = User::find($admin->id);
        if ($loggedIn == null) {
            return $this->error('User not found.');
        }
        $sacco = Sacco::find($loggedIn->sacco_id);

        if ($sacco == null) {
            return $this->error('Sacco not found.');
        }

        if (!isset($request->task)) {
            return $this->error('Task is missing.');
        }

        $task = $request->task;

        if (($task != 'Edit') && ($task != 'Create')) {
            return $this->error('Invalid task.');
        }

        $phone_number = Utils::prepare_phone_number($request->phone_number);
        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number.');
        }

        $account = null;
        if ($task == 'Edit') {
            if ($request->id == null) {
                return $this->error('User id is missing.');
            }
            $acc = User::find($request->id);
            if ($acc == null) {
                return $this->error('User not found.');
            }
            $old = User::where('phone_number', $phone_number)
                ->where('id', '!=', $request->id)
                ->first();
            if ($old != null) {
                return $this->error('User with same phone number already exists. ' . $old->id . ' ' . $old->phone_number . ' ' . $old->first_name . ' ' . $old->last_name);
            }
        } else {

            $old = User::where('phone_number', $phone_number)
                ->first();
            if ($old != null) {
                return $this->error('User with same phone number already exists.');
            }

            $acc = new Administrator();
            $acc->sacco_id = $sacco->id;
        }

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }
        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }

        //validate all
        if (
            $request->sex == null ||
            strlen($request->sex) < 2
        ) {
            return $this->error('Gender is missing.');
        }

        if (
            $request->campus_id == null ||
            strlen($request->campus_id) < 2
        ) {
            return $this->error('National ID is missing.');
        }
        if (
            $request->email != null &&
            strlen($request->email) > 2
        ) {
            $acc->email = $request->email;
        }


        $msg = "";
        $acc->first_name = $request->first_name;
        $acc->last_name = $request->last_name;
        $acc->campus_id = $request->campus_id;
        $acc->phone_number = $phone_number;
        $acc->sex = $request->sex;
        $acc->dob = $request->dob;
        $acc->address = $request->address;
        $acc->sacco_join_status = 'Approved';

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $acc->avatar = 'images/' . $images[0];
        }

        $code = 1;
        try {
            $acc->save();
            $msg = 'Account ' . $task . 'ed successfully.';
            return $this->success($acc, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }




    public function password_change(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user == null) {
            return $this->error('User not found.');
        }

        //logged in user
        $admin = auth('api')->user();
        if ($admin == null) {
            return $this->error('User not found.');
        }

        if ($request->password == null) {
            return $this->error('Password is required.');
        }

        if (strtolower($admin->user_type) != 'admin') {
            if (!password_verify($request->current_password, $user->password)) {
                return $this->error('Current password is incorrect.');
            }
        }

        $user->password = password_hash(trim($request->password), PASSWORD_DEFAULT);
        $user->save();
        $msg = 'Password changed successfully.';
        return $this->success(null, $msg);
    }




    public function register(Request $r)
    {
        if ($r->has_sacco != 'Yes') {
            return $this->error('Download latest app from google playstore to proceed.');
        }

        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }


        $r->validate([
            'phone_number' => 'required',
        ]);


        $phone_number = $r->phone_number;
        $isEmail = false;

        //check if $phone_number is email address
        if (filter_var($phone_number, FILTER_VALIDATE_EMAIL)) {
            $isEmail = true;
        }

        if (!$isEmail) {
            $phone_number = Utils::prepare_phone_number($r->phone_number);
            if (!Utils::phone_number_is_valid($phone_number)) {
                return $this->error('Invalid phone number.');
            }
            $acc = User::where(['phone_number' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        } else {
            $acc = User::where(['email' => $phone_number])->first();
            if ($acc == null) {
                $acc = User::where(['username' => $phone_number])->first();
            }
        }

        if ($acc != null) {
            // $acc->delete();
            return $this->error('Account with same phone number or email already exist.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }


        $u = User::where('phone_number', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }

        $u = User::where('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists. (username)');
        }

        $u = User::where('email', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists (email).');
        }

        $u = User::where('reg_number', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists (reg_number).');
        }


        $user = new Administrator();

        $name = $r->name;

        if (isset($r->name) && strlen($r->name) > 4) {
            $x = explode(' ', $name);
            if (
                isset($x[0]) &&
                isset($x[1])
            ) {
                $user->first_name = $x[0];
                $user->last_name = $x[1];
            } else {
                $user->first_name = $name;
            }
        }

        if (isset($r->first_name) && strlen($r->first_name) > 3) {
            $user->first_name = $r->first_name;
            $user->last_name = $r->last_name;
            $user->name = $user->first_name . ' ' . $r->last_name;
        }




        $user->phone_number = $phone_number;
        $user->username = $phone_number;
        $user->email = $phone_number;
        $user->reg_number = $phone_number;
        $user->country = $phone_number;
        $user->occupation = $phone_number;
        $user->profile_photo_large = '';
        $user->location_lat = '';
        $user->location_long = '';
        $user->facebook = '';
        $user->twitter = '';
        $user->linkedin = '';
        $user->website = '';
        $user->other_link = '';
        $user->cv = '';
        $user->language = '';
        $user->about = '';
        $user->address = '';
        $user->name = $name;
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        try {
            $user->save();
        } catch (\Throwable $th) {
            return $this->error('Failed because ' . $th->getMessage() . '');
        }


        $new_user = User::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created but not found.');
        }
        $sacc = new Sacco();
        $sacc->administrator_id = $new_user->id;
        $sacc->name = $new_user->sacco_name;
        $sacc->phone_number = $new_user->phone_number;
        $sacc->email_address = $new_user->email;
        $sacc->about = $new_user->about;


        try {
            $sacc->save();
            $new_user->sacco_id = 1;
            $new_user->save();
        } catch (\Throwable $th) {
            $new_user->delete();
            return $this->error('Account created but failed to create sacco account because ' . $th->getMessage() . '');
        }


        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        $new_user->remember_token = $token;
        return $this->success($new_user, 'Account created successfully.');
    }
}
