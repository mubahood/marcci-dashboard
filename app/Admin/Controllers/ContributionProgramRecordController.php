<?php

namespace App\Admin\Controllers;

use App\Models\ContributionProgramRecord;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContributionProgramRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Contribution Program Record';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ContributionProgramRecord());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('sacco_id', __('Sacco id'));
        $grid->column('member_id', __('Member id'));
        $grid->column('teasurer_id', __('Teasurer id'));
        $grid->column('year', __('Year'));
        $grid->column('week_number', __('Week number'));
        $grid->column('month_number', __('Month number'));
        $grid->column('contribution_program_id', __('Contribution program id'));
        $grid->column('amount', __('Amount'));
        $grid->column('is_paid', __('Is paid'));
        $grid->column('month_name', __('Month name'));
        $grid->column('type', __('Type'));
        $grid->column('description', __('Description'));
        $grid->column('details', __('Details'));
        $grid->column('payment_date', __('Payment date'));
        $grid->column('period_range_start', __('Period range start'));
        $grid->column('period_range_end', __('Period range end'));

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
        $show = new Show(ContributionProgramRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('member_id', __('Member id'));
        $show->field('teasurer_id', __('Teasurer id'));
        $show->field('year', __('Year'));
        $show->field('week_number', __('Week number'));
        $show->field('month_number', __('Month number'));
        $show->field('contribution_program_id', __('Contribution program id'));
        $show->field('amount', __('Amount'));
        $show->field('is_paid', __('Is paid'));
        $show->field('month_name', __('Month name'));
        $show->field('type', __('Type'));
        $show->field('description', __('Description'));
        $show->field('details', __('Details'));
        $show->field('payment_date', __('Payment date'));
        $show->field('period_range_start', __('Period range start'));
        $show->field('period_range_end', __('Period range end'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ContributionProgramRecord());

        $form->number('sacco_id', __('Sacco id'));
        $form->number('member_id', __('Member id'));
        $form->number('teasurer_id', __('Teasurer id'));
        $form->number('year', __('Year'));
        $form->number('week_number', __('Week number'));
        $form->number('month_number', __('Month number'));
        $form->number('contribution_program_id', __('Contribution program id'));
        $form->number('amount', __('Amount'));
        $form->text('is_paid', __('Is paid'))->default('No');
        $form->text('month_name', __('Month name'));
        $form->text('type', __('Type'));
        $form->textarea('description', __('Description'));
        $form->textarea('details', __('Details'));
        $form->date('payment_date', __('Payment date'))->default(date('Y-m-d'));
        $form->date('period_range_start', __('Period range start'))->default(date('Y-m-d'));
        $form->date('period_range_end', __('Period range end'))->default(date('Y-m-d'));

        return $form;
    }
}
