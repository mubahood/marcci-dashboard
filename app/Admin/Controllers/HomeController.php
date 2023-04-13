<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Garden;
use App\Models\Group;
use App\Models\Location;
use App\Models\Person;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use SplFileObject;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $u = Auth::user();
        $content
            ->title('MaRCCI - Dashboard')
            ->description('Hello ' . $u->name . "!");

        return $content;

        /* $g = Garden::find(1);
        Garden::generate_protocols($g);
        
        die("as"); */
        /*  
        Utils::importPwdsProfiles(Utils::docs_root().'/people.csv');
        die();
          
  
        foreach (Administrator::all() as $key => $as) {
            $as->avatar = 'images/u-'.rand(1,10).'.png';
            $as->save();
        } */



        $faker = Faker::create();
        $name = [
            'Gulu Women with Disabilities Union (GUWODU)',
            'Kijura Disabled Women Association (KIDWA)',
            'SignHealth Uganda (SU)',
            'Spinal Injuries Association Uganda (SIA-U)',
            'The National Association of the Deafblind in Uganda',
            'Jinja District Association of the Blind (JDAB)',
            'Busia District Union of People with Disabilities (BUDIPD)',
            'Kabale Association of People with Disabilities (KAPD)',
            'National Union of Disabled Persons of Uganda (NUDIPU)',
            'The Organisation for Emancipation of the Disabled',
            'United Deaf Women Organisation (UDEWO)',
            'Uganda Albinos Association',
            'Action for Youth with Disabilities Uganda (AYDU)',
            'Action on Disability& Development (ADD) International',
            'Masaka Disabled People Living with HIV/AIDS Association',
            'Uganda Parents with Deaf-Blind Association',
            'Comprehensive Rehabilitation Services in Uganda (CORSU)',
            'Uganda Persons with Disabilities Development Advocacy',
            'Youth and Persons with Disability (s) Integrated Development',
            'Uganda Landmine Survivors Association (ULSA)',
            'Sense International Uganda',
            'Masindi District People with Disability Union (MADIPHU)',
            'Save Children with Disabilities',
            'Youth with Physical Disability Development Forum',
            'Katalemwa Cheshire Home for Rehabilitation Services',
        ];
        $address = [
            'P.O Box 249, Gulu,Pawel Road, Opposite SOS children, Gulu',
            'P.O Box 36563, Kampala,Plot 99, Ntinda-Nakawa Road, Kampala, Kampala, Uganda',
            'P.O Box 1611 Wandegeya,Metal Health Uganda Office , Kampala',
            'P.O Box 379 Jinja ,JDAB offices, Mufubria subconty-Kumuli Road, Jinja',
            'P.O Box 124 Busia,District headquarters (District union office), Busia',
            'P.O Box 774 Kabale,District Headquarters near Education Department, Kabale',
        ];
        $subs = [];
        foreach (Location::get_sub_counties_array() as $key => $value) {
            $subs[] =  $key;
        }
        /*  
        foreach ($name as $key => $value) {
            $as = new Association();
            shuffle($subs);
            shuffle($subs);
            shuffle($address);
            shuffle($address);
            shuffle($address);
            $as->administrator_id = 1;
            $as->name = $value;
            $as->members = rand(50,1000);
            $as->parish = 'Nyamambuka II';
            $as->status = 'Approved';
            $as->village = 'Bwera';
            $as->vision = 'Simple vision of this association';
            $as->mission = 'Simple mission of this association';
            $as->phone_number = '+256706638494';
            $as->phone_number_2 = '+256793204665';
            $as->email = 'test-maiil@gmail.com';
            $as->website = 'http://www.test-ste.com';
            $as->address = $address[2];
            $as->subcounty_id = $subs[15];
            $as->gps_latitude = '0.36532221688073396';
            $as->gps_longitude = '32.606444250275224';
            $as->photo = 'images/l-'.rand(1,10).'.png';
            $as->about = 'P.O Box 249, Gulu,Pawel Road, Opposite SOS children, Gulu The organization was founded by a group of disabled women. Initially, the group was called Makmatic. It was established to prevent discrimination, violence or abuse of women and girls with disabilities and empower them economically, socially and politically to have a dignified life. Vision Women and girls with disabilities able to unite, organize, manage and empowered to affirm their human rights and freedoms in a dignified mannerObjectives';

            $as->save();  
        } */

        $ass = [];

        $groups = [];
        foreach (Group::all() as $key => $ass) {
            $groups[] = $ass->id;
        }

        /*  

        foreach (Association::all() as $key => $ass) {

            $max = rand(2,10);
            for ($i = 1; $i < $max; $i++) {
                shuffle($address);
                shuffle($subs);
                shuffle($address);
                $c = new Group();
                $c->name = 'Group '.$i;
                $c->leader = 'M. Muhindo';
                $c->association_id = $ass->id;
                $c->address = $address[2];
                $c->parish = 'Test parish';
                $c->village = 'Test parish';
                $c->phone_number = '+256706638494';
                $c->phone_number_2 = '+256793204665';
                $c->email = 'muhindo@gmail.com';
                $c->subcounty_id = $subs[15]; 
                $c->members = rand(100,1000); 
                $c->started = Carbon::now(); 
                $c->save(); 
            }
        }

 
 

 


 


deleted_at
	 
*/

        /*         for ($i = 1; $i < 100; $i++) {
            shuffle($groups);
            shuffle($groups);
            shuffle($groups);
            shuffle($address);
            shuffle($subs);
            shuffle($subs);
            shuffle($subs);
            $c = new Person();
            $c->administrator_id = 1;
            $c->address = $address[2];
            $c->created_at = $faker->dateTimeBetween('-2 month', '+1 month');
            $c->dob = $faker->dateTimeBetween('-30 year', '-10 year');
            $c->group_id = $groups[2];
            $c->name = $faker->name();
            $c->caregiver_name = $faker->name();
            $c->parish = 'Kawanda';
            $c->village = 'Kansangati';
            $c->village = 'Kansangati';
            $c->phone_number = '+256706638494';
            $c->caregiver_phone_number = '+256706638494';
            $c->phone_number_2 = '+256793204665';
            $c->email = 'muhindo@gmail.com';
            $c->education_level = [
                'None',
                'Below primary',
                'Primary',
                'Secondary',
                'A-Level',
                'Bachelor',
                'Masters',
                'PhD',
            ][rand(0, 7)];
            $c->caregiver_relationship = [
                'Friend',
                'Brother',
                'Mother',
                'Father',
                'Sister',
                'Cousin',
                'Uncle',
                'Other',
            ][rand(0, 7)];
            $c->subcounty_id = $subs[15];
            $c->sex = ['Male', 'Female'][rand(0, 1)];
            $c->caregiver_sex = ['Male', 'Female'][rand(0, 1)];
            $c->has_caregiver = ['Yes', 'No'][rand(0, 1)];
            $c->caregiver_age = rand(10,50);
            $c->employment_status = ['Employed', 'Not Employed'][rand(0, 1)];
            $c->photo = 'images/u-'.rand(1,16).'.png';
            $c->save(); 
        }
 */

        $u = Admin::user();


        $content->row(function (Row $row) {
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'New members',
                    'sub_title' => 'Joined 30 days ago.',
                    'number' => number_format(rand(100, 600)),
                    'link' => 'javascript:;'
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Products & Services',
                    'sub_title' => 'All time.',
                    'number' => number_format(rand(1000, 6000)),
                    'link' => 'javascript:;'
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => false,
                    'title' => 'Job oppotunities',
                    'sub_title' => rand(100, 400) . ' jobs posted 7 days ago.',
                    'number' => number_format(rand(1000, 6000)),
                    'link' => 'javascript:;'
                ]));
            });
            $row->column(3, function (Column $column) {
                $column->append(view('widgets.box-5', [
                    'is_dark' => true,
                    'title' => 'System traffic',
                    'sub_title' => rand(100, 400) . ' mobile app, ' . rand(100, 300) . ' web browser.',
                    'number' => number_format(rand(100, 6000)),
                    'link' => 'javascript:;'
                ]));
            });
        });




        $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.by-categories', []));
            });
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.by-districts', []));
            });
        });



        $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $column->append(Dashboard::dashboard_members());
            });
            $row->column(3, function (Column $column) {
                $column->append(Dashboard::dashboard_events());
            });
            $row->column(3, function (Column $column) {
                $column->append(Dashboard::dashboard_news());
            });
        });




        return $content;
        return $content
            ->title('Dashboard')
            ->description('Description...')
            ->row(Dashboard::title())
            ->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::environment());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::extensions());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dependencies());
                });
            });
    }
}
