<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"
    integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@if (Session::has('Success'))
    <script>
        swal("Message", "{{ Session::get('Success') }}", 'success', {
            button: true,
            button: "Ok",
            timer: 3500
        });
    </script>
@endif
@if (Session::has('Fail'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" area-hidden = "true">x</button>
        <h4 <i class="icon fa fa-ban"></i> Error!</h4> {{ Session::get('Fail') }}
    </div>
@endif
