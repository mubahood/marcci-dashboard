<?php

namespace App\Admin\Controllers;

use App\Models\Cycle;
use App\Models\Sacco;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CycleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cycles';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cycle());
        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableExport();
        $grid->disableFilter();
        //disbale delete
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
        });
        //order by name
        $grid->model()->orderBy('name', 'asc');
        $grid->column('name', __('Name'))->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('start_date', __('Start Date'))
            ->display(function ($date) {
                return date('d M, Y', strtotime($date));
            })->sortable();
        $grid->column('end_date', __('End date'))
            ->display(function ($date) {
                return date('d M, Y', strtotime($date));
            })->sortable();
        $grid->column('status', __('Status'))
            ->label([
                'Active' => 'success',
                'Inactive' => 'warning',
            ])
            ->sortable();

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return date('d M, Y', strtotime($date));
            })->sortable();
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
        $show = new Show(Cycle::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('status', __('Status'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('created_by_id', __('Created by id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Cycle());

        $u = Admin::user();
        if (!$u->isRole('admin')) {
            $form->hidden('sacco_id')->value($u->sacco_id);
        } else {
            $form->select('sacco_id', __('Sacco'))->options(Sacco::all()->pluck('name', 'id'))->rules('required');
        }

        $form->text('name', __('Name'))->rules('required');
        $form->date('start_date', __('Start date'))->default(date('Y-m-d'))
            ->rules('required');
        $form->date('end_date', __('End date'))->default(date('Y-m-d'))
            ->rules('required');
        //date range for start and end date
        $form->saving(function (Form $form) {
            if ($form->start_date > $form->end_date) {
                admin_error('Start date cannot be greater than end date');
                return back();
            }
        });

        $form->radio('status', __('Status'))
            ->options(['Active' => 'Active', 'Inactive' => 'Inactive'])
            ->default('Inactive');
        $form->textarea('description', __('Description'));

        //created_by_id hidden field
        $form->hidden('created_by_id')->value(Admin::user()->id);

        return $form;
    }
}
