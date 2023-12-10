<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sacco;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
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
    public function __construct()
    {

        /* $token = auth('api')->attempt([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        die($token); */
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


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

        $loggedIn = Administrator::find($admin->id);
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
            $acc = Administrator::find($request->id);
            if ($acc == null) {
                return $this->error('User not found.');
            }
            $old = Administrator::where('phone_number', $phone_number)
                ->where('id', '!=', $request->id)
                ->first();
            if ($old != null) {
                return $this->error('User with same phone number already exists. ' . $old->id . ' ' . $old->phone_number . ' ' . $old->first_name . ' ' . $old->last_name);
            }
        } else {

            $old = Administrator::where('phone_number', $phone_number)
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




    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));


        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        if ($r->name == null) {
            return $this->error('Name is required.');
        }





        $u = Administrator::where('phone_number', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }

        $u = Administrator::where('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists. (username)');
        }

        $u = Administrator::where('email', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists (email).');
        }

        $u = Administrator::where('reg_number', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists (reg_number).');
        }

        $user = new Administrator();

        $name = $r->name;

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

        $user->phone_number = $phone_number;
        $user->username = $phone_number;
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
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
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
