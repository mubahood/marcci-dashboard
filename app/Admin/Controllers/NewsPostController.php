<?php

namespace App\Admin\Controllers;

use App\Models\NewsPost;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NewsPostController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'NewsPost';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new NewsPost());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('title', __('Title'));
        $grid->column('created_by_id', __('Created by id'));
        $grid->column('details', __('Details'));
        $grid->column('photo', __('Photo'));
        $grid->column('category', __('Category'));
        $grid->column('views_count', __('Views count'));
        $grid->column('job_nature', __('Job nature'));
        $grid->column('job_minimum_academic_qualification', __('Job minimum academic qualification'));
        $grid->column('job_required_expirience', __('Job required expirience'));
        $grid->column('job_how_to_apply', __('Job how to apply'));
        $grid->column('job_phone_number', __('Job phone number'));
        $grid->column('job_location', __('Job location'));
        $grid->column('job_deadline', __('Job deadline'));
        $grid->column('job_slots', __('Job slots'));

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
        $show = new Show(NewsPost::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('title', __('Title'));
        $show->field('created_by_id', __('Created by id'));
        $show->field('details', __('Details'));
        $show->field('photo', __('Photo'));
        $show->field('category', __('Category'));
        $show->field('views_count', __('Views count'));
        $show->field('job_nature', __('Job nature'));
        $show->field('job_minimum_academic_qualification', __('Job minimum academic qualification'));
        $show->field('job_required_expirience', __('Job required expirience'));
        $show->field('job_how_to_apply', __('Job how to apply'));
        $show->field('job_phone_number', __('Job phone number'));
        $show->field('job_location', __('Job location'));
        $show->field('job_deadline', __('Job deadline'));
        $show->field('job_slots', __('Job slots'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new NewsPost());

        $form->textarea('title', __('Title'));
        $form->number('created_by_id', __('Created by id'));
        $form->textarea('details', __('Details'));
        $form->textarea('photo', __('Photo'));
        $form->text('category', __('Category'));
        $form->text('views_count', __('Views count'));
        $form->text('job_nature', __('Job nature'));
        $form->text('job_minimum_academic_qualification', __('Job minimum academic qualification'));
        $form->text('job_required_expirience', __('Job required expirience'));
        $form->text('job_how_to_apply', __('Job how to apply'));
        $form->text('job_phone_number', __('Job phone number'));
        $form->text('job_location', __('Job location'));
        $form->text('job_deadline', __('Job deadline'));
        $form->text('job_slots', __('Job slots'));

        return $form;
    }
}
