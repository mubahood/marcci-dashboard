<?php

namespace App\Admin\Controllers;

use App\Models\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Transaction';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('user_id', __('User id'));
        $grid->column('source_user_id', __('Source user id'));
        $grid->column('sacco_id', __('Sacco id'));
        $grid->column('type', __('Type'));
        $grid->column('source_type', __('Source type'));
        $grid->column('source_mobile_money_number', __('Source mobile money number'));
        $grid->column('source_mobile_money_transaction_id', __('Source mobile money transaction id'));
        $grid->column('source_bank_account_number', __('Source bank account number'));
        $grid->column('source_bank_transaction_id', __('Source bank transaction id'));
        $grid->column('desination_type', __('Desination type'));
        $grid->column('desination_mobile_money_number', __('Desination mobile money number'));
        $grid->column('desination_mobile_money_transaction_id', __('Desination mobile money transaction id'));
        $grid->column('desination_bank_account_number', __('Desination bank account number'));
        $grid->column('desination_bank_transaction_id', __('Desination bank transaction id'));
        $grid->column('amount', __('Amount'));
        $grid->column('description', __('Description'));
        $grid->column('details', __('Details'));

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
        $show = new Show(Transaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('user_id', __('User id'));
        $show->field('source_user_id', __('Source user id'));
        $show->field('sacco_id', __('Sacco id'));
        $show->field('type', __('Type'));
        $show->field('source_type', __('Source type'));
        $show->field('source_mobile_money_number', __('Source mobile money number'));
        $show->field('source_mobile_money_transaction_id', __('Source mobile money transaction id'));
        $show->field('source_bank_account_number', __('Source bank account number'));
        $show->field('source_bank_transaction_id', __('Source bank transaction id'));
        $show->field('desination_type', __('Desination type'));
        $show->field('desination_mobile_money_number', __('Desination mobile money number'));
        $show->field('desination_mobile_money_transaction_id', __('Desination mobile money transaction id'));
        $show->field('desination_bank_account_number', __('Desination bank account number'));
        $show->field('desination_bank_transaction_id', __('Desination bank transaction id'));
        $show->field('amount', __('Amount'));
        $show->field('description', __('Description'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Transaction());

        $form->number('user_id', __('User id'));
        $form->number('source_user_id', __('Source user id'));
        $form->number('sacco_id', __('Sacco id'));
        $form->text('type', __('Type'))->default('Deposit');
        $form->text('source_type', __('Source type'))->default('Mobile Money');
        $form->text('source_mobile_money_number', __('Source mobile money number'));
        $form->text('source_mobile_money_transaction_id', __('Source mobile money transaction id'));
        $form->text('source_bank_account_number', __('Source bank account number'));
        $form->text('source_bank_transaction_id', __('Source bank transaction id'));
        $form->text('desination_type', __('Desination type'))->default('Mobile Money');
        $form->text('desination_mobile_money_number', __('Desination mobile money number'));
        $form->text('desination_mobile_money_transaction_id', __('Desination mobile money transaction id'));
        $form->text('desination_bank_account_number', __('Desination bank account number'));
        $form->text('desination_bank_transaction_id', __('Desination bank transaction id'));
        $form->text('amount', __('Amount'));
        $form->textarea('description', __('Description'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
