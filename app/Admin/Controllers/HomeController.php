<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PestAndDiseaseController;
use App\Models\Association;
use App\Models\Crop;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\Group;
use App\Models\Location;
use App\Models\Person;
use App\Models\Question;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\QuestionController;
use Encore\Admin\Layout\Row;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Auth;
use SplFileObject;

class HomeController extends Controller
{
    public function questions(Content $content)
    {

        $u = Auth::user();
        $content
            ->title('Farmers Forum');
        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(QuestionController::get_questions());
            });
            
        });
        return $content;
    }

    public function answers(Content $content, $id)
    {
        $content
            ->title('Answers');
        $content->row(function (Row $row) use ($id) {
            $row->column(12, function (Column $column) use ($id) {
                $column->append(QuestionController::question_answers($id));
            });
        });
        return $content;
    }

    public function pestsAndDiseases(Content $content)
    {

        $u = Auth::user();
        $content
            ->title('Ask the expert');
        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(PestAndDiseaseController::index());
            });
            
        });
        return $content;
    }


    public function index(Content $content)
    {
        $u = Auth::user();
        $content
            ->title('NaRO - Dashboard')
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
                $sorghum_count = Garden::where('variety_id', 2)->count();
                $cow_peas = Garden::where('variety_id', 1)->count();

                $column->append(view('widgets.by-categories', compact('sorghum_count', 'cow_peas')));
            });
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.faqs', []));
            });
        });

        $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.groundnut-market', []));
            });
            $row->column(6, function (Column $column) {
                $column->append(view('widgets.products-services', []));
            });
           
        });

        $content->row(function (Row $row) {
            $row->column(12, function (Column $column) {
                $column->append(view('widgets.weather', []));
            });
          
           
        });


        return $content;

      
    }
}
