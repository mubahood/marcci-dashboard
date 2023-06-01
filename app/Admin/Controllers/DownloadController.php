<?php

namespace App\Admin\Controllers;

use App\Models\Download;
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
    protected $title = 'Download';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Download());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('district', __('District'));
        $grid->column('region', __('Region'));
        $grid->column('type_of_promoter', __('Type of promoter'));
        $grid->column('login', __('Login'));
        $grid->column('team_leader', __('Team leader'));
        $grid->column('client_phone_number', __('Client phone number'));
        $grid->column('client_activation_momo_code', __('Client activation momo code'));
        $grid->column('client_neighborhood', __('Client neighborhood'));
        $grid->column('other', __('Other'));

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

        $form->number('administrator_id', __('Administrator id'));
        $form->textarea('district', __('District'));
        $form->textarea('region', __('Region'));
        $form->textarea('type_of_promoter', __('Type of promoter'));
        $form->textarea('login', __('Login'));
        $form->textarea('team_leader', __('Team leader'));
        $form->textarea('client_phone_number', __('Client phone number'));
        $form->textarea('client_activation_momo_code', __('Client activation momo code'));
        $form->textarea('client_neighborhood', __('Client neighborhood'));
        $form->textarea('other', __('Other'));

        return $form;
    }
}
