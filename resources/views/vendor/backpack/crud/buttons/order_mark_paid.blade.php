@php
    use App\Models\Payment;
@endphp

@if(($entry->payment_status ?? null) !== Payment::STATUS_PAID)
    <form method="POST" action="{{ route('admin.order.markPaid', ['id' => $entry->id]) }}" style="display:inline-block;" onsubmit="return confirm('Mark this order as PAID and finalize stock?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-success" title="Mark payment as paid">
            <i class="la la-check"></i> Mark Paid
        </button>
    </form>
@endif
