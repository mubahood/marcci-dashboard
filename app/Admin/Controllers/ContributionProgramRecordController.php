<?php

namespace App\Admin\Controllers;

use App\Models\ContributionProgramRecord;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
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

        // SACCO Filtering for non-admin users
        $u = \Encore\Admin\Facades\Admin::user();
        if (!$u->isRole('admin')) {
            $grid->model()->where('sacco_id', $u->sacco_id);
        }

        // Order by most recent first
        $grid->model()->orderBy('id', 'DESC');

        // Disable batch actions
        $grid->disableBatchActions();

        // ID Column
        $grid->column('id', __('ID'))
            ->sortable();

        // Member Name
        $grid->column('member.name', __('Member'))
            ->display(function ($name) {
                return $name ?? 'N/A';
            })
            ->sortable();

        // Contribution Program
        $grid->column('program.name', __('Contribution Program'))
            ->display(function ($name) {
                return $name ?? 'N/A';
            })
            ->sortable();

        // Treasurer Name
        $grid->column('treasurer.name', __('Treasurer'))
            ->display(function ($name) {
                return $name ?? 'N/A';
            })
            ->sortable();

        // Due Date (Period Range Start)
        $grid->column('period_range_start', __('Due Date'))
            ->display(function ($date) {
                return $date ? date('d M Y', strtotime($date)) : 'N/A';
            })
            ->sortable();

        // Payment Date
        $grid->column('payment_date', __('Payment Date'))
            ->display(function ($date) {
                return $date ? date('d M Y', strtotime($date)) : 'N/A';
            })
            ->sortable();

        // Expected Amount
        $grid->column('amount', __('Expected'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount ?? 0);
            })
            ->sortable()
            ->totalRow(function ($amount) {
                return 'UGX ' . number_format($amount);
            });

        // Paid Amount
        $grid->column('paid_amount', __('Paid'))
            ->display(function ($amount) {
                return 'UGX ' . number_format($amount ?? 0);
            })
            ->sortable()
            ->totalRow(function ($amount) {
                return 'UGX ' . number_format($amount);
            });

        // Balance (Calculated field - no totalRow)
        $grid->column('balance', __('Balance'))
            ->display(function () {
                $balance = ($this->amount ?? 0) - ($this->paid_amount ?? 0);
                $color = $balance > 0 ? 'red' : 'green';
                return "<span style='color: {$color}; font-weight: bold;'>UGX " . number_format($balance) . "</span>";
            });

        // Is Paid Status
        $grid->column('is_paid', __('Paid?'))
            ->label([
                'Yes' => 'success',
                'No' => 'danger',
            ])
            ->sortable()
            ->filter([
                'Yes' => 'Yes',
                'No' => 'No',
            ]);

        // Period Info
        $grid->column('period_name', __('Period'))
            ->display(function ($period) {
                return $period ?? 'N/A';
            })
            ->sortable();

        // Description
        $grid->column('description', __('Description'))
            ->display(function ($desc) {
                return $desc ? \Illuminate\Support\Str::limit($desc, 50) : '-';
            });

        // Created Date
        $grid->column('created_at', __('Created'))
            ->display(function ($date) {
                return date('d M Y', strtotime($date));
            })
            ->sortable()
            ->hide();

        // Advanced Filters
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $u = Admin::user();
            // Member filter 
            $ajax_url = url(
                '/api/ajax-users?'
                    . 'sacco_id=' . $u->sacco_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
            );
            $ajax_url = trim($ajax_url);
            $filter->equal('member_id', 'Member')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);

            // Program filter
            $filter->equal('contribution_program_id', 'Program')->select(
                \App\Models\ContributionProgram::where('sacco_id', \Encore\Admin\Facades\Admin::user()->sacco_id)
                    ->pluck('name', 'id')
            );

            // Paid status filter
            $filter->equal('is_paid', 'Paid Status')->select([
                'Yes' => 'Paid',
                'No' => 'Not Paid',
            ]);

            // Date filters
            $filter->between('payment_date', 'Payment Date')->date();
            $filter->between('period_range_start', 'Due Date')->date();

            // Year filter
            $filter->equal('year', 'Year')->select([
                date('Y') => date('Y'),
                date('Y') - 1 => date('Y') - 1,
                date('Y') - 2 => date('Y') - 2,
            ]);
        });

        // Column selector
        $grid->showColumnSelector();

        // Export
        $grid->export(function ($export) {
            $export->filename('Contribution_Records_' . date('Y-m-d'));
            $export->column('balance', function ($value, $original) {
                return ($original['amount'] ?? 0) - ($original['paid_amount'] ?? 0);
            });
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
        $u = \Encore\Admin\Facades\Admin::user();

        // Hidden: SACCO ID
        $form->hidden('sacco_id')->default($u->sacco_id);

        // Hidden: Treasurer ID (current user)
        $form->hidden('teasurer_id')->default($u->id);
        if ($form->isCreating()) {

            // 1. Member (REQUIRED) - Read-only when editing
            $form->select('member_id', __('Member'))
                ->options(function ($id) use ($u) {
                    if ($id) {
                        $member = \App\Models\User::find($id);
                        return $member ? [$member->id => $member->name] : [];
                    }
                    return [];
                })
                ->ajax('/admin/api/users?sacco_id=' . $u->sacco_id)
                ->rules('required')
                ->disable('isEditing');

            // 2. Contribution Program (REQUIRED) - Read-only when editing
            $form->select('contribution_program_id', __('Contribution Program'))
                ->options(function ($id) use ($u) {
                    if ($id) {
                        $program = \App\Models\ContributionProgram::find($id);
                        return $program ? [$program->id => $program->name] : [];
                    }
                    return [];
                })
                ->ajax('/admin/api/contribution-programs?sacco_id=' . $u->sacco_id)
                ->rules('required')
                ->disable('isEditing');
        }

        // 3. Treasurer (Read-only - shows current user)
        $form->display('treasurer_name', __('Treasurer'))
            ->default($u->name);

        $form->divider();

        // 4. Due Date (Only shown when creating new record)
        $form->date('period_range_start', __('Due Date'))
            ->format('YYYY-MM-DD')
            ->default(date('Y-m-d'))
            ->rules('required')
            ->disable('isEditing');

        // 5. Is Paid? (REQUIRED)
        $form->radio('is_paid', __('Paid?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])
            ->rules('required')
            ->default('No')
            ->when('Yes', function ($form) {
                // 6. Amount Paid (Only when is_paid = Yes)
                $form->currency('paid_amount', __('Amount Paid'))
                    ->symbol('UGX')
                    ->rules('required|numeric|min:0');
            });

        // 7. Payment Date (REQUIRED)
        $form->date('payment_date', __('Payment Date'))
            ->format('YYYY-MM-DD')
            ->default(date('Y-m-d'))
            ->rules('required');

        // 8. Description (Optional)
        $form->text('description', __('Description'));

        // Hidden auto-filled fields
        $form->hidden('year')->default(date('Y'));
        $form->hidden('month_number')->default(date('n'));
        $form->hidden('week_number')->default(date('W'));
        $form->hidden('month_name')->default(date('F'));

        // Form settings
        $form->disableCreatingCheck();
        // $form->disableEditingCheck();
        $form->disableViewCheck();

        // Saving event
        $form->saving(function (Form $form) {
            // Auto-calculate year, month, week if not set
            if (empty($form->year)) {
                $form->year = date('Y');
            }
            if (empty($form->month_number)) {
                $form->month_number = date('n');
            }
            if (empty($form->week_number)) {
                $form->week_number = date('W');
            }
            if (empty($form->month_name)) {
                $form->month_name = date('F');
            }

            // Ensure paid_amount is 0 if not paid
            if ($form->is_paid !== 'Yes') {
                $form->paid_amount = 0;
            }

            // Validate paid amount doesn't exceed expected amount
            if ($form->is_paid === 'Yes' && !empty($form->paid_amount)) {
                $record = \App\Models\ContributionProgramRecord::find($form->model()->id);
                if ($record && $form->paid_amount > $record->amount) {
                    admin_error('Paid amount cannot exceed expected amount (UGX ' . number_format($record->amount) . ')');
                    return redirect()->back()->withInput();
                }
            }
        });

        // Saved event
        $form->saved(function (Form $form) {
            if ($form->isCreating()) {
                admin_success('Contribution record created successfully!');
            } else {
                admin_success('Contribution record updated successfully!');
            }
        });

        return $form;
    }
}
