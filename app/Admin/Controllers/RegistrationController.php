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

        $grid->column('id', __('Id'));
        $grid->column('user_id', __('User id'));
        $grid->column('category', __('Category'));
        $grid->column('level_of_education', __('Level of education'));
        $grid->column('marital_status', __('Marital status'));
        $grid->column('number_of_dependants', __('Number of dependants'));
        $grid->column('farmers group', __('Farmers group'));
        $grid->column('farming_experience', __('Farming experience'));
        $grid->column('production_scale', __('Production scale'));
        $grid->column('company_information', __('Company information'));
        $grid->column('owner_name', __('Owner name'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('registration_number', __('Registration number'));
        $grid->column('registration_date', __('Registration date'));
        $grid->column('physical_address', __('Physical address'));
        $grid->column('certificate_and_compliance', __('Certificate and compliance'));
        $grid->column('service_provider_name', __('Service provider name'));
        $grid->column('email_address', __('Email address'));
        $grid->column('postal_address', __('Postal address'));
        $grid->column('services_offered', __('Services offered'));
        $grid->column('district_sub_county', __('District sub county'));
        $grid->column('logo', __('Logo'));
        $grid->column('certificate_of_incorporation', __('Certificate of incorporation'));
        $grid->column('license', __('License'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('category', __('Category'));
        $show->field('level_of_education', __('Level of education'));
        $show->field('marital_status', __('Marital status'));
        $show->field('number_of_dependants', __('Number of dependants'));
        $show->field('farmers group', __('Farmers group'));
        $show->field('farming_experience', __('Farming experience'));
        $show->field('production_scale', __('Production scale'));
        $show->field('company_information', __('Company information'));
        $show->field('owner_name', __('Owner name'));
        $show->field('phone_number', __('Phone number'));
        $show->field('registration_number', __('Registration number'));
        $show->field('registration_date', __('Registration date'));
        $show->field('physical_address', __('Physical address'));
        $show->field('certificate_and_compliance', __('Certificate and compliance'));
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

        $form->number('user_id', __('User id'));
        $form->text('category', __('Category'));
        $form->text('level_of_education', __('Level of education'));
        $form->text('marital_status', __('Marital status'));
        $form->text('number_of_dependants', __('Number of dependants'));
        $form->text('farmers group', __('Farmers group'));
        $form->text('farming_experience', __('Farming experience'));
        $form->text('production_scale', __('Production scale'));
        $form->text('company_information', __('Company information'));
        $form->text('owner_name', __('Owner name'));
        $form->text('phone_number', __('Phone number'));
        $form->text('registration_number', __('Registration number'));
        $form->text('registration_date', __('Registration date'));
        $form->text('physical_address', __('Physical address'));
        $form->text('certificate_and_compliance', __('Certificate and compliance'));
        $form->text('service_provider_name', __('Service provider name'));
        $form->text('email_address', __('Email address'));
        $form->text('postal_address', __('Postal address'));
        $form->text('services_offered', __('Services offered'));
        $form->text('district_sub_county', __('District sub county'));
        $form->text('logo', __('Logo'));
        $form->text('certificate_of_incorporation', __('Certificate of incorporation'));
        $form->text('license', __('License'));

        return $form;
    }
}
