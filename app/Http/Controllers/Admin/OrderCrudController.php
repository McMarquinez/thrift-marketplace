<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Prologue\Alerts\Facades\Alert;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\RedirectResponse;

/**
 * Class OrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrderCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Order::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/order');
        CRUD::setEntityNameStrings('order', 'orders');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addClause('orderBy', 'created_at', 'desc');

        CRUD::addButtonFromView('line', 'mark_paid', 'order_mark_paid', 'beginning');
        CRUD::addButtonFromView('line', 'open_payment', 'order_open_payment', 'beginning');

        CRUD::column('order_number');
        CRUD::column('customer_name')->label('Customer');
        CRUD::column('total')->type('number')->prefix('PHP ')->decimals(2);
        CRUD::column('payment_status');
        CRUD::column('status');
        CRUD::column('shipping_status');
        CRUD::column('created_at')->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(OrderRequest::class);

        CRUD::field('order_number');
        CRUD::addField([
            'name' => 'customer_id',
            'label' => 'Customer',
            'type' => 'select',
            'entity' => 'customer',
            'model' => \App\Models\Customer::class,
            'attribute' => 'name',
        ]);
        CRUD::field('customer_name');
        CRUD::field('customer_email');
        CRUD::field('customer_phone');
        CRUD::field('shipping_address')->type('textarea');
        CRUD::field('subtotal')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('shipping_fee')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('discount_amount')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('total')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('status')->type('select_from_array')->options([
            'pending_payment' => 'pending_payment',
            'paid' => 'paid',
            'processing' => 'processing',
            'packed' => 'packed',
            'shipped' => 'shipped',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'expired' => 'expired',
        ]);
        CRUD::field('payment_status')->type('select_from_array')->options([
            'pending' => 'pending',
            'paid' => 'paid',
            'failed' => 'failed',
            'expired' => 'expired',
            'refunded' => 'refunded',
        ]);
        CRUD::field('shipping_status')->type('select_from_array')->options([
            'pending' => 'pending',
            'processing' => 'processing',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
        ]);
        CRUD::field('expires_at')->type('datetime');
        CRUD::field('notes')->type('textarea');
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

    public function markPaid(int $id): RedirectResponse
    {
        $order = Order::query()->with('payments')->findOrFail($id);
        $latestPayment = $order->payments()->latest('id')->first();

        $payload = [
            'status' => Payment::STATUS_PAID,
            'reference_number' => $latestPayment?->reference_number,
            'provider' => $latestPayment?->provider ?? 'manual_admin',
            'method' => $latestPayment?->method ?? 'gcash',
            'amount' => $latestPayment?->amount ?? (float) $order->total,
            'metadata' => [
                'source' => 'admin_quick_mark_paid',
            ],
        ];

        try {
            app(OrderService::class)->applyPaymentUpdate($order, $payload);
            Alert::success('Order payment marked as paid. Stock finalized and order status updated.')->flash();
        } catch (InsufficientStockException $exception) {
            Alert::error('Unable to mark as paid: ' . $exception->getMessage())->flash();
        } catch (\Throwable $exception) {
            Alert::error('Unable to mark as paid right now. Please try again.')->flash();
        }

        return redirect()->back();
    }
}
