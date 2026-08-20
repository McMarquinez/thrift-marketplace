<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return [
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'shipping_status' => $this->shipping_status,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],
            'shipping_address' => $this->shipping_address,
            'pricing' => [
                'subtotal' => (float) $this->subtotal,
                'shipping_fee' => (float) $this->shipping_fee,
                'discount_amount' => (float) $this->discount_amount,
                'total' => (float) $this->total,
            ],
            'expires_at' => optional($this->expires_at)?->toISOString(),
            'paid_at' => optional($this->paid_at)?->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => $latestPayment ? [
                'reference_number' => $latestPayment->reference_number,
                'provider' => $latestPayment->provider,
                'method' => $latestPayment->method,
                'amount' => (float) $latestPayment->amount,
                'status' => $latestPayment->status,
                'paid_at' => optional($latestPayment->paid_at)?->toISOString(),
            ] : null,
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
