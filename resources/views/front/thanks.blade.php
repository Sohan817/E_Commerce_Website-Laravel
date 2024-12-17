@extends('front.layouts.app')
@section('content')
    <section class = "container">
        @if (Session::has('Success'))
            <script>
                swal("Message", "{{ Session::get('Success') }}", 'success', {
                    button: true,
                    button: "Ok",
                    timer: 3500
                });
            </script>
        @endif
        <div class = "col-md-12 text-center py-5">
            <h1>Thank You!</h1>
            <p>Your order Id is: {{ $id }}</p>
        </div>
    </section>
@endsection
