<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class StorefrontCheckoutController extends Controller
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
}
