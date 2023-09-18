<?php

namespace App\Admin\Controllers;

use App\Models\Registration;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\User;

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
          //filter by name
       $grid->filter(function ($filter) 
       {
        // Remove the default id filter
        $filter->disableIdFilter();
        $filter->like('user_id', 'Applicant')->select(\App\Models\User::pluck('name', 'id'));
       
       });

       //remove the export and grid button
        $grid->disableExport();
        $grid->disableColumnSelector();

        $grid->model()->orderBy('created_at', 'desc');
       
      
         //show the user only his records
        if (auth('admin')->user()->isRole('basic-user')) 
        {
            $grid->model()->where('user_id', auth('admin')->user()->id);
            $registration= Registration::where('user_id', auth('admin')->user()->id)->first();

            //if registration exits disable create button
            if($registration){
                $grid->disableCreateButton();
            }

            //disable delete and show action button
            $grid->actions(function ($actions) {
                if($actions->row->status == 1 || 
                $actions->row->status == 2)
                {
                    $actions->disableDelete();
                    $actions->disableEdit();
                }
            });
     
            $grid->column('user_id', __('Applicant'))->display(function ($user_id) {
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
        }
        else
        {
            //disable creation of new records
            $grid->disableCreateButton();

            //disable delete and show action button
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
            });

            $grid->column('user_id', __('Applicant'))->display(function ($user_id) {
                return User::find($user_id)->name;
            });

            $grid->column('category', __('Category'));
            //$grid->column('status', __('Status'))->editable('select', [0 => 'Pending', 1 => 'Approved', 2 => 'Rejected']);
            $grid->column('status', __('Status'))->display(function ($status) {
                if($status == 0){
                    return "<span class='label label-warning'>Pending</span>";
                }
                elseif($status == 1){
                    return "<span class='label label-success'>Approved</span>";
                }
                elseif($status == 2){
                    return "<span class='label label-danger'>Rejected</span>";
                }
            });

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

        if ($form->isEditing()) 
        {
           
        }

        if(!$user->isRole('basic-user'))
        {
            //get form id
            $form_id =  request()->route()->parameter('registration');
            $registration = Registration::find($form_id);

             if ($registration->category == 'farmer'){
                $form->display('level_of_education', __('Level of education'));
                $form->display('marital_status', __('Marital status'));
                $form->display('number_of_dependants', __('Number of dependants'));
                $form->display('farmers group', __('Farmers group'));
                $form->display('farming_experience', __('Farming experience'));
                $form->display('production_scale', __('Production scale'));
                $form->divider();
                $form->radioButton('status', __('Status'))->options([
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected'
                ])->default(0);
             }
             if($registration->category == 'seed producer'){
                $form->display('company_information', __('Company information'));
                $form->display('owner_name', __('Owner name'));
                $form->display('phone_number', __('Phone number'));
                $form->display('registration_number', __('Registration number'));
                $form->display('registration_date', __('Registration date'));
                $form->display('physical_address', __('Physical address'));
                $form->display('certificate_and_compliance', __('Certificate and compliance'));
                $form->divider();
                $form->radioButton('status', __('Status'))->options([
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected'
                ])->default(0);
             }
             if($registration->category == 'service provider'){
                $form->display('service_provider_name', __('Service provider name'));
                $form->display('email_address', __('Email address'));
                $form->display('postal_address', __('Postal address'));
                $form->display('services_offered', __('Services offered'));
                $form->display('district_sub_county', __('District sub county'));
                $form->display('logo', __('Logo'));
                $form->display('certificate_of_incorporation', __('Certificate of incorporation'));
                $form->display('license', __('License'));
                $form->divider();
                $form->radioButton('status', __('Status'))->options([
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected'
                ])->default(0);

             }
          
        }
        else{
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
            }
              
                $form->disableEditingCheck();
                $form->disableCreatingCheck();
                $form->disableViewCheck();

                //disable delete button
                $form->tools(function (Form\Tools $tools) {
                    $tools->disableView();
                    $tools->disableDelete();
                });

        return $form;
    }
}
