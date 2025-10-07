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

        // Filter by SACCO
        if (!$u->isRole('admin')) {
            $grid->model()->where('sacco_id', $u->sacco_id);
        }

        $grid->model()->orderBy('id', 'DESC');
        $grid->disableBatchActions();

        // Core Information
        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Program Name'))->sortable();
        
        $grid->column('status', __('Status'))
            ->label([
                'Active' => 'success',
                'InActive' => 'default',
            ])
            ->sortable()
            ->filter([
                'Active' => 'Active',
                'InActive' => 'Inactive',
            ]);

        // Contribution Details
        $grid->column('contribution_type', __('Type'))
            ->label([
                'Periodic' => 'info',
                'Open' => 'warning',
            ])
            ->sortable()
            ->filter([
                'Periodic' => 'Periodic',
                'Open' => 'Open',
            ]);

        $grid->column('periodic_type', __('Period'))
            ->label([
                'Weekly' => 'primary',
                'Monthly' => 'success',
            ])
            ->sortable()
            ->hide();

        // Amount Information
        $grid->column('amount_per_member_type', __('Amount Type'))
            ->using([
                'Specific' => 'Fixed Amount',
                'Any' => 'Flexible Amount',
            ])
            ->sortable();

        $grid->column('amount_per_member_value', __('Amount Per Member'))
            ->display(function ($amount) {
                return $amount ? 'UGX ' . number_format($amount) : '-';
            })
            ->sortable();

        // Financial Summary
        $grid->column('total_expected', __('Expected'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount ?? 0);
            })
            ->sortable()->totalRow(function ($amount) {
                return 'UGX ' . number_format($amount);
            });

        $grid->column('total_collected', __('Collected'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount ?? 0);
            })
            ->sortable()->totalRow(function ($amount) {
                return 'UGX ' . number_format($amount);
            });

        $grid->column('total_balance', __('Balance'))
            ->display(function ($amount) {
                $balance = $amount ?? 0;
                $class = $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : '');
                return "<span class='{$class}'>UGX " . number_format($balance) . "</span>";
            })
            ->sortable()->totalRow(function ($amount) {
                return 'UGX ' . number_format($amount);
            });

        // Dates
        $grid->column('start_date', __('Start Date'))
            ->display(function ($date) {
                return $date ? date('d M Y', strtotime($date)) : '-';
            })->sortable();

        $grid->column('end_date', __('End Date'))
            ->display(function ($date) {
                return $date ? date('d M Y', strtotime($date)) : '-';
            })->sortable()->hide();

        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return date('d M Y', strtotime($date));
            })->sortable()->hide();

        // Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            
            $filter->like('name', 'Program Name');
            $filter->equal('status', 'Status')->select([
                'Active' => 'Active',
                'InActive' => 'Inactive',
            ]);
            $filter->equal('contribution_type', 'Type')->select([
                'Periodic' => 'Periodic',
                'Open' => 'Open',
            ]);
            $filter->between('start_date', 'Start Date')->date();
            $filter->between('end_date', 'End Date')->date();
        });

        // Column selector
        $grid->showColumnSelector();

        // Export
        $grid->export(function ($export) {
            $export->filename('Contribution_Programs_' . date('Y-m-d'));
        });

        // Print Report Button
        $grid->column('print_report', __('Report'))
            ->display(function () {
                $url = url('program-report/' . $this->id);
                return "<a href='$url' target='_blank' class='btn btn-xs btn-success' title='View Program Report'>
                    <i class='fa fa-print'></i> Print Report
                </a>";
            });

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
        $form = new Form(new ContributionProgram());
        $u = Admin::user();

        // Hidden: SACCO ID (auto-assigned)
        $form->hidden('sacco_id')->default($u->sacco_id);

        // 1. Contribution Title (REQUIRED)
        $form->text('name', __('Contribution title'))
            ->rules('required');

        // 2. Contribution Type (REQUIRED)
        $form->radio('contribution_type', __('Contribution type'))
            ->options([
                'Periodic' => 'Repetitive',
                'One time' => 'One time',
            ])
            ->rules('required')
            ->when('Periodic', function ($form) {
                
                // 3. Repeat Type (Cycle) - Only when Periodic
                $form->radio('periodic_type', __('Repeat type (Cycle)'))
                    ->options([
                        'Weekly' => 'Weekly',
                        'Monthly' => 'Monthly',
                    ])
                    ->rules('required');

                // 4. Repetitive Amount Type - Per member - Only when Periodic
                $form->radio('amount_per_member_type', __('Repetitive Amount Type - Per member'))
                    ->options([
                        'Specific' => 'Specific Amount',
                        'Any' => 'Any Amount',
                    ])
                    ->rules('required')
                    ->when('Specific', function ($form) {
                        
                        // 5. Expected amount from each member - Only when Specific
                        $form->radio('amount_to_use', __('Expected amount from each member'))
                            ->options([
                                'PERSONALIZED_AMOUNT' => 'Personalized Amount (assigned on person)',
                                'PROGRAM_AMOUNT' => 'Contribution amount (for this program)',
                            ])
                            ->rules('required')
                            ->when('PROGRAM_AMOUNT', function ($form) {
                                
                                // 6. Amount per member per cycle - Only when PROGRAM_AMOUNT
                                $form->currency('amount_per_member_value', __('Amount per member per cycle (UGX)'))
                                    ->symbol('UGX')
                                    ->rules('required');
                            });

                        // 7. Start Date & End Date (Side by side)
                        $form->date('start_date', __('Contribution Start date'))
                            ->format('YYYY-MM-DD')
                            ->rules('required');

                        $form->date('end_date', __('Contribution end date'))
                            ->format('YYYY-MM-DD')
                            ->rules('required');
                    });
            });

        // 8. Target Amount (ALWAYS VISIBLE - Outside all conditions)
        $form->currency('target_amount', __('Target Amount (UGX)'))
            ->symbol('UGX')
            ->rules('required');

        // 9. Status (REQUIRED)
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'InActive' => 'Closed',
            ])
            ->rules('required')
            ->default('Active');

        // Hidden fields
        $form->hidden('total_expected')->default(0);
        $form->hidden('total_collected')->default(0);
        $form->hidden('total_balance')->default(0);
        $form->hidden('prepared')->default('No');

        // Form settings
        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        // Saving event - Clean up fields based on conditions
        $form->saving(function (Form $form) {
            // Validate dates if both are provided
            if (!empty($form->start_date) && !empty($form->end_date)) {
                if (strtotime($form->end_date) < strtotime($form->start_date)) {
                    admin_error('End date must be after start date');
                    return redirect()->back()->withInput();
                }
            }

            // Clean up fields based on contribution type
            if ($form->contribution_type !== 'Periodic') {
                // Clear periodic-related fields
                $form->periodic_type = null;
                $form->amount_per_member_type = null;
                $form->amount_to_use = null;
                $form->amount_per_member_value = null;
                $form->start_date = null;
                $form->end_date = null;
            } else {
                // Periodic contribution
                if ($form->amount_per_member_type !== 'Specific') {
                    // Clear specific amount fields
                    $form->amount_to_use = null;
                    $form->amount_per_member_value = null;
                    $form->start_date = null;
                    $form->end_date = null;
                } else {
                    // Specific amount
                    if ($form->amount_to_use !== 'PROGRAM_AMOUNT') {
                        // Clear program amount field
                        $form->amount_per_member_value = null;
                    }
                }
            }

            // Initialize financial fields
            if (!isset($form->total_expected)) {
                $form->total_expected = 0;
            }
            if (!isset($form->total_collected)) {
                $form->total_collected = 0;
            }
            if (!isset($form->total_balance)) {
                $form->total_balance = 0;
            }
            if (!isset($form->prepared)) {
                $form->prepared = 'No';
            }
        });

        // Saved event
        $form->saved(function (Form $form) {
            if ($form->isCreating()) {
                admin_success('Contribution program created successfully!');
            } else {
                admin_success('Contribution program updated successfully!');
            }
        });

        return $form;
    }
}
