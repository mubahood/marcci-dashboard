<?php

namespace App\Admin\Controllers;

use App\Models\PestsAndDisease;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Crop;
use App\Models\GroundnutVariety;

class PestsAndDiseaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PestsAndDisease';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PestsAndDisease());

        //show a user only their gardens
        $user = auth()->user();
        if(!$user->isRole('administrator')){
          $grid->model()->where('user_id', $user->id);
        }
        //filter by garden name
        $grid->filter(function($filter) use($user){
            //disable the default id filter
            $filter->disableIdFilter();
            $filter->like('category', 'Category');
        });

        //disable  column selector
        $grid->disableColumnSelector();

        //disable export
        $grid->disableExport();

        $grid->column('garden_location', __('Garden location'));
        $grid->column('user_id', __('User'))->display(function($user_id) {
            return \App\Models\User::find($user_id)->name;
        });
        $grid->column('variety_id', __('Variety'))->display(function($variety_id) {
            return \App\Models\GroundnutVariety::find($variety_id)->name;
        });
        $grid->column('category', __('Category'));
  
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
        $show = new Show(PestsAndDisease::findOrFail($id));

        $show->field('garden_location', __('Garden location'));
        $show->field('user_id', __('User id'))->as(function($user_id){
            return \App\Models\User::find($user_id)->name;
        });
        $show->field('variety_id', __('Variety'))->as(function($variety_id){
            return \App\Models\GroundnutVariety::find($variety_id)->name;
        });
        $show->field('category', __('Category'));
        $show->field('photo', __('Photo'))->image();
        $show->field('video', __('Video'))->video();
        $show->field('audio', __('Audio'))->audio();
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PestsAndDisease());

        $user = auth()->user();

        //When form is creating, assign user id
        if ($form->isCreating()) 
        {
            $form->hidden('user_id')->default($user->id);

        }

         //onsaved return to the list page
         $form->saved(function (Form $form) 
        {
            admin_toastr(__('Query submitted successfully'), 'success');
            return redirect('/pests-and-diseases');
        });

        $form->text('garden_location', __('Garden location'));
        $form->select('variety_id', __('Variety '))->options(GroundnutVariety::pluck('name', 'id'))->rules('required');
        $form->text('category', __('Category'));
        $form->file('photo', __('Photo'));
        $form->file('video', __('Video'));
        $form->file('audio', __('Audio'));
        $form->text('description', __('Description'));

        return $form;
    }
}
