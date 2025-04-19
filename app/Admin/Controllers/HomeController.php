<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\ContributionProgram;
use App\Models\Crop;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Location;
use App\Models\Person;
use App\Models\Sacco;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {


        return $content;
        /* 
        

total_expected
total_collected
total_balance

amount_per_member_value
name


 
 
 
 
 
 
 
 
 
 


        */
        $u = Admin::user();
        $sacco_id = $u->sacco_id;
        $sacco = Sacco::find($sacco_id);
        $sacco_name = $sacco->name;

        $faker = Faker::create();
        $max = 500;
        $fathers = User::where('sex', 'Male')->get()->pluck('id')->toArray();
        $mothers = User::where('sex', 'Female')->get()->pluck('id')->toArray();
        $roots = User::where('website', 'Root')->get()->pluck('id')->toArray();

        shuffle($mothers);
        shuffle($fathers);
        shuffle($roots);

        //set execution time to unlimited
        set_time_limit(0);

        //set memory limit to unlimited
        ini_set('memory_limit', '-1');

        foreach (User::all() as $key => $user) {
            if (in_array($user->id, $roots)) {
                continue;
            }

            shuffle($mothers);
            shuffle($fathers);

            $mother = User::find($mothers[array_rand($mothers)]);
            $father = User::find($fathers[array_rand($fathers)]);

            if ($user->website == 'Father') {
                $user->linkedin = $father->id;
                $user->website = 'Father';
            } else {
                $user->linkedin = $mother->id;
                $user->website = 'Mother';
            }
            $user->sacco_id = 1;
            $user->language = ['Compulsory', 'Compulsory', 'Compulsory', 'Compulsory', 'Optional', 'Optional', 'Compulsory', 'Compulsory', 'Compulsory', 'Compulsory', 'Compulsory', 'Compulsory'][rand(0, 11)];


            $user->save();
            echo $user->id . ". " . $user->name . "<br>";
        }

        dd($roots);


        for ($i = 0; $i < $max; $i++) {
            break;
            $m = new User();
            $m->username = $faker->phoneNumber;
            $m->first_name = $faker->firstName;
            $m->last_name = $faker->lastName;
            $m->email = $faker->email;
            $m->reg_date = Carbon::now()->timestamp;
            $m->last_seen = Carbon::now()->timestamp;
            $m->approved = 1;
            $m->profile_photo = $faker->imageUrl(640, 480, 'people');
            $m->profile_photo_large = $faker->imageUrl(640, 480, 'people');
            $m->phone_number = $m->username;
            $m->user_type = 'Member';
            $m->sex = ['Male', 'Female'][rand(0, 1)];
            $m->reg_number = ['Alive', 'Late', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive', 'Alive'][rand(0, 9)];
            $m->country = ['Uganda', 'Kenya', 'Tanzania', 'Rwanda', 'Burundi'][rand(0, 4)];
            $m->occupation = $faker->jobTitle;
            $m->location_lat = $faker->latitude;
            $m->location_long = $faker->longitude;
            $m->facebook = $faker->url;
            $m->twitter = $faker->url;
            $m->whatsapp = $faker->phoneNumber;
            $m->website = ['Father', 'Father', 'Mother', 'Father', 'Father', 'Father'][rand(0, 4)];

            if ($m->website == 'Father') {
                $fathers = User::where('sacco_id', $sacco->id)->where('sex', 'Male')->get()->toArray();
                //shuffle($fathers);
                $father = $fathers[array_rand($fathers)];
                $m->linkedin = $father['id'];
            } else {
                $mothers = User::where('sacco_id', $sacco->id)->where('sex', 'Female')->get();
                //shuffle($mothers);
                $mother = $mothers->random();
                $m->linkedin = $mother->id;
            }

            $m->other_link = $faker->url;
            $m->cv = $faker->imageUrl(640, 480, 'people');
            $m->language = $faker->languageCode;
            $m->about = $faker->text;
            $m->address = $faker->address;
            $m->dob = Carbon::now()->subYears(rand(18, 50))->format('Y-m-d');
            $m->intro = $faker->text;
            $m->sacco_id = $sacco->id;
            $m->sacco_join_status = 'Approved';
            $m->status = 'Active';
            $m->balance = 0;
            $m->remember_token = $faker->uuid;
            $m->avatar = $faker->imageUrl(640, 480, 'people');
            $m->title = ['Mr', 'Mrs', 'Ms'][rand(0, 2)];
            $m->id_front = $faker->imageUrl(640, 480, 'people');
            $m->id_back = $faker->imageUrl(640, 480, 'people');
            $m->created_at = Carbon::now()->timestamp;
            $m->updated_at = Carbon::now()->timestamp;
            $m->password = bcrypt($m->username);
            $m->save();
            echo $m->id . " " . $m->name . "<br>";
        }


        return $content;
        /*      
    
    "linkedin" => "1"
    "website" => "https://muni.ac.ug/RIF/mobi-save"
    "other_link" => "Primary"
    "cv" => "files/Chairman's welcome rev. 1.pdf"
    "language" => null
    "about" => "<p>Muhindo Mubarak is currently a commuter <strong>science</strong> and engineering <strong>student </strong>at Islamic universe of Technology - Daka</p>"
    "address" => "Bwera, Kasese, Uganda"
    "created_at" => "2020-07-30 18:56:55"
    "updated_at" => "2025-04-12 12:39:52"
    "remember_token" => "ck0Xfh1WhVQEMiXnGTayjGIJsEVSmFMYqc5DG51Z19DBTaLqWoQLBNyS2Ggs"
    "avatar" => "images/muni logo.PNG"
    "name" => "Muni University"
    "campus_id" => "1676688"
    "complete_profile" => "1"
    "title" => "Mr"
    "dob" => "1989-12-13"
    "intro" => "a"
    "sacco_id" => "1"
    "sacco_join_status" => "Pending"
    "id_front" => ""
    "id_back" => ""
    "status" => "Active"
    "balance" => "1"
        */

        /*  
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Administrator::class, 'user_id');
            $table->foreignIdFor(Administrator::class, 'source_user_id');
            $table->foreignIdFor(Sacco::class, 'sacco_id');
            $table->string('type')->default('Deposit');
            $table->string('source_type')->default('Mobile Money');
            $table->string('source_mobile_money_number')->nullable();
            $table->string('source_mobile_money_transaction_id')->nullable();
            $table->string('source_bank_account_number')->nullable();
            $table->string('source_bank_transaction_id')->nullable();
            $table->string('desination_type')->default('Mobile Money');
            $table->string('desination_mobile_money_number')->nullable();
            $table->string('desination_mobile_money_transaction_id')->nullable();
            $table->string('desination_bank_account_number')->nullable();
            $table->string('desination_bank_transaction_id')->nullable();
            $table->string('amount');
            $table->text('description')->nullable();
            $table->text('details')->nullable();
        }); */

        $u = Auth::user();
        $content
            ->title('MobiSave - Dashboard')
            ->description('Hello ' . $u->name . "!");
        $u = Admin::user();


        $content->row(function (Row $row) {
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Registered Farmers',
                    'sub_title' => 'Joined 30 days ago.',
                    'number' => number_format(User::count()),
                    'link' => 'javascript:;'
                ]));
            });
            // $row->column(3, function (Column $column) {
            //     $column->append(view('widgets.box-5', [
            //         'is_dark' => false,
            //         'title' => 'Registered Gardens',
            //         'sub_title' => 'All time.',
            //         'number' => number_format(Garden::count()),
            //         'link' => 'javascript:;'
            //     ]));
            // });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Garden Activities',
                    'sub_title' => 'From System',
                    'number' => number_format(GardenActivity::count()),
                    'link' => 'javascript:;'
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Production Guides',
                    'sub_title' => 'From system',
                    'number' => number_format(Crop::count()),
                    'link' => 'javascript:;'
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Weather',
                    'sub_title' => 'Weather API',
                    'number' => 20 . '&#176;C',
                    'link' => 'javascript:;'
                ]));
            });
        });
        $content->row(function (Row $row) {


            $row->column(6, function (Column $column) {
                $sorghum_count = Garden::where('crop_id', 2)->count();
                $cow_peas = Garden::where('crop_id', 1)->count();

                $column->append(view('widgets.by-categories', compact('sorghum_count', 'cow_peas')));
            });
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.faqs', []));
            });
        });


        return $content;

        // $content->row(function (Row $row) {
        //     $row->column(6, function (Column $column) {
        //         $column->append(view('widgets.by-categories', []));
        //     });
        //     $row->column(6, function (Column $column) {
        //         $column->append(view('widgets.calender', []));
        //     });
        // });



        // $content->row(function (Row $row) {
        //     $row->column(6, function (Column $column) {
        //         $column->append(Dashboard::dashboard_members());
        //     });
        //     $row->column(3, function (Column $column) {
        //         $column->append(Dashboard::dashboard_events());
        //     });
        //     $row->column(3, function (Column $column) {
        //         $column->append(Dashboard::dashboard_news());
        //     });
        // });




        // return $content;
        // return $content
        //     ->title('Dashboard')
        //     ->description('Description...')
        //     ->row(Dashboard::title())
        //     ->row(function (Row $row) {

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::environment());
        //         });

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::extensions());
        //         });

        //         $row->column(4, function (Column $column) {
        //             $column->append(Dashboard::dependencies());
        //         });
        //     });
    }
}
