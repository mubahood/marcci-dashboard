<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MembersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Members';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $u = Admin::user();
        // Filter by Sacco
        if (!$u->isRole('admin')) {
            if (!$u->isRole('sacco')) {
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableDelete();
                });
                // $grid->disableFilter();
            }
            $grid->model()->where('sacco_id', $u->sacco_id);
        }


        $conds = [];
        //check if reg_number is not set in GET, then 
        if (!isset($_GET['reg_number'])) {
            // $conds['reg_number'] = 'Alive';
        }

        $grid->model()
            ->where($conds)
            ->orderBy('id', 'DESC');

        $grid->disableBatchActions();

        // ID and Photo
        $grid->column('id', __('ID'))->sortable();

        $grid->column('avatar', __('Photo'))->image('', 50, 50)
            ->hide();

        // Search functionality
        $grid->quickSearch('first_name', 'name', 'last_name', 'email', 'phone_number', 'reg_number')
            ->placeholder('Search by name, email, phone or registration number');

        // Personal Information
        $grid->column('first_name', __('Name'))
            ->display(function ($first_name) {
                return $this->first_name . ' ' . $this->last_name;
            })
            ->sortable();
        $grid->column('name', __('Full Name'))->sortable()->hide();

        $grid->column('sex', __('Gender'))
            ->using([
                'Male' => 'Male',
                'Female' => 'Female',
            ])
            ->dot([
                'Male' => 'primary',
                'Female' => 'danger',
            ])
            ->sortable()
            ->hide()
            ->filter([
                'Male' => 'Male',
                'Female' => 'Female',
            ]);

        $grid->column('dob', __('Date of Birth'))
            ->display(function ($dob) {
                if (!$dob || $dob == '0000-00-00' || $dob == '0000-00-00 00:00:00') return '-';
                try {
                    $date = date('d M Y', strtotime($dob));
                    $age = \Carbon\Carbon::parse($dob)->age;
                    return $date . " ($age years)";
                } catch (\Exception $e) {
                    return '-';
                }
            })->hide();


        $grid->column('email', __('Email'))->sortable()->hide();
        $grid->column('whatsapp', __('WhatsApp'))->hide();

        // Location
        $grid->column('address', __('Address'))->limit(30)->hide();
        $grid->column('country', __('Country of Residence'))->hide();
        //twitter
        $grid->column('twitter', __('Parent'))
            ->display(function ($twitter) {
                return $this->twitter ?? '-';
            })->sortable()
            ->filter(function ($filter) {
                $filter->like('twitter', 'Parent Name');
            });
        //relationship
        $grid->column('website', __('Relationship'))
            ->display(function ($website) {
                return $this->website ?? '-';
            })->sortable()
            ->filter([
                'Root' => 'Root',
                'Father' => 'Father',
                'Mother' => 'Mother',
            ]);

        // Registration & Status
        $grid->column('reg_number', __('Life Status'))
            ->using([
                'Alive' => 'Alive',
                'Late' => 'Deceased',
            ]) 
            ->dot([
                'Alive' => 'success',
                'Late' => 'danger',
            ])
            ->sortable()
            ->filter([
                'Alive' => 'Alive',
                'Late' => 'Deceased',
            ]);

        /* $grid->column('user_type', __('Type'))
            ->label([
                'Admin' => 'danger',
                'Member' => 'success',
                'Treasurer' => 'warning',
            ])
            ->sortable()
            ->hide(); */

        $grid->column('language', __('Contribution'))

            ->sortable()
            ->filter([
                'Compulsory' => 'Compulsory',
                'Optional' => 'Optional',
                'None' => 'N/A (Not Applicable)',
            ])
            ->editable('select', [
                'Compulsory' => 'Compulsory',
                'Optional' => 'Optional',
                'None' => 'N/A (Not Applicable)',
            ]);

        $grid->column('personalized_contribution_amount', __('Amount (UGX)'))
            ->editable()->sortable();
        // Financial Information
        $grid->column('balance', __('Balance'))
            ->display(function ($balance) {
                return 'UGX ' . number_format($balance ?? 0);
            })
            ->sortable();

        //contribution_start_date
        $grid->column('contribution_start_date', __('Contribution Start Date'))
            ->editable('date')->sortable();



        // Sacco (for Admin only)
        if ($u->isRole('admin')) {
            $grid->column('sacco_id', __('Sacco'))
                ->display(function ($sacco_id) {
                    if (empty($sacco_id)) return '-';
                    $sacco = \App\Models\Sacco::find($sacco_id);
                    return $sacco ? $sacco->name : '-';
                })->sortable();
        }

        // Filters
        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();



            $ajax_url = url(
                '/api/ajax-users?'
                    . 'sacco_id=' . $u->sacco_id
                    . "&search_by_1=name"
                    . "&search_by_2=id"
            );
            $ajax_url = trim($ajax_url);
            $filter->equal('linkedin', 'Filter by Parent')
                ->select(function ($id) {
                    $a = User::find($id);
                    if ($a) {
                        return [$a->id => $a->name];
                    }
                })->ajax($ajax_url);




            $filter->between('created_at', 'Registration Date')->datetime();
            $filter->between('dob', 'Date of Birth')->date();

            if ($u->isRole('admin')) {
                $filter->equal('sacco_id', 'Sacco')->select(\App\Models\Sacco::pluck('name', 'id')->toArray());
            }
        });



        // Dates
        $grid->column('created_at', __('Date Joined'))
            ->display(function ($date) {
                return date('d M Y', strtotime($date));
            })->sortable();

        // Column selector
        $grid->showColumnSelector();

        // Export
        $grid->export(function ($export) {
            $export->filename('Sacco_Members_' . date('Y-m-d'));
            $export->except(['avatar', 'profile_photo', 'profile_photo_large']);
        });

        // Contact Information
        $grid->column('phone_number', __('Phone Number'))->sortable()->editable();
        $grid->column('add_child', __('Add Child'))
            ->display(function () {
                $url = admin_url('members/create') . '?parent_id=' . $this->id . '&relationship=Child';
                return "<a href='$url' class='btn btn-xs btn-primary'>Add Child</a>";
            });
        
        // Print Report Button
        $grid->column('print_report', __('Report'))
            ->display(function () {
                $url = url('member-report/' . $this->id);
                return "<a href='$url' target='_blank' class='btn btn-xs btn-success' title='View Contribution Report'>
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('first_name', __('First name'));
        $show->field('last_name', __('Last name'));
        $show->field('reg_date', __('Reg date'));
        $show->field('last_seen', __('Last seen'));
        $show->field('email', __('Email'));
        $show->field('approved', __('Approved'));
        $show->field('profile_photo', __('Profile photo'));
        $show->field('user_type', __('User type'));
        $show->field('sex', __('Sex'));
        $show->field('reg_number', __('Life Status'));
        $show->field('country', __('Country of Residence'));
        $show->field('occupation', __('Occupation'));
        $show->field('profile_photo_large', __('Profile photo large'));
        $show->field('phone_number', __('Phone number'));
        $show->field('location_lat', __('Location lat'));
        $show->field('location_long', __('Location long'));
        $show->field('facebook', __('Facebook'));
        $show->field('twitter', __('Parent Name'));
        $show->field('whatsapp', __('Whatsapp'));
        $show->field('linkedin', __('Parent ID'));
        $show->field('website', __('Family Parent Relationship'));
        $show->field('other_link', __('Highest Level of Education'));
        $show->field('cv', __('Date of Death'));
        $show->field('language', __('Contribution Eligibility'));
        $show->field('about', __('About'));
        $show->field('address', __('Address'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('remember_token', __('Remember token'));
        $show->field('avatar', __('Avatar'));
        $show->field('name', __('Name'));
        $show->field('campus_id', __('Campus id'));
        $show->field('complete_profile', __('Complete profile'));
        $show->field('title', __('Title'));
        $show->field('dob', __('Dob'));
        $show->field('intro', __('Intro'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('sacco_join_status', __('Sacco join status'));
        $show->field('id_front', __('Spouse'));
        $show->field('id_back', __('Id back'));
        $show->field('status', __('Status'));
        $show->field('balance', __('Balance'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());
        $u = Admin::user();

        // ============================================
        // SECTION 1: BASIC INFORMATION (Matches Mobile App Order)
        // ============================================
        $form->divider('MEMBER REGISTRATION');

        // Hidden: SACCO ID (auto-assigned)
        $form->hidden('sacco_id')->default($u->sacco_id ?? null);
        /* if (!$u->isRole('admin')) {
        } else {
            $form->select('sacco_id', __('Select Sacco'))
                ->options(\App\Models\Sacco::pluck('name', 'id')->toArray())
                ->rules('required')
                ->help('Select the SACCO this member belongs to');
        } */



        // 1. First Name (REQUIRED)
        $form->text('first_name', __('First Name'))
            ->rules('required|min:2|max:100')
            ->help('Enter member\'s first name');

        // 2. Last Name (REQUIRED)
        $form->text('last_name', __('Last Name'))
            ->rules('required|min:2|max:100')
            ->help('Enter member\'s last name');

        // 3. Family Parent Relationship (REQUIRED)
        $form->radio('website', __('Family Parent Relationship'))
            ->options([
                'Root' => 'Root (No Parent)',
                'Father' => 'Father',
                'Mother' => 'Mother',
            ])
            ->default('Root')
            ->rules('required')
            ->help('Father = this person\'s father, Mother = this person\'s mother');

        $u = Admin::user();
        $ajax_url = url(
            '/api/ajax-users?'
                . 'sacco_id=' . ($u->sacco_id ?? '')
                . "&search_by_1=name"
                . "&search_by_2=id"
        );
        $ajax_url = trim($ajax_url);

        $hasParent = null;
        //check if is creating and parent_id is set in GET
        if ($form->isCreating() && isset($_GET['parent_id'])) {
            $hasParent = User::find($_GET['parent_id']);
        }

        if ($hasParent != null) {
            $_options = User::where('id', $hasParent->id)->get();
            $options = [];
            foreach ($_options as $o) {
                $options[$o->id] = $o->name . " (ID: $o->id)";
            } 

            $form->select('linkedin', __('Select Parent'))
                ->options($options)
                ->default($hasParent->id)
                ->readOnly()
                ->help('Search and select parent member');
        } else {
            $form->select('linkedin', __('Select Parent'))
                ->options(function ($id) {
                    if ($id) {
                        $parent = User::find($id);
                        if ($parent) {
                            return [$parent->id => $parent->name];
                        }
                    }
                    return [];
                })
                ->ajax($ajax_url)
                ->help('Search and select parent member');
        }
        // 4. Gender (REQUIRED)
        $form->radio('sex', __('Gender'))
            ->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ])
            ->rules('required')
            ->default('Male');

        // 5. Date of Birth
        $form->date('dob', __('Date of Birth'))
            ->format('YYYY-MM-DD')
            ->help('Select member\'s date of birth');

        // 6. Life Status (REQUIRED)
        $form->radio('reg_number', __('Life Status'))
            ->options([
                'Alive' => 'Alive',
                'Late' => 'Deceased',
            ])
            ->default('Alive')
            ->rules('required')
            ->help('Current life status')
            ->when('Late', function (Form $form) {
                // 6a. Date of Death (conditional)
                $form->date('cv', __('Date of Death'))
                    ->format('YYYY-MM-DD')
                    ->help('Date of death');
            })
            ->when('Alive', function (Form $form) {
                $form->divider('ADMIN & CONTRIBUTION SETTINGS (Only for alive members)');

                // 7. Make Admin (REQUIRED for alive)
                $form->radio('is_admin', __('Make Admin'))
                    ->options([
                        'No' => 'No',
                        'Yes' => 'Yes',
                    ])
                    ->default('No')
                    ->rules('required');

                // 8. Contribution Eligibility (REQUIRED for alive)
                $form->radio('language', __('Contribution Eligibility'))
                    ->options([
                        'Compulsory' => 'Compulsory',
                        'Optional' => 'Optional',
                        'None' => 'N/A',
                    ])
                    ->default('Compulsory')
                    ->rules('required');

                // 9. Personalized Contribution Amount
                $form->currency('personalized_contribution_amount', __('Personalized Contribution Amount (UGX)'))
                    ->symbol('UGX')
                    ->default(5000)
                    ->rules('nullable|numeric|min:0');

                // 10. Contribution Start Date
                $form->date('contribution_start_date', __('Contribution Start Date'))
                    ->format('YYYY-MM-DD');

                $form->divider();
            });

        // 11. Phone Number (REQUIRED)
        $form->text('phone_number', __('Phone Number'))
            ->rules(function ($form) {
                if ($form->isCreating()) {
                    return 'required|unique:users,phone_number';
                }
                return 'required|unique:users,phone_number,' . $form->model()->id;
            })
            ->help('Primary phone number');

        // 12. Highest Level of Education
        $form->select('other_link', __('Highest Level of Education'))
            ->options([
                'None' => 'None',
                'Primary' => 'Primary',
                'O-Level' => 'O-Level',
                'A-Level' => 'A-Level',
                'Certificate' => 'Certificate',
                'Diploma' => 'Diploma',
                'Degree' => 'Degree',
                'Masters' => 'Masters',
                'PhD' => 'PhD',
            ])
            ->default('None');

        // 13. Country of Residence (REQUIRED)
        $form->select('country', __('Country of Residence'))
            ->options([
                'Uganda' => 'Uganda',
                'Kenya' => 'Kenya',
                'Tanzania' => 'Tanzania',
                'Rwanda' => 'Rwanda',
                'Burundi' => 'Burundi',
                'South Sudan' => 'South Sudan',
                'DRC' => 'DRC',
                'Other' => 'Other',
            ])
            ->default('Uganda')
            ->rules('required');

        // 14. Address
        $form->textarea('address', __('Address'))
            ->rows(2);

        // 15. Spouse
        $form->text('id_front', __('Spouse'))
            ->help('Spouse name');

        // 16. Photo Upload
        $form->image('avatar', __('Member Photo'))
            ->uniqueName()
            ->help('Upload member passport photo');

        // $form->divider('ADMIN SETTINGS');

        // Hidden/Auto-filled fields
        $form->hidden('user_type')->default('Member');
        $form->hidden('sacco_join_status')->default('Approved');
        $form->hidden('status')->default('Active');
        $form->hidden('complete_profile')->default(1);
        $form->hidden('approved')->default(1);
        $form->hidden('balance')->default(0);
        $form->hidden('should_be_validated')->default('Yes');

        // Password Section


        if ($form->isCreating()) {
            $form->password('password', __('Password'))
                ->rules('nullable|confirmed|min:6')
                ->help('Leave blank to auto-generate from phone number');

            $form->password('password_confirmation', __('Confirm Password'))
                ->help('Confirm password if setting manually');
        } else {

            //as question, do you want to change password?
            $form->checkbox('change_password', __('Change Password?'))
                ->options(['yes' => 'Yes, I want to change the password'])
                ->when('yes', function (Form $form) {
                    $form->password('password', __('New Password'))
                        ->rules('required|confirmed|min:6')
                        ->help('Enter new password');
                    $form->password('password_confirmation', __('Confirm New Password'))
                        ->rules('required')
                        ->help('Confirm new password');
                });
        }

        // ============================================
        // FORM SETTINGS
        // ============================================

        $form->ignore(['password_confirmation', 'change_password']);

        // Tools
        $form->disableViewCheck();

        // Saving event
        $form->saving(function (Form $form) {
            try {
                // Auto-generate username from phone number
                if (empty($form->username)) {
                    $form->username = $form->phone_number ?? 'user_' . time();
                }

                // Auto-generate full name
                if (!empty($form->first_name) && !empty($form->last_name)) {
                    $form->name = trim($form->first_name . ' ' . $form->last_name);
                }

                // If deceased, automatically set contribution eligibility to None
                if ($form->reg_number == 'Late') {
                    $form->language = 'None';
                    $form->is_admin = 'No';
                }

                // Hash password if provided and not already hashed
                if (!empty($form->password)) {
                    if (!str_starts_with($form->password, '$2y$') && !str_starts_with($form->password, '$2a$')) {
                        $form->password = bcrypt($form->password);
                    }
                } elseif (!$form->isCreating()) {
                    // Keep existing password for updates
                    unset($form->password);
                } else {
                    // Auto-generate password from phone for new members if empty
                    if (empty($form->password)) {
                        $form->password = bcrypt($form->phone_number ?? '123456');
                    }
                }

                // Set defaults
                if (!isset($form->balance)) {
                    $form->balance = 0;
                }
                if (!isset($form->approved)) {
                    $form->approved = 1;
                }
                if (!isset($form->complete_profile)) {
                    $form->complete_profile = 1;
                }
            } catch (\Exception $e) {
                admin_error('Error: ' . $e->getMessage());
                return redirect()->back()->withInput();
            }
        });

        // Saved event
        $form->saved(function (Form $form) {
            if ($form->isCreating()) {
                admin_success('Member registered successfully!');
            } else {
                admin_success('Member updated successfully!');
            }
        });

        return $form;
    }
}
