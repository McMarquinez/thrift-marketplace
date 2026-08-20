<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ShipmentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ShipmentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ShipmentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Shipment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/shipment');
        CRUD::setEntityNameStrings('shipment', 'shipments');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'name' => 'order_id',
            'label' => 'Order',
            'type' => 'select',
            'entity' => 'order',
            'model' => \App\Models\Order::class,
            'attribute' => 'order_number',
        ]);
        CRUD::column('courier');
        CRUD::column('tracking_number');
        CRUD::column('shipping_fee')->type('number')->prefix('PHP ')->decimals(2);
        CRUD::column('status');
        CRUD::column('shipped_at')->type('datetime');
        CRUD::column('delivered_at')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ShipmentRequest::class);

        CRUD::addField([
            'name' => 'order_id',
            'label' => 'Order',
            'type' => 'select',
            'entity' => 'order',
            'model' => \App\Models\Order::class,
            'attribute' => 'order_number',
        ]);
        CRUD::field('courier');
        CRUD::field('tracking_number');
        CRUD::field('shipping_fee')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('status')->type('select_from_array')->options([
            'pending' => 'pending',
            'processing' => 'processing',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
        ]);
        CRUD::field('shipped_at')->type('datetime');
        CRUD::field('delivered_at')->type('datetime');
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
