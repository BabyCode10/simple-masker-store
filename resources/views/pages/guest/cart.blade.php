@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="pt-3">
        @include('partials._alerts')
    </div>

    <div class="row pt-3">
        <div class="col-8">
            <form action="{{ url('/cart') }}" method="post" id="cart">
                @csrf
                
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Product</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $no = 0;
                        @endphp
                        @foreach ($carts as $cart)
                        <tr>
                            <th scope="row">{{ ++$no }}</th>
                            <td>{{ $cart->name }}</td>
                            <td>Rp {{ number_format( $cart->price, 0, '.', ',' ) }}</td>
                            <td>
                                <input name="ids[]" type="hidden"value={{ $cart->id }}>
                                <input name="quantities[]" type="number" name="form-control" id="quantity" min="100" value="{{ $cart->quantity }}" step="100">
                            </td>
                            <td>
                                <a href="{{ url( "/cart/{$cart->id}" ) }}" class="btn btn-sm btn-danger rounded-circle">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary" form="cart">Update cart</button>
                </div>
            </form>
        </div>

        <div class="col-4 d-flex flex-column justify-content-between">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Totals</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                
                <tbody>
                    <tr>
                        <td>Sub Total</td>
                        <td>Rp {{ number_format( \Cart::getSubTotal(), 0, ',', '.' ) }}</td>
                    </tr>

                    <tr>
                        <td>Total</td>
                        <td>Rp {{ number_format( \Cart::getTotal(), 0, ',', '.' ) }}</td>
                    </tr>
                </tbody>
            </table>

            <a href="{{ url('/checkout') }}" class="btn btn-secondary">Proceed to checkout</a>
        </div>
    </div>
</div>
@endsection