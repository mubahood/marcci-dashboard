<?php

namespace App\Admin\Controllers;

use App\Models\Download;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DownloadController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Downloads';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Download());
        $grid->disableCreateButton();
        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Regisetered'))->display(
            function ($x) {
                return Utils::my_date($x);
            }
        )->sortable();
        $grid->column('administrator_id', __('Agent'))
            ->display(
                function ($x) {
                    $u = Administrator::find($x);
                    if ($u == null) {
                        return Utils::my_date($x);
                    }
                    return $u->name;
                }
            )->sortable();
        $grid->column('district', __('District'));
        $grid->column('region', __('Region'));
        $grid->column('type_of_promoter', __('Type of promoter'));
        $grid->column('login', __('Login'));
        $grid->column('team_leader', __('Team leader'));
        $grid->column('client_phone_number', __('Client phone number'));
        $grid->column('client_activation_momo_code', __('Client activation momo code'));
        $grid->column('client_neighborhood', __('Client neighborhood'));
        $grid->column('other', __('Verified'))
            ->dot([
                'Verified' => 'success'
            ], 'danger');

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
        $show = new Show(Download::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('district', __('District'));
        $show->field('region', __('Region'));
        $show->field('type_of_promoter', __('Type of promoter'));
        $show->field('login', __('Login'));
        $show->field('team_leader', __('Team leader'));
        $show->field('client_phone_number', __('Client phone number'));
        $show->field('client_activation_momo_code', __('Client activation momo code'));
        $show->field('client_neighborhood', __('Client neighborhood'));
        $show->field('other', __('Other'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Download());


        $form->display('district', __('District'));
        $form->display('region', __('Region'));
        $form->display('client_phone_number', __('Client phone number'));
        $form->display('client_activation_momo_code', __('Client activation momo code'));
        $form->display('client_neighborhood', __('Client neighborhood'));
        $form->radio('other', __('Verified'))
            ->options([
                'Verified' => 'Yes',
                'Pending for verification' => 'No',
            ]);

        return $form;
    }
}
