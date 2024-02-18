<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\CounsellingCentre;
use App\Models\Crop;
use App\Models\CropProtocol;
use App\Models\Event;
use App\Models\FinancialRecord;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Institution;
use App\Models\Job;
use App\Models\NewsPost;
use App\Models\Parish;
use App\Models\Person;
use App\Models\PestsAndDisease;
use App\Models\PestsAndDiseaseReport;
use App\Models\Product;
use App\Models\ServiceProvider;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class ApiResurceController extends Controller
{

    use ApiResponser;

    public function crops(Request $r)
    {
        $items = [];

        foreach (Crop::all() as $key => $crop) {
            $items[] = $crop;
        }

        return $this->success(
            $items,
            $message = "Sussesfully",
            200
        );
    }

    public function my_list(Request $r, $model)
    {

        header('Content-Type: application/json');
        http_response_code(200);
        $model = "App\Models\\" . $model;
        $data = $model::where([])->get();
        echo (json_encode([
            'code' => 1,
            'message' => 'success',
            'data' => $data
        ]));
        die();
    }



    public function garden_activities(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = GardenActivity::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = GardenActivity::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }

    public function parishes()
    {
        $items = [];
        foreach (Parish::all() as $key => $parish) {
            $name = $parish->name;
            if ($parish->subcounty != null) {
                $name = $parish->subcounty->name . ", " . $name;
            }
            if ($parish->district != null) {
                $name = $parish->district->name . ", " . $name;
            }
            $items[] = [
                'id' => $parish->id,
                'name' => $name,
            ];
        }
        return $this->success(
            $items,
            $message = "Sussesfully",
        );
    }


    public function gardens(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        $gardens = [];
        if ($u->isRole('agent')) {
            $gardens = Garden::where([])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $gardens = Garden::where(['user_id' => $u->id])
                ->limit(1000)
                ->orderBy('id', 'desc')
                ->get();
        }

        return $this->success(
            $gardens,
            $message = "Sussesfully",
            200
        );
    }

    public function pests_and_disease_reports(Request $r)
    {
        return $this->success(
            PestsAndDiseaseReport::where([])
                ->limit(10000)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Sussesfully",
            200
        );
    }



    public function people(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Person::where(['administrator_id' => $u->id])
                ->limit(100)
                ->orderBy('id', 'desc')
                ->get(),
            $message = "Sussesfully",
            200
        );
    }
    public function jobs(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }

        return $this->success(
            Job::where([])
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get(),
            $message = "Sussesfully",
        );
    }


    public function activity_submit(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->activity_id == null ||
            $r->farmer_activity_status == null ||
            $r->farmer_comment == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        //status
        if (
            $r->farmer_activity_status == null
        ) {
            return $this->error('Status is missing');
        }


        $activity = GardenActivity::find($r->activity_id);

        if ($activity == null) {
            $graden = Garden::find($r->garden_id);
            if ($graden == null) {
                return $this->error('Garden not found.');
            }
            $activity = new GardenActivity();
            $activity->garden_id = $r->garden_id;
            $activity->user_id = $u->id;
            $activity->crop_activity_id = 1;
            $activity->activity_name = $r->activity_name;
        }

        if (isset($r->activity_date_to_be_done)) {
            if (strlen($r->activity_date_to_be_done) > 2) {
                $activity->activity_date_to_be_done = Carbon::parse($r->activity_date_to_be_done);
                $activity->activity_due_date = Carbon::parse($r->activity_date_to_be_done);
            }
        }


        $image = "";
        if (!empty($_FILES)) {
            if (!empty($_FILES)) {
                try {
                    //$image = Utils::upload_images_2($_FILES, true);
                    if ($r->file('file') != null) {
                        $image = Utils::file_upload($r->file('file'));
                    }
                } catch (Throwable $t) {
                    return $this->error('Failed to upload image, becase ' . $t->getMessage() . '');
                }
            }
        }

        $activity->photo = $image;
        $activity->farmer_activity_status = $r->farmer_activity_status;
        $activity->farmer_comment = $r->farmer_comment;

        if ($r->farmer_activity_status == 'Done') {
            if ($r->activity_date_done == null || strlen($r->activity_date_done) < 2) {
                return $this->error('Activity date is missing. => ' . $r->activity_date_done);
            }
            $activity->activity_date_done = Carbon::parse($r->activity_date_done);
            $activity->farmer_submission_date = Carbon::now();
            $activity->farmer_has_submitted = 'Yes';
        } else if ($r->farmer_activity_status == 'Pending') {
            if ($r->activity_date_to_be_done == null || strlen($r->activity_date_to_be_done) < 2) {
                return $this->error('Activity date is missing.');
            }
            $activity->activity_date_to_be_done = Carbon::parse($r->activity_date_to_be_done);
        } else if ($r->farmer_activity_status == 'Skipped') {
            $activity->farmer_submission_date = Carbon::now();
            $activity->activity_date_done = Carbon::now();
            $activity->farmer_has_submitted = 'Yes';
        } else {
            return $this->error('Status is not valid. => ' . $r->farmer_activity_status . '');
        }



        try {
            $activity->save();
            return $this->success(null, $message = "Sussesfully created!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save activity, becase ' . $th->getMessage() . '');
        }
    }

    public function garden_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null 
        ) {
            return $this->error('Name is missing');
        }
        //planting_date == null ||
        if(!isset($r->planting_date)){
            return $this->error('Planting date is missing');
        }

        if (
            $r->parish_id == null
        ) {
            return $this->error('Parish is missing');
        }

        if (
            $r->crop_id == null
        ) {
            return $this->error('Crop is missing');
        }


        $image = "";
        if (!empty($_FILES)) {
            try {
                //$image = Utils::upload_images_2($_FILES, true);
                if ($r->file('file') != null) {
                    $image = Utils::file_upload($r->file('file'));
                }
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        if (!isset($r->task)) {
            return $this->error('Task is missing');
        }

        $isCreate = false;
        if ($r->task == 'create') {
            $obj = new Garden();
            $isCreate = true;
        } else {
            $obj = Garden::find($r->id);
            if ($obj == null) {
                return $this->error('Garden not found');
            }
            $isCreate = false;
        }
        $parish = Parish::find($r->parish_id);
        if ($parish == null) {
            return $this->error('Parish not found');
        }

        $obj->name = $r->name;
        $obj->user_id = $u->id;
        $obj->status = $r->status;
        $obj->production_scale = $r->production_scale;
        $obj->planting_date = Carbon::parse($r->planting_date);
        $obj->land_occupied = $r->land_occupied;
        $obj->crop_id = $r->crop_id;
        $obj->details = $r->details;

        $obj->parish_id = $r->parish_id;
        $obj->district_id = $parish->district_id;
        $obj->subcounty_id = $parish->subcounty_id;
        $obj->gps_lati = $r->gps_lati;
        $obj->gps_longi = $r->gps_longi;

        if (!$isCreate) {
            if ($image != 'no_image.jpg') {
                $obj->photo = $image;
            }
        } else {
            $obj->photo = $image;
        }

        $obj->save();

        $msg = "Garden Updated Sussesfully!";
        if ($isCreate) {
            $msg = "Garden Created Sussesfully!";
        }

        return $this->success(null, $msg, 200);
    }


    public function financial_records_cerate(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->garden_id == null
        ) {
            return $this->error('Garden not found');
        }
        if (
            $r->amount == null
        ) {
            return $this->error('Amount is missing');
        }
        //category
        if (
            $r->category == null
        ) {
            return $this->error('Category is missing');
        }
        //date
        if (
            $r->date == null
        ) {
            return $this->error('Date is missing');
        }

        $garden = Garden::find($r->garden_id);
        if ($garden == null) {
            return $this->error('Garden not found');
        }
        $obj = new FinancialRecord();
        $obj->garden_id = $r->garden_id;
        $obj->user_id = $u->id;

        $category = trim($r->category);
        if ($category == 'Income') {
            $obj->amount = abs($r->amount);
        } else if ($category == 'Expense') {
            $obj->amount = -1 * abs($r->amount);
        } else {
            return $this->error('Category is not valid');
        }
        $obj->category = $category;
        $obj->description = $r->description;
        $obj->payment_method = "Cash";

        $date = Carbon::parse($r->date);
        if ($date == null) {
            return $this->error('Date is not valid');
        }
        $obj->date = $date;
        $obj->quantity = 1;
        $image = "";
        if (!empty($_FILES)) {
            try {
                //$image = Utils::upload_images_2($_FILES, true);
                if ($r->file('file') != null) {
                    $image = Utils::file_upload($r->file('file'));
                }
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }

        $garden->income = FinancialRecord::where(['garden_id' => $r->garden_id, 'category' => 'Income'])->sum('amount');
        $garden->expense = FinancialRecord::where(['garden_id' => $r->garden_id, 'category' => 'Expense'])->sum('amount');
        $garden->profit = $garden->income + $garden->expense;
        try {
            $garden->save();
        } catch (\Throwable $t) {
            return $this->error('Failed to save garden, because ' . $t->getMessage() . '');
        }



        $obj->recipient = $image;
        try {
            $obj->save();
        } catch (\Throwable $t) {
            return $this->error('Failed to save report, becase ' . $t->getMessage() . '');
        }
        $obj = FinancialRecord::find($obj->id);
        return $this->success($obj, 'Success', 1);
    }



    public function pests_report(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->garden_id == null ||
            $r->pests_and_disease_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            try {
                //$image = Utils::upload_images_2($_FILES, true);
                if ($r->file('file') != null) {
                    $image = Utils::file_upload($r->file('file'));
                }
            } catch (Throwable $t) {
                $image = "no_image.jpg";
            }
        }
        $garden = Garden::find($r->garden_id);
        if ($garden == null) {
            return $this->error('Garden not found');
        }
        $pest = PestsAndDisease::find($r->pests_and_disease_id);
        if ($pest == null) {
            return $this->error('Pest not found');
        }

        $crop = Crop::find($garden->crop_id);
        if ($crop == null) {
            return $this->error('Crop not found');
        }

        $obj = new PestsAndDiseaseReport();

        $obj->pests_and_disease_id = $r->pests_and_disease_id;
        $obj->garden_id = $r->garden_id;
        $obj->crop_id = $garden->crop_id;
        $obj->user_id = $u->id;
        $obj->district_id = $garden->district_id;
        $obj->subcounty_id = $garden->subcounty_id;
        $obj->parish_id = $garden->parish_id;
        $obj->photo = $image;
        $obj->gps_lati = $garden->gps_lati;
        $obj->gps_longi = $garden->gps_longi;
        $obj->photo = $image;
        $msg = "Report Created Sussesfully!";
        try {
            $obj->save();
        } catch (\Throwable $t) {
            return $this->error('Failed to save report, becase ' . $t->getMessage() . '');
        }

        return $this->success(null, $msg, 200);
    }



    public function product_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->category == null ||
            $r->price == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            if (!empty($_FILES)) {
                try {
                    //$image = Utils::upload_images_2($_FILES, true);
                    if ($r->file('file') != null) {
                        $image = Utils::file_upload($r->file('file'));
                    }
                } catch (Throwable $t) {
                    return $this->error('Failed to upload image, becase ' . $t->getMessage() . '');
                }
            }
        }




        $obj = new Product();
        $obj->name = $r->name;
        $obj->administrator_id = $u->id;
        $obj->type = $r->category;
        $obj->details = $r->details;
        $obj->price = $r->price;
        $obj->offer_type = $r->offer_type;
        $obj->state = $r->state;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = 1;
        $obj->photo = $image;

        try {
            $obj->save();
            return $this->success(null, $message = "Product Uploaded Sussesfully!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save product, becase ' . $th->getMessage() . '');
            //throw $th;
        }
    }

    public function person_create(Request $r)
    {
        $u = $r->user;
        if ($u == null) {
            return $this->error('User not found.');
        }
        if (
            $r->name == null ||
            $r->sex == null ||
            $r->subcounty_id == null
        ) {
            return $this->error('Some Information is still missing. Fill the missing information and try again.');
        }

        $image = "";
        if (!empty($_FILES)) {
            if (!empty($_FILES)) {
                try {
                    //$image = Utils::upload_images_2($_FILES, true);
                    if ($r->file('file') != null) {
                        $image = Utils::file_upload($r->file('file'));
                    }
                } catch (Throwable $t) {
                    return $this->error('Failed to upload image, becase ' . $t->getMessage() . '');
                }
            }
        }

        $obj = new Person();
        $obj->id = $r->id;
        $obj->created_at = $r->created_at;
        $obj->association_id = $r->association_id;
        $obj->administrator_id = $u->id;
        $obj->group_id = $r->group_id;
        $obj->name = $r->name;
        $obj->address = $r->address;
        $obj->parish = $r->parish;
        $obj->village = $r->village;
        $obj->phone_number = $r->phone_number;
        $obj->email = $r->email;
        $obj->district_id = $r->district_id;
        $obj->subcounty_id = $r->subcounty_id;
        $obj->disability_id = $r->disability_id;
        $obj->phone_number_2 = $r->phone_number_2;
        $obj->dob = $r->dob;
        $obj->sex = $r->sex;
        $obj->education_level = $r->education_level;
        $obj->employment_status = $r->employment_status;
        $obj->has_caregiver = $r->has_caregiver;
        $obj->caregiver_name = $r->caregiver_name;
        $obj->caregiver_sex = $r->caregiver_sex;
        $obj->caregiver_phone_number = $r->caregiver_phone_number;
        $obj->caregiver_age = $r->caregiver_age;
        $obj->caregiver_relationship = $r->caregiver_relationship;
        $obj->photo = $image;
        $obj->save();


        return $this->success(null, $message = "Sussesfully registered!", 200);
    }

    public function groups()
    {
        return $this->success(Group::get_groups(), 'Success');
    }


    public function associations()
    {
        return $this->success(Association::where([])->orderby('id', 'desc')->get(), 'Success');
    }

    public function institutions()
    {
        return $this->success(Institution::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function service_providers()
    {
        return $this->success(ServiceProvider::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function counselling_centres()
    {
        return $this->success(CounsellingCentre::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function products()
    {
        return $this->success(Product::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function events()
    {
        return $this->success(Event::where([])->orderby('id', 'desc')->get(), 'Success');
    }
    public function news_posts()
    {
        return $this->success(NewsPost::where([])->orderby('id', 'desc')->get(), 'Success');
    }


    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }

        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {

            $u = $r->user;
            $administrator_id = $u->id;

            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }





    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item not found.",
            ]);
        }


        unset($_POST['_method']);
        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        foreach ($_POST as $key => $value) {
            $obj->$key = $value;
        }


        $success = false;
        $msg = "";
        try {
            $obj->save();
            $msg = "Updated successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }
}
