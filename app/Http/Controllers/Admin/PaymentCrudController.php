<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaymentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PaymentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PaymentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Payment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/payment');
        CRUD::setEntityNameStrings('payment', 'payments');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $orderId = request()->query('order_id');
        if (! empty($orderId)) {
            CRUD::addClause('where', 'order_id', (int) $orderId);
        }

        CRUD::addClause('orderByRaw', "CASE WHEN status = 'pending' THEN 0 ELSE 1 END");
        CRUD::addClause('orderBy', 'created_at', 'desc');

        CRUD::addColumn([
            'name' => 'order_id',
            'label' => 'Order',
            'type' => 'select',
            'entity' => 'order',
            'model' => \App\Models\Order::class,
            'attribute' => 'order_number',
        ]);
        CRUD::column('reference_number');
        CRUD::column('provider');
        CRUD::column('method');
        CRUD::column('amount')->type('number')->prefix('PHP ')->decimals(2);
        CRUD::column('status');
        CRUD::column('paid_at')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PaymentRequest::class);

        CRUD::addField([
            'name' => 'order_id',
            'label' => 'Order',
            'type' => 'select',
            'entity' => 'order',
            'model' => \App\Models\Order::class,
            'attribute' => 'order_number',
        ]);
        CRUD::field('reference_number');
        CRUD::field('provider');
        CRUD::field('method');
        CRUD::field('amount')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('status')->type('select_from_array')->options([
            'pending' => 'pending',
            'paid' => 'paid',
            'failed' => 'failed',
            'expired' => 'expired',
            'refunded' => 'refunded',
        ]);
        CRUD::field('paid_at')->type('datetime');
        CRUD::field('metadata')->type('textarea')->hint('Store JSON payload for gateway references or notes.');
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
