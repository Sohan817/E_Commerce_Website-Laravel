<!DOCTYPE html>
<html>

<head>
    <title>Order Emailocument</title>
</head>

<body style="font-family: Arial,Helvetica sans-serif;font-size:16px">
    <h1>Thank you!Your order placed successfully</h1>
    <h2>Your order Id is: #{{ $mailData['order']->id }}</h2>
    <h2>Products</h2>
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header pt-3">
                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            <h3 class="h5 mb-3">Shipping Address</h3>
                            <address>
                                <strong>{{ $mailData['order']->first_name . ' ' . $mailData['order']->last_name }}</strong><br>
                                {{ $mailData['order']->address }}</strong><br>
                                {{ $mailData['order']->city }},{{ $mailData['order']->zip }},{{ getCountry($mailData['order']->country_id)->name }}<br>
                                Phone: {{ $mailData['order']->mobile }}<br>
                                Email: {{ $mailData['order']->email }}<br> <br>
                            </address>
                        </div> <br>
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                <b>Invoice #007612</b><br>
                                <br>
                                <b>Total:</b> ${{ number_format($mailData['order']->grand_total, 2) }}<br>
                                <b>Status:</b> <span class="text-success">
                                    @if ($mailData['order']->status == 'pending')
                                        <span class="badge bg-primary">Pending</span>
                                    @elseif($mailData['order']->status == 'shipped')
                                        <span class="badge bg-info">Shipped</span>
                                    @elseif($mailData['order']->status == 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </span>
                                <br>
                            </div>
                        </div>
                    </div><br>
                    <div class="card-body table-responsive p-3">
                        <table cellspacing="3" cellpadding="3" border="0" width="700">
                            <thead>
                                <tr style="background: #ccc">
                                    <th>Product</th>
                                    <th width="100">Price</th>
                                    <th width="100">Qty</th>
                                    <th width="100">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mailData['order']->items as $Item)
                                    <tr>
                                        <td>{{ $Item->product_name }}</td>
                                        <td>${{ number_format($Item->price, 2) }}</td>
                                        <td>{{ $Item->qty }}</td>
                                        <td>${{ number_format($Item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="3" class="text-right" align="right">Subtotal:</th>
                                    <td>${{ number_format($mailData['order']->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right" align="right">
                                        Discount
                                        {{ !empty($mailData['order']->cupon_code) ? '(' . $mailData['order']->cupon_code . ')' : '' }}
                                    </th>
                                    <td>${{ number_format($mailData['order']->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right" align="right">Shipping:</th>
                                    <td>${{ number_format($mailData['order']->shipping, 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-right" align="right">Grand Total:</th>
                                    <td>${{ number_format($mailData['order']->grand_total, 2) }}</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
</body>

</html>
