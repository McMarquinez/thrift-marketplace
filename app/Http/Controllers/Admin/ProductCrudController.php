<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addClause('withReservedQuantity');

        CRUD::column('sku');
        CRUD::column('name');
        CRUD::addColumn([
            'name' => 'category_id',
            'label' => 'Category',
            'type' => 'select',
            'entity' => 'category',
            'model' => \App\Models\Category::class,
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'name' => 'brand_id',
            'label' => 'Brand',
            'type' => 'select',
            'entity' => 'brand',
            'model' => \App\Models\Brand::class,
            'attribute' => 'name',
        ]);
        CRUD::column('price')->type('number')->prefix('PHP ')->decimals(2);
        CRUD::column('stock_quantity')->type('number')->label('Stock');
        CRUD::addColumn([
            'name' => 'reserved_quantity',
            'label' => 'Reserved',
            'type' => 'model_function',
            'function_name' => 'getReservedQuantityForAdmin',
        ]);
        CRUD::addColumn([
            'name' => 'available_quantity',
            'label' => 'Available',
            'type' => 'model_function',
            'function_name' => 'getAvailableQuantityForAdmin',
        ]);
        CRUD::column('condition');
        CRUD::column('status');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);

        CRUD::field('sku')->hint('Unique inventory identifier. Example: TS-000001');
        CRUD::field('name');
        CRUD::field('slug')->hint('URL slug. Example: vintage-denim-jacket');
        CRUD::field('short_description')->type('textarea');
        CRUD::field('description')->type('textarea');
        CRUD::field('price')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('compare_at_price')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('cost_price')->type('number')->attributes(['step' => '0.01', 'min' => 0]);
        CRUD::field('stock_quantity')->type('number')->attributes(['step' => 1, 'min' => 0]);
        CRUD::field('condition')->type('select_from_array')->options([
            'new' => 'new',
            'like_new' => 'like_new',
            'very_good' => 'very_good',
            'good' => 'good',
            'fair' => 'fair',
        ]);
        CRUD::field('status')->type('select_from_array')->options([
            'draft' => 'draft',
            'published' => 'published',
            'archived' => 'archived',
        ]);
        CRUD::addField([
            'name' => 'category_id',
            'label' => 'Category',
            'type' => 'select',
            'entity' => 'category',
            'model' => \App\Models\Category::class,
            'attribute' => 'name',
        ]);
        CRUD::addField([
            'name' => 'brand_id',
            'label' => 'Brand',
            'type' => 'select',
            'entity' => 'brand',
            'model' => \App\Models\Brand::class,
            'attribute' => 'name',
        ]);
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
