<?php

namespace App\Admin\Controllers;

use App\Models\Farmer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FarmerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Farmer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Farmer());

        $grid->column('id', __('Id'));
        $grid->column('organisation_id', __('Organisation id'));
        $grid->column('farmer_group_id', __('Farmer group id'));
        $grid->column('first_name', __('First name'));
        $grid->column('last_name', __('Last name'));
        $grid->column('country_id', __('Country id'));
        $grid->column('language_id', __('Language id'));
        $grid->column('national_id_number', __('National id number'));
        $grid->column('gender', __('Gender'));
        $grid->column('education_level', __('Education level'));
        $grid->column('year_of_birth', __('Year of birth'));
        $grid->column('phone', __('Phone'));
        $grid->column('email', __('Email'));
        $grid->column('is_your_phone', __('Is your phone'));
        $grid->column('is_mm_registered', __('Is mm registered'));
        $grid->column('other_economic_activity', __('Other economic activity'));
        $grid->column('location_id', __('Location id'));
        $grid->column('address', __('Address'));
        $grid->column('latitude', __('Latitude'));
        $grid->column('longitude', __('Longitude'));
        $grid->column('password', __('Password'));
        $grid->column('farming_scale', __('Farming scale'));
        $grid->column('land_holding_in_acres', __('Land holding in acres'));
        $grid->column('land_under_farming_in_acres', __('Land under farming in acres'));
        $grid->column('ever_bought_insurance', __('Ever bought insurance'));
        $grid->column('ever_received_credit', __('Ever received credit'));
        $grid->column('status', __('Status'));
        $grid->column('created_by_user_id', __('Created by user id'));
        $grid->column('created_by_agent_id', __('Created by agent id'));
        $grid->column('agent_id', __('Agent id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('poverty_level', __('Poverty level'));
        $grid->column('food_security_level', __('Food security level'));
        $grid->column('marital_status', __('Marital status'));
        $grid->column('family_size', __('Family size'));
        $grid->column('farm_decision_role', __('Farm decision role'));
        $grid->column('is_pwd', __('Is pwd'));
        $grid->column('is_refugee', __('Is refugee'));
        $grid->column('date_of_birth', __('Date of birth'));
        $grid->column('age_group', __('Age group'));
        $grid->column('language_preference', __('Language preference'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('phone_type', __('Phone type'));
        $grid->column('preferred_info_type', __('Preferred info type'));
        $grid->column('home_gps_latitude', __('Home gps latitude'));
        $grid->column('home_gps_longitude', __('Home gps longitude'));
        $grid->column('village', __('Village'));
        $grid->column('street', __('Street'));
        $grid->column('house_number', __('House number'));
        $grid->column('land_registration_numbers', __('Land registration numbers'));
        $grid->column('labor_force', __('Labor force'));
        $grid->column('equipment_owned', __('Equipment owned'));
        $grid->column('livestock', __('Livestock'));
        $grid->column('crops_grown', __('Crops grown'));
        $grid->column('has_bank_account', __('Has bank account'));
        $grid->column('has_mobile_money_account', __('Has mobile money account'));
        $grid->column('payments_or_transfers', __('Payments or transfers'));
        $grid->column('financial_service_provider', __('Financial service provider'));
        $grid->column('has_credit', __('Has credit'));
        $grid->column('loan_size', __('Loan size'));
        $grid->column('loan_usage', __('Loan usage'));
        $grid->column('farm_business_plan', __('Farm business plan'));
        $grid->column('covered_risks', __('Covered risks'));
        $grid->column('insurance_company_name', __('Insurance company name'));
        $grid->column('insurance_cost', __('Insurance cost'));
        $grid->column('repaid_amount', __('Repaid amount'));
        $grid->column('photo', __('Photo'));
        $grid->column('district_id', __('District id'));
        $grid->column('subcounty_id', __('Subcounty id'));
        $grid->column('parish_id', __('Parish id'));
        $grid->column('bank_id', __('Bank id'));
        $grid->column('other_livestock_count', __('Other livestock count'));
        $grid->column('poultry_count', __('Poultry count'));
        $grid->column('sheep_count', __('Sheep count'));
        $grid->column('goat_count', __('Goat count'));
        $grid->column('cattle_count', __('Cattle count'));
        $grid->column('bank_account_number', __('Bank account number'));
        $grid->column('has_receive_loan', __('Has receive loan'));

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
        $show = new Show(Farmer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('organisation_id', __('Organisation id'));
        $show->field('farmer_group_id', __('Farmer group id'));
        $show->field('first_name', __('First name'));
        $show->field('last_name', __('Last name'));
        $show->field('country_id', __('Country id'));
        $show->field('language_id', __('Language id'));
        $show->field('national_id_number', __('National id number'));
        $show->field('gender', __('Gender'));
        $show->field('education_level', __('Education level'));
        $show->field('year_of_birth', __('Year of birth'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('is_your_phone', __('Is your phone'));
        $show->field('is_mm_registered', __('Is mm registered'));
        $show->field('other_economic_activity', __('Other economic activity'));
        $show->field('location_id', __('Location id'));
        $show->field('address', __('Address'));
        $show->field('latitude', __('Latitude'));
        $show->field('longitude', __('Longitude'));
        $show->field('password', __('Password'));
        $show->field('farming_scale', __('Farming scale'));
        $show->field('land_holding_in_acres', __('Land holding in acres'));
        $show->field('land_under_farming_in_acres', __('Land under farming in acres'));
        $show->field('ever_bought_insurance', __('Ever bought insurance'));
        $show->field('ever_received_credit', __('Ever received credit'));
        $show->field('status', __('Status'));
        $show->field('created_by_user_id', __('Created by user id'));
        $show->field('created_by_agent_id', __('Created by agent id'));
        $show->field('agent_id', __('Agent id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('poverty_level', __('Poverty level'));
        $show->field('food_security_level', __('Food security level'));
        $show->field('marital_status', __('Marital status'));
        $show->field('family_size', __('Family size'));
        $show->field('farm_decision_role', __('Farm decision role'));
        $show->field('is_pwd', __('Is pwd'));
        $show->field('is_refugee', __('Is refugee'));
        $show->field('date_of_birth', __('Date of birth'));
        $show->field('age_group', __('Age group'));
        $show->field('language_preference', __('Language preference'));
        $show->field('phone_number', __('Phone number'));
        $show->field('phone_type', __('Phone type'));
        $show->field('preferred_info_type', __('Preferred info type'));
        $show->field('home_gps_latitude', __('Home gps latitude'));
        $show->field('home_gps_longitude', __('Home gps longitude'));
        $show->field('village', __('Village'));
        $show->field('street', __('Street'));
        $show->field('house_number', __('House number'));
        $show->field('land_registration_numbers', __('Land registration numbers'));
        $show->field('labor_force', __('Labor force'));
        $show->field('equipment_owned', __('Equipment owned'));
        $show->field('livestock', __('Livestock'));
        $show->field('crops_grown', __('Crops grown'));
        $show->field('has_bank_account', __('Has bank account'));
        $show->field('has_mobile_money_account', __('Has mobile money account'));
        $show->field('payments_or_transfers', __('Payments or transfers'));
        $show->field('financial_service_provider', __('Financial service provider'));
        $show->field('has_credit', __('Has credit'));
        $show->field('loan_size', __('Loan size'));
        $show->field('loan_usage', __('Loan usage'));
        $show->field('farm_business_plan', __('Farm business plan'));
        $show->field('covered_risks', __('Covered risks'));
        $show->field('insurance_company_name', __('Insurance company name'));
        $show->field('insurance_cost', __('Insurance cost'));
        $show->field('repaid_amount', __('Repaid amount'));
        $show->field('photo', __('Photo'));
        $show->field('district_id', __('District id'));
        $show->field('subcounty_id', __('Subcounty id'));
        $show->field('parish_id', __('Parish id'));
        $show->field('bank_id', __('Bank id'));
        $show->field('other_livestock_count', __('Other livestock count'));
        $show->field('poultry_count', __('Poultry count'));
        $show->field('sheep_count', __('Sheep count'));
        $show->field('goat_count', __('Goat count'));
        $show->field('cattle_count', __('Cattle count'));
        $show->field('bank_account_number', __('Bank account number'));
        $show->field('has_receive_loan', __('Has receive loan'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Farmer());

        $form->text('organisation_id', __('Organisation id'));
        $form->text('farmer_group_id', __('Farmer group id'));
        $form->text('first_name', __('First name'));
        $form->text('last_name', __('Last name'));
        $form->text('country_id', __('Country id'));
        $form->text('language_id', __('Language id'));
        $form->text('national_id_number', __('National id number'));
        $form->text('gender', __('Gender'));
        $form->text('education_level', __('Education level'));
        $form->text('year_of_birth', __('Year of birth'));
        $form->mobile('phone', __('Phone'));
        $form->email('email', __('Email'));
        $form->textarea('is_your_phone', __('Is your phone'));
        $form->textarea('is_mm_registered', __('Is mm registered'));
        $form->textarea('other_economic_activity', __('Other economic activity'));
        $form->text('location_id', __('Location id'));
        $form->text('address', __('Address'));
        $form->text('latitude', __('Latitude'));
        $form->text('longitude', __('Longitude'));
        $form->password('password', __('Password'));
        $form->text('farming_scale', __('Farming scale'));
        $form->decimal('land_holding_in_acres', __('Land holding in acres'));
        $form->decimal('land_under_farming_in_acres', __('Land under farming in acres'));
        $form->textarea('ever_bought_insurance', __('Ever bought insurance'));
        $form->text('ever_received_credit', __('Ever received credit'));
        $form->text('status', __('Status'));
        $form->text('created_by_user_id', __('Created by user id'));
        $form->text('created_by_agent_id', __('Created by agent id'));
        $form->text('agent_id', __('Agent id'));
        $form->text('poverty_level', __('Poverty level'));
        $form->text('food_security_level', __('Food security level'));
        $form->text('marital_status', __('Marital status'));
        $form->number('family_size', __('Family size'));
        $form->text('farm_decision_role', __('Farm decision role'));
        $form->textarea('is_pwd', __('Is pwd'));
        $form->textarea('is_refugee', __('Is refugee'));
        $form->date('date_of_birth', __('Date of birth'))->default(date('Y-m-d'));
        $form->text('age_group', __('Age group'));
        $form->text('language_preference', __('Language preference'));
        $form->text('phone_number', __('Phone number'));
        $form->text('phone_type', __('Phone type'));
        $form->text('preferred_info_type', __('Preferred info type'));
        $form->decimal('home_gps_latitude', __('Home gps latitude'));
        $form->decimal('home_gps_longitude', __('Home gps longitude'));
        $form->text('village', __('Village'));
        $form->text('street', __('Street'));
        $form->text('house_number', __('House number'));
        $form->text('land_registration_numbers', __('Land registration numbers'));
        $form->text('labor_force', __('Labor force'));
        $form->text('equipment_owned', __('Equipment owned'));
        $form->text('livestock', __('Livestock'));
        $form->text('crops_grown', __('Crops grown'));
        $form->textarea('has_bank_account', __('Has bank account'));
        $form->textarea('has_mobile_money_account', __('Has mobile money account'));
        $form->text('payments_or_transfers', __('Payments or transfers'));
        $form->text('financial_service_provider', __('Financial service provider'));
        $form->textarea('has_credit', __('Has credit'));
        $form->number('loan_size', __('Loan size'));
        $form->text('loan_usage', __('Loan usage'));
        $form->textarea('farm_business_plan', __('Farm business plan'));
        $form->text('covered_risks', __('Covered risks'));
        $form->text('insurance_company_name', __('Insurance company name'));
        $form->number('insurance_cost', __('Insurance cost'));
        $form->number('repaid_amount', __('Repaid amount'));
        $form->textarea('photo', __('Photo'));
        $form->number('district_id', __('District id'));
        $form->number('subcounty_id', __('Subcounty id'));
        $form->number('parish_id', __('Parish id'));
        $form->number('bank_id', __('Bank id'));
        $form->number('other_livestock_count', __('Other livestock count'));
        $form->number('poultry_count', __('Poultry count'));
        $form->number('sheep_count', __('Sheep count'));
        $form->number('goat_count', __('Goat count'));
        $form->number('cattle_count', __('Cattle count'));
        $form->textarea('bank_account_number', __('Bank account number'));
        $form->text('has_receive_loan', __('Has receive loan'))->default('No');

        return $form;
    }
}
