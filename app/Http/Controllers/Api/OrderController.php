<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderPaymentRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Requests\Api\TrackOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201);
        } catch (InsufficientStockException $exception) {
            return response()->json([
                'message' => 'Unable to create order.',
                'error' => $exception->getMessage(),
            ], 422);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Unable to create order.',
                'error' => 'One or more products are invalid.',
            ], 422);
        }
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $order = Order::query()
            ->with(['items', 'payments'])
            ->where('order_number', $orderNumber)
            ->where('customer_email', $validated['email'])
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return (new OrderResource($order))->response();
    }

    public function track(TrackOrderRequest $request): JsonResponse
    {
        $order = Order::query()
            ->with(['items', 'payments'])
            ->where('order_number', $request->string('order_number')->toString())
            ->where('customer_email', $request->string('email')->toString())
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return (new OrderResource($order))->response();
    }

    public function payment(OrderPaymentRequest $request, string $orderNumber): JsonResponse
    {
        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        try {
            $updatedOrder = $this->orderService->applyPaymentUpdate($order, $request->validated());

            return (new OrderResource($updatedOrder))->response();
        } catch (InsufficientStockException $exception) {
            return response()->json([
                'message' => 'Unable to process payment.',
                'error' => $exception->getMessage(),
            ], 422);
        }
    }
}
