<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentWebhookRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function __invoke(PaymentWebhookRequest $request): JsonResponse
    {
        $order = Order::query()
            ->where('order_number', $request->string('order_number')->toString())
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        try {
            $updatedOrder = $this->orderService->applyPaymentUpdate($order, $request->validated(), true);

            return response()->json([
                'message' => 'Webhook processed.',
                'data' => (new OrderResource($updatedOrder))->resolve(),
            ]);
        } catch (InsufficientStockException $exception) {
            return response()->json([
                'message' => 'Unable to process webhook payment.',
                'error' => $exception->getMessage(),
            ], 422);
        }
    }
}
