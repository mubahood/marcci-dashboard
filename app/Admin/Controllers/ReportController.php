<?php

namespace App\Admin\Controllers;

use App\Models\Cycle;
use App\Models\Report;
use App\Models\Sacco;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Report());
        /* $r = Report::find(1);
        $r->is_processed = rand(100000,100000000);
        $r->status = 'Pending';
        $r->save();
        die(); */


        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('created_by_user_id', __('Created by user id'));
        $grid->column('sacco_id', __('Sacco id'));
        $grid->column('cycle_id', __('Cycle id'));
        $grid->column('title', __('Title'));
        $grid->column('status', __('Status'));
        $grid->column('report_type', __('Report type'));
        $grid->column('period_type', __('Period type'));
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));
        $grid->column('balance', __('Balance'));
        $grid->column('TOTAL_SAVING', __('TOTAL_SAVING'));
        $grid->column('SHARE_COUNT', __('SHARE COUNT'));
        $grid->column('SHARE', __('SHARE'));
        $grid->column('LOAN_BALANCE', __('LOAN BALANCE'));
        $grid->column('LOAN_TOTAL_AMOUNT', __('LOAN TOTAL AMOUNT'));
        $grid->column('LOAN_COUNT', __('LOAN COUNT'));
        $grid->column('LOAN_INTEREST', __('LOAN INTEREST'));
        $grid->column('LOAN_REPAYMENT', __('LOAN REPAYMENT'));
        $grid->column('FEE', __('FEE'));
        $grid->column('WITHDRAWAL', __('WITHDRAWAL'));
        $grid->column('CYCLE_PROFIT', __('CYCLE PROFIT'));
        $grid->column('FINE', __('FINE'));
        $grid->column('transactions_data', __('Transactions data'));
        $grid->column('loan_transactions_data', __('Loan transactions data'));
        $grid->column('users_data', __('Users data'));
        $grid->column('loans_data', __('Loans data'));
        $grid->column('is_processed', __('Is processed'));

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
        $show = new Show(Report::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_by_user_id', __('Created by user id'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('cycle_id', __('Cycle id'));
        $show->field('title', __('Title'));
        $show->field('status', __('Status'));
        $show->field('report_type', __('Report type'));
        $show->field('period_type', __('Period type'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));
        $show->field('balance', __('Balance'));
        $show->field('TOTAL_SAVING', __('TOTAL_SAVING'));
        $show->field('SHARE_COUNT', __('SHARE COUNT'));
        $show->field('SHARE', __('SHARE'));
        $show->field('LOAN_BALANCE', __('LOAN BALANCE'));
        $show->field('LOAN_TOTAL_AMOUNT', __('LOAN TOTAL AMOUNT'));
        $show->field('LOAN_COUNT', __('LOAN COUNT'));
        $show->field('LOAN_INTEREST', __('LOAN INTEREST'));
        $show->field('LOAN_REPAYMENT', __('LOAN REPAYMENT'));
        $show->field('FEE', __('FEE'));
        $show->field('WITHDRAWAL', __('WITHDRAWAL'));
        $show->field('CYCLE_PROFIT', __('CYCLE PROFIT'));
        $show->field('FINE', __('FINE'));
        $show->field('transactions_data', __('Transactions data'));
        $show->field('loan_transactions_data', __('Loan transactions data'));
        $show->field('users_data', __('Users data'));
        $show->field('loans_data', __('Loans data'));
        $show->field('is_processed', __('Is processed'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Report());
        $u = \Encore\Admin\Facades\Admin::user();

        $form->hidden('created_by_user_id', __('Created by user id'))->default($u->id);
        $form->hidden('sacco_id', __('Sacco id'))->default($u->sacco_id);

        //creating
        if (!$form->isCreating()) {
            $form->display('title', __('Title'))->default('Financial Report');
        }

        $form->radio('report_type', __('Report Type'))
            ->options([
                'General' => 'General Report',
                'Member' => "Member's Report",
            ])->rules('required')
            ->when('Member', function ($form) {
                $u = Admin::user();
                $form->select('user_id', __('Select Member'))
                    ->options(
                        User::getDropdownData([
                            'sacco_id' => $u->sacco_id,
                        ])
                    )->rules('required');
            });

        $form->radio('period_type', __('Period Type'))
            ->options([
                'Cycle' => 'Cycle',
                'Period' => 'Period',
            ])->rules('required')
            ->when('Cycle', function ($form) {
                $u = Admin::user();
                $form->select('cycle_id', __('Cycle'))
                    ->options(
                        Cycle::getDropdownData([
                            'sacco_id' => $u->sacco_id,
                        ])
                    )->rules('required');
            })->when('Period', function ($form) {
                $form->dateRange('start_date', 'end_date', __('Period'))->rules('required');
            });

        if ($form->isCreating()) {
            $form->hidden('status', __('Status'))->default('Pending');
        } else {
            $form->radio('status', __('Status'))
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                    'Prepared' => 'Prepared',
                ])->rules('required');
        }


        /*         $form->decimal('balance', __('Balance'))->default(0.00);
        $form->decimal('TOTAL_SAVING', __('TOTAL_SAVING'))->default(0.00);
        $form->decimal('SHARE_COUNT', __('SHARE COUNT'))->default(0.00);
        $form->decimal('SHARE', __('SHARE'))->default(0.00);
        $form->decimal('LOAN_BALANCE', __('LOAN BALANCE'))->default(0.00);
        $form->decimal('LOAN_TOTAL_AMOUNT', __('LOAN TOTAL AMOUNT'))->default(0.00);
        $form->decimal('LOAN_COUNT', __('LOAN COUNT'))->default(0.00);
        $form->decimal('LOAN_INTEREST', __('LOAN INTEREST'))->default(0.00);
        $form->decimal('LOAN_REPAYMENT', __('LOAN REPAYMENT'))->default(0.00);
        $form->decimal('FEE', __('FEE'))->default(0.00);
        $form->decimal('WITHDRAWAL', __('WITHDRAWAL'))->default(0.00);
        $form->decimal('CYCLE_PROFIT', __('CYCLE PROFIT'))->default(0.00);
        $form->decimal('FINE', __('FINE'))->default(0.00);
        $form->textarea('transactions_data', __('Transactions data'));
        $form->textarea('loan_transactions_data', __('Loan transactions data'));
        $form->textarea('users_data', __('Users data'));
        $form->textarea('loans_data', __('Loans data'));
        $form->text('is_processed', __('Is processed'))->default('No'); */

        return $form;
    }
}
