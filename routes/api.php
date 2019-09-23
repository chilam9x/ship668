<?php

use App\Http\Middleware\VerifyJWTToken;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'users', 'namespace' => 'Api', 'middleware' => VerifyJWTToken::class], function () {
    Route::post('updategeneral', 'APIUserController@updateGeneral');
    Route::post('updatebank', 'APIUserController@updateBank');
    Route::post('updatepass', 'APIUserController@updatePass');
    Route::get('getuser', 'APIUserController@getUser');
    // Route::get('logout', 'APIUserController@logout');
    Route::post('logout', 'APIUserController@logout');
    Route::post('adddelivery', 'APIUserController@addDelivery');
    Route::post('updateavatar', 'APIUserController@updateAvatar');
    Route::get('updateaddress/{id}', 'APIUserController@updateAddress');
    Route::delete('removedelivery/{id}', 'APIUserController@removeDelivery');
    Route::get('getaddress', 'APIUserController@getSendOrReceiveAddress');

    Route::post('location', 'LocationController@location');
    Route::post('turn-on-parttime', 'APIUserController@turnOnParttime');
    Route::post('update/device', 'APIUserController@updateDevice');
    Route::get('shipper/get-online', 'APIUserController@getShipperOnline');
});

Route::group(['prefix' => 'users', 'namespace' => 'Api'], function () {
    // Route::get('location', 'LocationController@location');
    Route::post('login', 'APIUserController@login');
    Route::post('login_shipper', 'APIUserController@loginWithPassword');
    Route::post('login_fb', 'APIUserController@loginfb');
    Route::post('login_google', 'APIUserController@loginGG');
    // Route::get('notifications', 'NotificationController@index');
    // Route::get('notifications/detail', 'NotificationController@detail');
    // Route::get('notifications/test', 'NotificationController@test');
    // Route::get('notifications', 'NotificationController@getByUser');
});

Route::group(['prefix' => 'order', 'namespace' => 'API', 'middleware' => VerifyJWTToken::class], function () {
    Route::get('listbook', 'Customer\OrderController@getListBook');
    Route::get('deliveryaddress', 'OrderController@getBooking');
    Route::post('booking', 'OrderController@booking');
    Route::put('updatebook/{id}', 'OrderController@updateBook');
    Route::put('cancelbook/{id}', 'OrderController@cancelBook');
    Route::get('listCOD', 'OrderController@getCOD');
    Route::get('listCOD_details', 'OrderController@getCODDetails');
    Route::get('detail/{id}', 'OrderController@bookDetail');
    Route::get('delete_booking/{id}', 'OrderController@deleteBooking');

    Route::get('total-price', 'WalletController@getTotalPrice');
    Route::get('total-COD', 'WalletController@getTotalCOD');
    Route::get('wallet', 'WalletController@getWallet');
    Route::get('wallet/list-books/{walletId}', 'WalletController@getListBook');
    Route::get('wallet/withdrawal', 'WalletController@withDrawal');
    Route::get('wallet/description', 'WalletController@getWalletDescription');
    Route::get('total-summary', 'WalletController@getTotalSummary');

    Route::group(['prefix' => 'customer'], function () {
        Route::get('last-book', 'Customer\OrderController@lastedBookSender');
        Route::put('updatebook/{id}/other-note', 'Customer\OrderController@updateNote');
        Route::put('deny/{id}', 'Customer\OrderController@RequestReturn');
        //Route::get('listbook', 'Customer\OrderController@getBook');
    });

    Route::group(['prefix' => 'shipper'], function () {
        Route::get('listbook', 'Shipper\OrderController@getListBook');
        Route::post('listbook-wait', 'Shipper\OrderController@getBookShipperWait');

        Route::get('listbook-wait/detail', 'Shipper\OrderController@getBookShipperWaitDetail');
        Route::post('auto-assign', 'OrderController@assignShipperAuto');
        Route::post('auto-assign-single', 'Shipper\OrderController@assignSingleShipperAuto');
        Route::put('update-prioritize', 'OrderController@updatePrioritize');
        Route::put('updatebook/{id}', 'Shipper\OrderController@updateBookShipper');
        Route::put('updatebook/{id}/other-note', 'Shipper\OrderController@updateNote');
        Route::get('detail/{id}', 'OrderController@bookDetailShipper');
        Route::post('upload_image', 'OrderController@uploadImage');
        Route::get('area-scope', 'OrderController@getAreaScope');
        Route::get('area-scope-shipping', 'OrderController@getAreaScopeShipping');
        Route::get('listbook-count', 'Shipper\OrderController@getBookShipperCount');

        Route::put('updatebook/{id}/weight', 'Shipper\OrderController@updateWeightPrice');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationController@getNotification');
        Route::get('/detail', 'NotificationController@getNotificationDetail');
        Route::get('/unread-count', 'NotificationController@getUnreadCount');
        Route::put('/read-all', 'NotificationController@readAll');
    });

    Route::get('count-book', 'OrderController@countBook');
});

Route::group(['prefix' => 'order', 'namespace' => 'API'], function () {
    Route::post('pricing', 'OrderController@pricingBook');
    Route::post('check_province', 'OrderController@checkProvince');
    Route::post('search', 'OrderController@pricingBook');
    Route::get('searchbook/{id}', 'OrderController@searchBook');
    Route::get('listagency', 'OrderController@loadAgency');
});
Route::group(['prefix' => 'place', 'namespace' => 'API'], function () {
    Route::get('province', 'PlaceController@getProvince');
    Route::get('district/{id}', 'PlaceController@getDistrict');
    Route::get('ward/{id}', 'PlaceController@getWard');
    Route::get('agencies', 'PlaceController@getAgency');
});

Route::group(['prefix' => 'setting', 'namespace' => 'API'], function () {
    Route::get('version', 'SettingController@getVersion');
    Route::get('transaction', 'SettingController@getTransaction');
});

Route::group(['prefix' => 'policy', 'namespace' => 'API'], function () {
    Route::get('/', 'PolicyController@getPolicy');
});
//--------RAYMOND------
Route::group(['prefix' => 'qrcode', 'namespace' => 'API'], function () {
    Route::group(['prefix' => 'customer'], function () {
        Route::post('check-qrcode-create-new', 'QRCodeController@checkQRCodeCreateNew');
    });
    Route::group(['prefix' => 'shipper'], function () {
        Route::post('receive-order', 'QRCodeController@receiveOrder');
        Route::post('sender-order', 'QRCodeController@senderOrder');
    });
});
Route::group(['prefix' => 'order', 'namespace' => 'API'], function () {
    Route::group(['prefix' => 'customer'], function () {
        Route::post('create', 'OrderController@create');
    });
});
