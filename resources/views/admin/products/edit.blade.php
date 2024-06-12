@extends('admin.layouts.app')
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Product</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <form action="post" id="editProductForm" name="editProductForm">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="title">Title</label>
                                            <input type="text" name="title" id="title" class="form-control"
                                                placeholder="Title..." value="{{ $product->title }}">
                                            <p class="error"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="slug">Slug</label>
                                            <input type="text" name="slug" id="slug" class="form-control"
                                                placeholder="Slug..." readonly value="{{ $product->slug }}">
                                            <p class="error"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" cols="30" rows="10" class="summernote"
                                                placeholder="Description..." value="{{ $product->description }}"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Media</h2>
                                <div id="image" class="dropzone dz-clickable">
                                    <div class="dz-message needsclick">
                                        <br>Drop files here or click to upload.<br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="product_image">
                            @if ($productImages->isNotEmpty())
                                @foreach ($productImages as $productImage)
                                    <div class="col-md-3" id = "image-row-{{ $productImage->id }}">
                                        <input type="hidden" name="image_array[]" value="{{ $productImage->id }}">
                                        <div class="card">
                                            <img src="{{ asset('uploads/products/smallImage/' . $productImage->image) }}"
                                                class="card-img-top" alt="">
                                            <div class="card-body">
                                                <a href="javascript:void(0)"
                                                    onClick = "deleteImage({{ $productImage->id }})"
                                                    class="btn btn-danger">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Pricing</h2>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="price">Price</label>
                                            <input type="number" name="price" id="price" class="form-control"
                                                placeholder="Price" value="{{ $product->price }}">
                                            <p class="error"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="compare_price">Compare at Price</label>
                                            <input type="text" name="compare_price" id="compare_price"
                                                class="form-control" placeholder="Compare Price"
                                                value="{{ $product->compare_price }}">
                                            <p class="text-muted mt-3">
                                                To show a reduced price, move the product’s original price into Compare at
                                                price. Enter a lower value into Price.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Inventory</h2>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sku">SKU (Stock Keeping Unit)</label>
                                            <input type="text" name="sku" id="sku" class="form-control"
                                                placeholder="sku" value="{{ $product->sku }}">
                                            <p class="error"></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="barcode">Barcode</label>
                                            <input type="text" name="barcode" id="barcode" class="form-control"
                                                placeholder="Barcode" value="{{ $product->barcode }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="track_quantity" value="No">
                                                <input class="custom-control-input" type="checkbox" id="track_quantity"
                                                    name="track_quantity" value="Yes"
                                                    value="{{ $product->track_quantity == 'Yes' ? 'checked' : '' }}">
                                                <label for="track_quantity" class="custom-control-label">Track
                                                    Quantity</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <input type="number" min="0" name="quantity" id="quantity"
                                                class="form-control" placeholder="Quantity"
                                                value="{{ $product->quantity }}">
                                            <p class="error"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Product status</h2>
                                <div class="mb-3">
                                    <select name="status" id="status" class="form-control">
                                        <option {{ $product->status == 1 ? 'selected' : '' }} value="1">Active
                                        </option>
                                        <option {{ $product->status == 0 ? 'selected' : '' }} value="0">Block
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h4  mb-3">Product category</h2>
                                <div class="mb-3">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">Secect a category</option>
                                        @if ($categories->isNotEmpty())
                                            @foreach ($categories as $category)
                                                <option {{ $product->category_id == $category->id ? 'selected' : '' }}
                                                    value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="error"></p>
                                </div>
                                <div class="mb-3">
                                    <label for="category">Sub category</label>
                                    <select name="sub_category" id="sub_category" class="form-control">
                                        <option value="">Secect a sub category </option>
                                        @if ($subCategories->isNotEmpty())
                                            @foreach ($subCategories as $subCategory)
                                                <option
                                                    {{ $product->sub_category_id == $subCategory->id ? 'selected' : '' }}
                                                    value="{{ $subCategory->id }}">{{ $subCategory->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Product brand</h2>
                                <div class="mb-3">
                                    <select name="brand_id" id="brand_id" class="form-control">
                                        <option value="">Select a brand</option>
                                        @if ($brands->isNotEmpty())
                                            @foreach ($brands as $brand)
                                                <option {{ $product->brand_id == $brand->id ? 'selected' : '' }}
                                                    value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h2 class="h4 mb-3">Featured product</h2>
                                <div class="mb-3">
                                    <select name="is_featured" id="is_featured" class="form-control">
                                        <option {{ $product->is_featured == 'No' ? 'selected' : '' }} value="No">No
                                        </option>
                                        <option {{ $product->is_featured == 'Yes' ? 'selected' : '' }} value="Yes">Yes
                                        </option>
                                    </select>
                                    <p class="error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </div>
        </form>
        <!-- /.card -->
    </section>

    <!-- /.content -->
@endsection

@section('customjs')
    <script>
        //Form validation using ajax
        $("#editProductForm").submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: "{{ route('products.update', $product->id) }}",
                type: 'put',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response['status'] == true) {

                        $('.error').removeClass("invalid-feedback").html('');
                        $("input[type='text'],input[type='number'],select").removeClass('is-invalid');

                        window.location.href = "{{ route('products.index') }}"

                    } else {
                        var errors = response['errors'];

                        $('.error').removeClass("invalid-feedback").html('');

                        $("input[type='text'],input[type='number'],select").removeClass('is-invalid');

                        $.each(errors, function(key, value) {
                            $(`#${key}`).addClass('is-invalid').siblings('p').addClass(
                                "invalid-feedback").html(value);
                        });
                    }

                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong");
                }
            })
        });


        //Get croduct sub categories
        $("#category").change(function() {
            var category_id = $(this).val();
            $.ajax({
                url: "{{ route('products-sub_categories.index') }}",
                type: 'get',
                data: {
                    category_id: category_id
                },
                dataType: 'json',
                success: function(response) {
                    $('#sub_category').find('option').not(':first').remove();
                    $.each(response['subCategories'], function(key, item) {
                        $('#sub_category').append(
                            `<option value="${item.id}">${item.name}</option>`)
                    });
                },
                error: function(jqXHR, exception) {
                    console.log("Something went wrong");
                }
            })
        });

        //Get slug autometically
        $("#title").change(function() {
            element = $(this);
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: "{{ route('getSlug') }}",
                type: 'get',
                data: {
                    title: element.val()
                },
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response["status"] == true) {
                        $('#slug').val(response["slug"]);
                    }
                }
            });
        });

        //Upload image using dropzone
        Dropzone.autoDiscover = false;
        const dropzone = $("#image").dropzone({
            url: '{{ route('product-images.update') }}',
            maxFiles: 10,
            paramName: 'image',
            params: {
                'product_id': '{{ $product->id }}'
            },
            addRemoveLinks: true,
            acceptedFiles: "image/jpeg,image/jpg,image/png/image/gif,image/jpg",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(file, response) {

                var html = `<div class="col-md-3" id = "image-row-${response.image_id}">
                    <input type="hidden" name="image_array[]" value="${response.image_id}">
                    <div class="card">
                        <img src="${response.imagePath}" class="card-img-top" alt="">
                        <div class="card-body">
                            <a href="javascript:void(0)" onClick = "deleteImage(${response.image_id})" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>`;
                $('#product_image').append(html);
            },
            complete: function(file) {
                this.removeFile(file);
            }
        });

        function deleteImage(id) {
            $("#image-row-" + id).remove();
            if (confirm("Do you want to delete this image?")) {
                $.ajax({
                    url: '{{ route('product-images.destroy') }}',
                    type: 'delete',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        if (status == true) {
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                });
            }
        }
    </script>
@endsection
