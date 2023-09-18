<?php

namespace App\Admin\Controllers;

use App\Models\Registration;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RegistrationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Registration';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Registration());
        $registration= Registration::where('user_id', auth('admin')->user()->id)->first();
        //check if registration is null
        if($registration == null){
            //return empty table
            return $grid;
        }

         //show the user only his records
        if (auth('admin')->user()->isRole('basic-user')) {
            $grid->model()->where('user_id', auth('admin')->user()->id);
        }

     
        $grid->column('user_id', __('User id'))->display(function ($user_id) {
            return User::find($user_id)->name;
        });

        $grid->column('category', __('Category'));

        if($registration->category == 'farmer'){
        $grid->column('farmers group', __('Farmers group'));
        $grid->column('farming_experience', __('Farming experience'));
        $grid->column('production_scale', __('Production scale'));
        }

        if($registration->category == 'seed producer'){
        $grid->column('owner_name', __('Owner name'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('registration_number', __('Registration number'));
        $grid->column('registration_date', __('Registration date'));
        $grid->column('physical_address', __('Physical address'));
        }

        if($registration->category == 'service provider'){
        $grid->column('service_provider_name', __('Service provider name'));
        $grid->column('email_address', __('Email address'));
        $grid->column('postal_address', __('Postal address'));
        $grid->column('services_offered', __('Services offered'));
        
        }

     

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Registration::findOrFail($id));
        $registration = Registration::find($id);

        $show->field('user_id', __('User id'))->as(function ($user_id) {
            return User::find($user_id)->name;
        });
        $show->field('category', __('Category'));
        
        if($registration->category == 'farmer'){
        $show->field('level_of_education', __('Level of education'));
        $show->field('marital_status', __('Marital status'));
        $show->field('number_of_dependants', __('Number of dependants'));
        $show->field('farmers group', __('Farmers group'));
        $show->field('farming_experience', __('Farming experience'));
        $show->field('production_scale', __('Production scale'));
        }

        if($registration->category == 'seed producer'){
        $show->field('company_information', __('Company information'));
        $show->field('owner_name', __('Owner name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('registration_number', __('Registration number'));
        $show->field('registration_date', __('Registration date'));
        $show->field('physical_address', __('Physical address'));
        $show->field('certificate_and_compliance', __('Certificate and compliance'));
        }

        if($registration->category == 'service provider'){
        $show->field('service_provider_name', __('Service provider name'));
        $show->field('email_address', __('Email address'));
        $show->field('postal_address', __('Postal address'));
        $show->field('services_offered', __('Services offered'));
        $show->field('district_sub_county', __('District sub county'));
        $show->field('logo', __('Logo'));
        $show->field('certificate_of_incorporation', __('Certificate of incorporation'));
        $show->field('license', __('License'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Registration());
        $user = auth()->user();

        //When form is creating, assign user id
        if ($form->isCreating()) 
        {
            $form->hidden('user_id')->default($user->id);

        }


        $form->display('user_id', __('Applicant'))->default($user->name);
        $form->radioCard('category', __('Category'))->options([
                'farmer' => 'Farmer',
                'seed producer' => 'Seed Producer',
                'service provider' => 'Service Provider'
                ])
                ->when('farmer', function(Form $form){
                       
                    $form->text('level_of_education', __('Level of education'));
                    $form->text('marital_status', __('Marital status'));
                    $form->text('number_of_dependants', __('Number of dependants'));
                    $form->text('farmers group', __('Farmers group'));
                    $form->text('farming_experience', __('Farming experience'));
                    $form->text('production_scale', __('Production scale'));

                })
                ->when('seed producer', function(Form $form){
                    $form->text('company_information', __('Company information'));
                    $form->text('owner_name', __('Owner name'));
                    $form->text('phone_number', __('Phone number'));
                    $form->text('registration_number', __('Registration number'));
                    $form->text('registration_date', __('Registration date'));
                    $form->text('physical_address', __('Physical address'));
                    $form->text('certificate_and_compliance', __('Certificate and compliance'));

                })
                ->when('service provider', function(Form $form){
                    $form->text('service_provider_name', __('Service provider name'));
                    $form->text('email_address', __('Email address'));
                    $form->text('postal_address', __('Postal address'));
                    $form->text('services_offered', __('Services offered'));
                    $form->text('district_sub_county', __('District sub county'));
                    $form->text('logo', __('Logo'));
                    $form->text('certificate_of_incorporation', __('Certificate of incorporation'));
                    $form->text('license', __('License'));
                })->required();

        return $form;
    }
}
