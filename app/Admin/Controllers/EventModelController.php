<?php

namespace App\Admin\Controllers;

use App\Models\EventModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EventModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Events';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new EventModel());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('theme', __('Theme'));
        $grid->column('photo', __('Photo'));
        $grid->column('details', __('Details'));
        $grid->column('venue_name', __('Venue name'));
        $grid->column('venue_address', __('Venue address'));
        $grid->column('gps_latitude', __('Gps latitude'));
        $grid->column('gps_longitude', __('Gps longitude'));
        $grid->column('event_date', __('Event date'));
        $grid->column('event_time', __('Event time'));
        $grid->column('event_duration', __('Event duration'));
        $grid->column('event_type', __('Event type'));
        $grid->column('ticket_types', __('Ticket types'));
        $grid->column('is_free', __('Is free'));
        $grid->column('status', __('Status'));
        $grid->column('event_organizer', __('Event organizer'));
        $grid->column('rsvp_phone_1', __('Rsvp phone 1'));
        $grid->column('rsvp_phone_2', __('Rsvp phone 2'));
        $grid->column('rsvp_email', __('Rsvp email'));
        $grid->column('rsvp_url', __('Rsvp url'));

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
        $show = new Show(EventModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('theme', __('Theme'));
        $show->field('photo', __('Photo'));
        $show->field('details', __('Details'));
        $show->field('venue_name', __('Venue name'));
        $show->field('venue_address', __('Venue address'));
        $show->field('gps_latitude', __('Gps latitude'));
        $show->field('gps_longitude', __('Gps longitude'));
        $show->field('event_date', __('Event date'));
        $show->field('event_time', __('Event time'));
        $show->field('event_duration', __('Event duration'));
        $show->field('event_type', __('Event type'));
        $show->field('ticket_types', __('Ticket types'));
        $show->field('is_free', __('Is free'));
        $show->field('status', __('Status'));
        $show->field('event_organizer', __('Event organizer'));
        $show->field('rsvp_phone_1', __('Rsvp phone 1'));
        $show->field('rsvp_phone_2', __('Rsvp phone 2'));
        $show->field('rsvp_email', __('Rsvp email'));
        $show->field('rsvp_url', __('Rsvp url'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new EventModel());

        $form->textarea('name', __('Name'));
        $form->textarea('theme', __('Theme'));
        $form->image('photo', __('Photo'));
        $form->textarea('details', __('Details'));
        $form->textarea('venue_name', __('Venue name'));
        $form->textarea('venue_address', __('Venue address'));
        $form->textarea('gps_latitude', __('Gps latitude'));
        $form->textarea('gps_longitude', __('Gps longitude'));
        $form->textarea('event_date', __('Event date'));
        $form->textarea('event_time', __('Event time'));
        $form->textarea('event_duration', __('Event duration'));
        $form->textarea('event_type', __('Event type'));
        $form->textarea('ticket_types', __('Ticket types'));
        $form->textarea('is_free', __('Is free'));
        $form->text('status', __('Status'))->default('Upcoming');
        $form->textarea('event_organizer', __('Event organizer'));
        $form->textarea('rsvp_phone_1', __('Rsvp phone 1'));
        $form->textarea('rsvp_phone_2', __('Rsvp phone 2'));
        $form->textarea('rsvp_email', __('Rsvp email'));
        $form->textarea('rsvp_url', __('Rsvp url'));

        return $form;
    }
}
