<?php

use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\DiscountCouponController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

//Email routes
// Route::get('/test', function () {
//     orderEmail(46);
// });

//Frontend routes
Route::get('/', [FrontController::class, 'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('front.shop');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('front.product');
Route::get('cart', [CartController::class, 'cart'])->name('front.cart');
Route::post('add-to-cart', [CartController::class, 'addToCart'])->name('front.addToCart');
Route::post('update_cart', [CartController::class, 'updateCart'])->name('front.update_cart');
Route::post('delete_item', [CartController::class, 'deleteItem'])->name('front.delete_item');
Route::get('/checkout', [CartController::class, 'checkout'])->name('front.checkout');
Route::post('/process_checkout', [CartController::class, 'processCheckout'])->name('front.process_checkout');
Route::post('/get-order-summary', [CartController::class, 'getOrderSummary'])->name('front.getOrderSummary');
Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('front.apply-discount');
Route::post('/remove-discount', [CartController::class, 'removeCoupon'])->name('front.remove-discount');
Route::post('/add-to-wishlist', [FrontController::class, 'addToWishlist'])->name('front.addToWishlist');
Route::get('/thanks/{orderId}', [CartController::class, 'thankYou'])->name('front.thanks');
Route::get('/pages/{slug}', [FrontController::class, 'page'])->name('front.pages');
Route::post('/send-contact-email', [FrontController::class, 'sendContactEmail'])->name('front.sendContactEmail');



//User Auth routes
Route::group(['prefix' => 'user'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [AuthController::class, 'register'])->name('user_account.register');
        Route::post('/register-process', [AuthController::class, 'registerProcess'])->name('user_account.registerProcess');
        Route::get('/login', [AuthController::class, 'login'])->name('user_account.login');
        Route::post('/login-process', [AuthController::class, 'authenticate'])->name('user_account.loginProcess');
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('user_account.profile');
        Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('user_account.updateProfile');
        Route::post('/update-profile-address', [AuthController::class, 'updateProfileAddress'])->name('user_account.updateProfileAddress');
        Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('user_account.showChangePassword');
        Route::post('/process-change-password', [AuthController::class, 'changePassword'])->name('user_account.changePassword');
        Route::get('/my-orders', [AuthController::class, 'orders'])->name('user_account.orders');
        Route::get('/order-detail/{orderId}', [AuthController::class, 'orderDetail'])->name('user_account.order-detail');
        Route::get('/logout', [AuthController::class, 'logout'])->name('user_account.logout');
        Route::get('/my-wishlist', [AuthController::class, 'wishlist'])->name('user_account.wishlist');
        Route::post('/remove-from-wishlist', [AuthController::class, 'removeFromWishlist'])->name('user_account.removeFromWishlist');
    });
});

//Admin auth routes
Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => 'admin.guest'], function () {
        Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });
    Route::group(['middleware' => 'admin.auth'], function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');
        //Category Routes
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');
        Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');

        //Sub category routes
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');

        //Brand Routes
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');

        //Product Routes
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products-sub_categories', [ProductSubCategoryController::class, 'index'])->name('products-sub_categories.index');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('/product-images/update', [ProductImageController::class, 'update'])->name('product-images.update');
        Route::delete('/product-images', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');
        Route::get('/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');

        //Shipping routes
        Route::get('/shipping/create', [ShippingController::class, 'create'])->name('shipping.create');
        Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
        Route::get('/shipping/{id}', [ShippingController::class, 'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}', [ShippingController::class, 'update'])->name('shipping.update');
        Route::delete('/shipping/{id}', [ShippingController::class, 'destroy'])->name('shipping.delete');

        //Discount Coupon code Routes
        Route::get('/coupon', [DiscountCouponController::class, 'index'])->name('coupon.index');
        Route::get('/coupon/create', [DiscountCouponController::class, 'create'])->name('coupon.create');
        Route::post('/coupon', [DiscountCouponController::class, 'store'])->name('coupon.store');
        Route::get('/coupon/{id}', [DiscountCouponController::class, 'edit'])->name('coupon.edit');
        Route::put('/coupon/{id}', [DiscountCouponController::class, 'update'])->name('coupon.update');
        Route::delete('/coupon/{id}', [DiscountCouponController::class, 'destroy'])->name('coupon.delete');

        //Order routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('orders.detail');
        Route::post('/order/change-status/{id}', [OrderController::class, 'changeOrderStatus'])->name('orders.changeOrderStatus');
        Route::post('/order/send-email/{id}', [OrderController::class, 'sendEnvoiceEmail'])->name('orders.sendEnvoiceEmail');

        //User Routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.delete');

        //Page Routes
        Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('pages/store', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.delete');

        //Settings Route
        Route::get('/show-change-password', [SettingController::class, 'showChangePassword'])->name('admin.showChangePassword');
        Route::post('/change-password', [SettingController::class, 'changePassword'])->name('admin.changePassword');

        Route::get('/getSlug', function (Request $request) {
            $slug = '';
            if (!empty($request->title)) {
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' =>  $slug
            ]);
        })->name('getSlug');
    });
});
