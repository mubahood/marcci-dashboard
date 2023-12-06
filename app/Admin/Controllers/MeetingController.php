<?php

namespace App\Admin\Controllers;

use App\Models\Meeting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class MeetingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Meetings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Meeting());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created'))
        ->sortable();
        $grid->column('name', __('Name'))
        ->editable()
        ->sortable();
        $grid->column('date', __('Date'));
        $grid->column('location', __('Location'));
        $grid->column('sacco_id', __('Sacco id'));
        $grid->column('administrator_id', __('Administrator id'));
        $grid->column('members', __('Members'));
        $grid->column('minutes', __('Minutes'));
        $grid->column('attendance', __('Attendance'));

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
        $show = new Show(Meeting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('date', __('Date'));
        $show->field('location', __('Location'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('members', __('Members'));
        $show->field('minutes', __('Minutes'));
        $show->field('attendance', __('Attendance'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        $u = Auth::user();


        $form = new Form(new Meeting());

        $sacco_id = $u->sacco_id;
        //hidden for sacco_id
        $form->hidden('sacco_id')->default($sacco_id);
        $form->hidden('administrator_id')->default($u->id);

        $form->text('name', __('Name'))
            ->rules('required');
        $form->date('date', __('Date'))->default(date('Y-m-d'))
            ->rules('required');
        $form->text('location', __('Location'))
            ->rules('required');

        $users = \App\Models\User::where('sacco_id', $sacco_id)->get();
        $form->multipleSelect('members', __('Members'))
            ->options($users->pluck('name', 'id'))
            ->rules('required');

        $form->quill('minutes', __('Minutes'));
        $form->textarea('attendance', __('Attendance'));

        return $form;
    }
}
