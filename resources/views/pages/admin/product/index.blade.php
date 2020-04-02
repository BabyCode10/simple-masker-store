@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials._alerts')

    <div class="row">
        <div class="col-md-4">
            <x-sidebar />
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        Product
                        <form class="d-flex justify-content-between align-items-center" action="#">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search Product" aria-label="Search Product" aria-describedby="button-search">

                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="submit" id="button-search">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                            </div>
                            
                            <a href="{{ route('admin.product.create') }}" class="ml-2 btn btn-primary">Add</a>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Title</th>
                                <th scope="col">Price</th>
                                <th scope="col">Stock</th>
                                <th class="text-center" scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no = 0;
                            @endphp

                            @foreach ($products as $product)
                                <tr>
                                    <th scope="row">{{ ++$no }}</th>
                                    <td>{{ $product->title }}</td>
                                    <td>Rp {{ number_format($product->price, 0, '.', ',') }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.product.destroy', $product->id) }}" method="post">
                                            @csrf
                                            @method('delete')

                                            <a href="{{ route('admin.product.edit', $product->id) }}" class="btn btn-sm btn-primary rounded-circle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        
                                            <a href="{{ route('admin.product.show', $product->id) }}" class="btn btn-sm btn-primary rounded-circle">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
