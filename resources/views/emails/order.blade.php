@component('mail::message')
# Thank you. Your order has been received.

Invoice: {{ $order->invoice }} <br>
Bill: Rp {{ number_format( $order->bill->total, 0, '.', ',' ) }} <br>

Order details <br>
@component('mail::table')
| # | Item | Quantity |
| :-: | :- | :-: |
@php
    $no = 0;    
@endphp
@foreach ($order->orderProducts as $product)
| {{ ++$no }} | {{ $product->product->title }} | {{$product->quantity}} |
@endforeach
@endcomponent

@component('mail::button', ['url' => url('/payment')])
Payment Confirmation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent