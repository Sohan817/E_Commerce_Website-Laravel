@extends('admin.layouts.app')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Shipping Management</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('categories.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            @if (Session::has('Success'))
                <div class = "col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! Session::get('Success') !!}
                    </div>
                </div>
            @endif
            <form action="" method="post" id="shippingForm" name="shippingForm">
                <div class="card">
                    <div class="card-body">
                        <div class= "row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <select name="country_id" id="country_id" class="form-control">
                                        <option value="">Select a Country</option>
                                        @if (!empty($countries))
                                            @foreach ($countries as $country)
                                                <option {{ $shippingCharge->country_id == $country->id ? 'selected' : '' }}
                                                    value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                            <option {{ $shippingCharge->country_id == 'rest_of_world' ? 'selected' : '' }}
                                                value="rest_of_world">Rest of the world</option>
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" value="{{ $shippingCharge->amount }}" name="amount" id="amount"
                                    class="form-control" placeholder="Amount">
                                <p></p>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('customjs')
    <script>
        //Form validation using ajax
        $("#shippingForm").submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: "{{ route('shipping.update', $shippingCharge->id) }}",
                type: 'put',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response['status'] == true) {
                        window.location.href = "{{ route('shipping.create') }}"
                    } else {
                        var errors = response['errors'];
                        if (errors['country_id']) {
                            $("#country_id").addClass('is-invalid')
                                .siblings('p')
                                .addClass("invalid-feedback").html(errors['country_id']);
                        } else {
                            $("#country_id").addClass('is-invalid')
                                .siblings('p')
                                .removeClass("invalid-feedback").html('');
                        }
                        if (errors['amount']) {
                            $("#amount").addClass('is-invalid')
                                .siblings('p')
                                .addClass("invalid-feedback").html(errors['amount']);
                        } else {
                            $("#amount").addClass('is-invalid')
                                .siblings('p')
                                .removeClass("invalid-feedback").html('');
                        }
                    }
                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong");
                }
            })
        });
    </script>
@endsection
