<?php

namespace App\Admin\Controllers;

use App\Models\ContributionProgram;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ContributionProgramController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Contribution Programs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $u = Admin::user();
        $grid = new Grid(new ContributionProgram());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('sacco_id', __('Sacco id'));
        $grid->column('total_expected', __('Total expected'));
        $grid->column('total_collected', __('Total collected'));
        $grid->column('total_balance', __('Total balance'));
        $grid->column('amount_per_member_type', __('Amount per member type'));
        $grid->column('amount_per_member_value', __('Amount per member value'));
        $grid->column('name', __('Name'));
        $grid->column('contribution_type', __('Contribution type'));
        $grid->column('periodic_type', __('Periodic type'));
        $grid->column('new_members_billing_type', __('New members billing type'));
        $grid->column('details', __('Details'));
        $grid->column('status', __('Status'));
        $grid->column('public_type', __('Public type'));
        $grid->column('membership_type', __('Membership type'));
        $grid->column('members', __('Members'));
        $grid->column('treasurers', __('Treasurers'));
        $grid->column('start_date', __('Start date'));
        $grid->column('end_date', __('End date'));

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
        $show = new Show(ContributionProgram::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('total_expected', __('Total expected'));
        $show->field('total_collected', __('Total collected'));
        $show->field('total_balance', __('Total balance'));
        $show->field('amount_per_member_type', __('Amount per member type'));
        $show->field('amount_per_member_value', __('Amount per member value'));
        $show->field('name', __('Name'));
        $show->field('contribution_type', __('Contribution type'));
        $show->field('periodic_type', __('Periodic type'));
        $show->field('new_members_billing_type', __('New members billing type'));
        $show->field('details', __('Details'));
        $show->field('status', __('Status'));
        $show->field('public_type', __('Public type'));
        $show->field('membership_type', __('Membership type'));
        $show->field('members', __('Members'));
        $show->field('treasurers', __('Treasurers'));
        $show->field('start_date', __('Start date'));
        $show->field('end_date', __('End date'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
    /*     $rec = ContributionProgram::find(4);
        $rec->prepared = 'No';
        //ContributionProgram::validate($rec);
        ContributionProgram::prepare($rec);
        die('done'); */

        $form = new Form(new ContributionProgram());
        $u = Admin::user();
        $users = User::where([
            'sacco_id' => $u->sacco_id
        ])->get()
            ->pluck('name', 'id');
        $form->hidden('sacco_id', __('Sacco id'))->default($u->sacco_id);
        /*         $form->number('total_expected', __('Total expected'));
        $form->number('total_collected', __('Total collectedq'));
        $form->number('total_balance', __('Total balance')); */
        $form->text('name', __('Contribution Name'))->rules('required');
        $form->select('amount_per_member_type', __('Contribution Amount Type'))
            ->options([
                'Specific' => 'Specific Amount',
                'Any' => 'Any Amount',
            ])->rules('required')
            ->when('Specific', function ($form) {
                $form->number('amount_per_member_value', __('Amount per member'))
                    ->rules('required');
            })
            ->when('Any', function ($form) {
                $form->number('total_expected', __('Target amount'))
                    ->rules('required');
            });


        $form->select('contribution_type', __('Contribution type'))
            ->options([
                'Periodic' => 'Periodic',
                'Open' => 'Open',
            ])->when('Periodic', function ($form) {
                $form->select('periodic_type', __('Period type'))
                    ->rules('required')
                    ->options([
                        'Weekly' => 'Weekly',
                        'Monthly' => 'Monthly',
                    ]);

                $form->select('new_members_billing_type', __('New members billing type'))
                    ->rules('required')
                    ->options([
                        'MemberRegisterDate' => 'Member Register Date',
                        'ContributionStartDate' => 'Contribution Start Date',
                        'SpecificDate' => 'Specific Date',
                    ])->rules('required');
            })->rules('required');
        $form->text('details', __('Details'));
        $form->select('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'InActive' => 'Not Active'
            ])
            ->default('Active');

        $form->radio('membership_type', __('Membership target'))
            ->options([
                'All' => 'All members',
                'Specific' => 'Specific members'
            ])->rules('required')
            ->when('Specific', function ($form) {
                $u = Admin::user();
                $users = User::where([
                    'sacco_id' => $u->sacco_id
                ])->get()
                    ->pluck('name', 'id');
                $form->listbox('members', __('Members'))
                    ->options($users)->rules('required');
            });
        $form->select('public_type', __('Contribution Visibility Type'))
            ->options([
                'Public' => 'Public',
                'Private' => 'Private',
            ])
            ->rules('required');

        $form->listbox('treasurers', __('Treasurers'))
            ->options($users)->rules('required')
            ->rules('required');

        $form->date('start_date', __('Start date'))->default(date('Y-m-d'))->rules('required');
        $form->date('end_date', __('End date'))->rules('required');

        return $form;
    }
}
