@extends('front.layouts.app')
@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.shop') }}">Shop</a></li>
                    <li class="breadcrumb-item">Cart</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-9 pt-4">
        <div class="container">
            <div class="row">
                @if (Session::has('Success'))
                    <div class = "col-md-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {!! Session::get('Success') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" area-label = "Close"></button>
                        </div>
                    </div>
                @endif
                @if (Session::has('Fail'))
                    <div class = "col-md-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {!! Session::get('Fail') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" area-label = "Close"></button>
                        </div>
                    </div>
                @endif
                @if (Cart::count() > 0)
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table" id="cart">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($cartContent as $item)
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    @if (!empty($item->options->productImage->image))
                                                        <img
                                                            src="{{ asset('uploads/products/smallImage/' . $item->options->productImage->image) }}">
                                                    @else
                                                        <img class="card-img-top"
                                                            src="{{ asset('admin-assets/img/default-150x150.png') }}" />
                                                    @endif
                                                    <h2>{{ $item->name }}</h2>
                                                </div>
                                            </td>
                                            <td>${{ $item->price }}</td>
                                            <td>
                                                <div class="input-group quantity mx-auto" style="width: 100px;">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-minus p-2 pt-1 pb-1 sub"
                                                            data-id= "{{ $item->rowId }}">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text"
                                                        class="form-control form-control-sm  border-0 text-center"
                                                        value="{{ $item->qty }}">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-plus p-2 pt-1 pb-1 add"
                                                            data-id= "{{ $item->rowId }}">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                ${{ $item->price * $item->qty }}
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-danger"
                                                    onClick="deleteItem('{{ $item->rowId }}');"><i
                                                        class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card cart-summery">
                            <div class="card-body">
                                <div class="sub-title">
                                    <h2 class="bg-white">Cart Summery</h3>
                                </div>
                                <div class="d-flex justify-content-between pb-2">
                                    <div>Subtotal</div>
                                    <div>${{ Cart::subtotal() }}</div>
                                </div>
                                <div class="pt-2">
                                    <a href="{{ route('front.checkout') }}" class="btn-dark btn btn-block w-100">Proceed to
                                        Checkout</a>
                                </div>
                            </div>
                        </div>
                        <div class="input-group apply-coupan mt-4">
                            <input type="text" placeholder="Coupon Code" class="form-control">
                            <button class="btn btn-dark" type="button" id="button-addon2">Apply Coupon</button>
                        </div>
                    </div>
                @else
                    <div class= "col-md-12">
                        <div class= "card">
                            <div class= "card-body d-flex justify-content-center align-items-center">
                                <h4>Your Cart is empty!</h4>
                            </div>
                        </div>
                    </div>
            </div>
            @endif
        </div>
        </div>
    </section>
@endsection

@section('customJs')
    <script>
        $(".add").click(function() {
            var quantityElement = $(this).parent().prev();
            var quantityValue = parseInt(quantityElement.val());
            if (quantityValue < 10) {
                quantityElement.val(quantityValue + 1);
                var rowId = $(this).data('id');
                var newQuantity = quantityElement.val();
                updateCart(rowId, newQuantity);
            }
        });

        $(".sub").click(function() {
            var quantityElement = $(this).parent().next();
            var quantityValue = parseInt(quantityElement.val());
            if (quantityValue > 1) {
                quantityElement.val(quantityValue - 1);
                var rowId = $(this).data('id');
                var newQuantity = quantityElement.val();
                updateCart(rowId, newQuantity);
            }
        });

        function updateCart(rowId, qty) {
            $.ajax({
                url: "{{ route('front.update_cart') }}",
                type: 'post',
                data: {
                    rowId: rowId,
                    qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    window.location.href = '{{ route('front.cart') }}';
                }
            });
        }

        function deleteItem(rowId) {
            if (confirm("Do you want to delete this item?")) {
                $.ajax({
                    url: "{{ route('front.delete_item') }}",
                    type: 'post',
                    data: {
                        rowId: rowId
                    },
                    dataType: 'json',
                    success: function(response) {
                        window.location.href = "{{ route('front.cart') }}";
                    }
                });
            }

        }
    </script>
@endsection
