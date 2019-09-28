<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::post('/loginPage', 'Admin\User\CollaboratorController@postLogin');
Route::post('/registerPage', 'Admin\User\CollaboratorController@postRegister');
Route::get('/logout', 'Admin\User\CollaboratorController@logout');

Auth::routes();
Route::get('/', function () {
    return view('front-ent.element.index');
});

Route::group(['prefix' => 'front-ent', 'namespace' => 'UI'], function () {
    Route::post('/login', 'UserController@login');
    Route::get('/logout', 'UserController@logout');
    Route::get('/booking/all', 'BookingController@allBooking');
    Route::get('/booking/received', 'BookingController@receivedBooking');
    Route::get('/booking/sent', 'BookingController@sentBooking');
    Route::get('/booking/return', 'BookingController@returnBooking');
    Route::get('/booking/get-cancel', 'BookingController@getCancelBooking');
    Route::get('/booking/cancel/{id}', 'BookingController@cancelBooking');
    Route::resource('/shipper', 'ShipperController');
    Route::resource('/booking', 'BookingController');
    Route::resource('/agency', 'AgencyController');
    Route::post('/feedback', 'SettingController@feedback');
    Route::get('/COD/pending', 'CODController@pendingCOD');
    Route::get('/COD/finish', 'CODController@finishCOD');
    Route::any('/profile', 'UserController@profile');
    Route::get('booking/print/{id}', 'BookingController@printBooking');
    Route::get('print/book-new-talking', 'BookingController@printBookNewTalking');
    Route::get('create-book-by-import', 'BookingController@getCreateByImport');
    Route::post('create-book-by-import', 'BookingController@postCreateByImport');
    // Route::get('export-excel-example-book', 'BookingController@getExportExcelExampleBook');
    Route::get('export-excel-book', 'BookingController@exportBooking');

    Route::get('notifications', 'NotificationController@index');
    Route::get('notifications/detail/{id}', 'NotificationController@detail');

    Route::get('policy', 'SettingController@policy');

    Route::get('total-price', 'WalletController@getTotalPrice');
    Route::get('total-COD', 'WalletController@getTotalCOD');
    Route::get('wallet', 'WalletController@getWallet');
    Route::get('wallet/list-books/{walletId}', 'WalletController@getListBook');
    Route::get('wallet/withdrawal', 'WalletController@withDrawal');
});

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'admin.auth'], function () {
    Route::get('/', function () {
        return redirect('/admin/report');
    });
    Route::get('shippers/list_booking', 'User\ShipperController@exportBooking');
    Route::get('shippers/paid', 'User\ShipperController@paidBooking');
    Route::get('shippers/maps', 'User\ShipperController@maps');
    Route::get('shippers/detail_total_cod/{id}', 'User\ShipperController@getDetailTotalCOD');
    Route::get('shippers/refresh-book/{shipperId}', 'User\ShipperController@refreshBook');
    Route::any('shippers/manage-scope/{shipperId}', 'User\ShipperController@manageScope');
    Route::resource('shippers', 'User\ShipperController');

    Route::resource('collaborators', 'User\CollaboratorController');

    Route::get('agencies/liabilities/{id}', 'User\AgencyController@getLiabilities');
    Route::resource('agencies', 'User\AgencyController');

    Route::get('customers/owe/{id}', 'User\CustomerController@getOwe');
    Route::get('customers/paidAll/{id}', 'User\CustomerController@paidAll');
    Route::get('customers/show_address/{id}', 'User\CustomerController@getDelivery');
    Route::get('customers/delete_delivery/{id}', 'User\CustomerController@deleteDelivery');
    Route::post('customers/export_print_owe/{id}', 'User\CustomerController@exportPrintOwe');
    Route::get('customers/list_booking', 'User\CustomerController@exportBooking');
    Route::get('customers/withdrawal/{customerId}', 'User\CustomerController@withDrawal');

    Route::resource('warehouse', 'User\WareHouseController');
    Route::get('warehouse/list_booking', 'User\WareHouseController@exportBooking');
    Route::get('warehouse/paid', 'User\WareHouseController@paidBooking');
    Route::get('warehouse/maps', 'User\WareHouseController@maps');
    Route::get('warehouse/detail_total_cod/{id}', 'User\WareHouseController@getDetailTotalCOD');
    Route::get('warehouse/refresh-book/{warehouseId}', 'User\WareHouseController@refreshBook');
    Route::any('warehouse/manage-scope/{warehouseId}', 'User\WareHouseController@manageScope');
    //COD
//    Route::get('cod', 'COD\CODController@getCOD');
    Route::get('COD_details/{id}', 'COD\CODController@getCODDetails');
    Route::get('paid_COD/{id}', 'COD\CODController@paidCOD');

    Route::get('search_price', 'Setting\PriceController@searchPrice');
    Route::post('search_price', 'Setting\PriceController@postSearchPrice');
    Route::get('price/{type}/{id}', 'Setting\PriceController@edit');
    Route::put('price/{type}/{id}', 'Setting\PriceController@update');
    Route::resource('price', 'Setting\PriceController');
    Route::resource('discounts', 'Setting\DiscountController');
    Route::resource('customers', 'User\CustomerController');
    Route::resource('customers', 'User\CustomerController');
    Route::resource('partners', 'User\PartnerController');
    Route::resource('district_type', 'District\DistrictTypeController');
    Route::get('feedback', 'Register\ShipperController@getFeedback');
    Route::get('feedback/delete/{id}', 'Register\ShipperController@deleteFeedback');
    Route::resource('versions', 'Setting\VersionController');
    //report route
    Route::resource('report', 'Report\ReportController');
    Route::post('get-chart-booking', 'Report\ReportController@booking_chart');
    Route::post('get-report', 'Report\ReportController@getReport');
    Route::get('export', 'Report\ReportController@reportExport');


    //end report route

    Route::group(['prefix' => 'booking', 'namespace' => 'Booking'], function () {
        Route::get('create', 'BookingController@createBooking');
        Route::post('create-booking', 'BookingController@postCreateBooking');
        Route::get('new', 'BookingController@newBooking');
        Route::get('received', 'BookingController@receiveBooking');
        Route::get('delay', 'BookingController@getDelayBooking');
        Route::get('cancel', 'BookingController@getCancelBooking');
        Route::get('move_booking', 'BookingController@moveBooking');
        Route::post('move', 'BookingController@postMoveBooking');
        Route::get('moved/{id}', 'BookingController@movedBooking');
        Route::get('sent', 'BookingController@getSentBooking');
        Route::get('return', 'BookingController@getReturnBooking');
        Route::get('continued/{cate}/{id}', 'BookingController@continued');
        Route::post('continued/{cate}/{id}', 'BookingController@postContinued');
        Route::get('completed/{category}/{id}', 'BookingController@completed');
        Route::get('delay/{category}/{id}', 'BookingController@delay');
        Route::get('deny/{id}', 'BookingController@deny');
        Route::get('assign/{id}', 'BookingController@assign');
        Route::get('reassign/{category}/{id}', 'BookingController@reAssign');
        Route::get('update/{active}/{id}', 'BookingController@updateBooking');
        Route::get('delete/{active}/{id}', 'BookingController@deleteBooking');
        Route::get('cancel/{active}/{id}', 'BookingController@cancelBooking');
        Route::put('update/{active}/{id}', 'BookingController@postUpdateBooking');
        Route::post('assign/{id}', 'BookingController@postAssign');
        Route::post('reassign/{category}/{id}', 'BookingController@postReAssign');
        Route::get('send_assign/{id}', 'BookingController@sendAssign');
        Route::post('send_assign/{id}', 'BookingController@postSendAssign');
        Route::get('deny_assign/{id}', 'BookingController@denyAssign');
        Route::post('deny_assign/{deny_id}', 'BookingController@postDenyAssign');
        Route::get('print/{type}/{id}', 'BookingController@printBooking');
        Route::get('export', 'ExportBookingController@exportBookingByTime');
        Route::get('exportAdvance', 'ExportBookingController@exportBookingAdvance');
        Route::get('move-to-receive/{bookDeliveryId}', 'BookingController@moveToReceive');
    });

    Route::group(['prefix' => 'import', 'namespace' => 'Setting'], function () {
        Route::post('provincial', 'PriceTaskController@importProvincial');
        Route::post('provincialVip', 'PriceTaskController@importProvincialVip');
        Route::post('provincialPro', 'PriceTaskController@importProvincialPro');
        Route::post('interMunicipal', 'PriceTaskController@importInterMunicipal');
        Route::post('special', 'PriceTaskController@importSpecial');
        Route::post('special_price', 'PriceTaskController@importSpecialPrice');
    });

    Route::group(['prefix' => 'export', 'namespace' => 'Setting'], function () {
        Route::get('provincial', 'PriceTaskController@exportProvincial');
        Route::get('provincialVip', 'PriceTaskController@exportProvincialVip');
        Route::get('provincialPro', 'PriceTaskController@exportProvincialPro');
        Route::get('interMunicipal', 'PriceTaskController@exportInterMunicipal');
        Route::get('special', 'PriceTaskController@exportSpecial');
        Route::get('special_price', 'PriceTaskController@exportSpecialPrice');
    });
    Route::group(['prefix' => 'register', 'namespace' => 'Register'], function () {
        Route::resource('shippers', 'ShipperController');
        Route::resource('agency', 'AgencyController');
    });

    Route::get('policies', 'Setting\PolicyController@index');
    Route::any('policies/add', 'Setting\PolicyController@add');
    Route::any('policies/edit/{id}', 'Setting\PolicyController@edit');
    Route::get('policies/delete/{id}', 'Setting\PolicyController@delete');

    Route::resource('promotions', 'Notification\PromotionController');
    Route::get('notification-handles', 'Notification\NotificationHandleController@index');
    Route::any('notification-handles/add', 'Notification\NotificationHandleController@add');
    Route::any('notification-handles/edit/{id}', 'Notification\NotificationHandleController@edit');
    Route::get('notification-handles/delete/{id}', 'Notification\NotificationHandleController@delete');

    Route::get('wallet/non-payment', 'Wallet\WalletController@getNonPayment');
    Route::get('wallet/paymented', 'Wallet\WalletController@getPaymented');
    Route::get('wallet/update/{walletId}', 'Wallet\WalletController@getUpdate');
    Route::get('wallet/bookings/{walletId}', 'Wallet\WalletController@getBookings');
    Route::get('wallet/export-booking/{walletId}', 'Wallet\WalletController@exportBooking');
    Route::get('wallet/update-status/{walletId}', 'Wallet\WalletController@getUpdateStatus');
    //raymond
    Route::group(['prefix' => 'qrcode'], function () {
        Route::get('/', 'QRCode\QRCodeController@index');
        Route::post('create', 'QRCode\QRCodeController@postCreate');
        Route::post('find', 'QRCode\QRCodeController@find');
        Route::get('print', 'QRCode\QRCodeController@print');
    });
});

Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function () {
    //user
    Route::get('collaborators', 'UserController@getUser');
    Route::get('shipper', 'UserController@getShipper');
    Route::get('warehouse', 'UserController@getWareHouse');
    Route::get('agency', 'UserController@getAgency');
    Route::get('payment_agency', 'SettingController@paymentAgency');
    Route::get('liabilities/{id}', 'UserController@getLiability');
    Route::get('change_liabilities_status/{id}', 'SettingController@turnoverPaid');
    Route::get('customer', 'UserController@getCustomer');
    Route::get('owe_details/{id}', 'UserController@getOweDetails');
    Route::get('change_owe_status/{id}', 'UserController@changeOweStatus');
    Route::get('partner', 'UserController@getPartner');
    //end user route
    //delivery address
    Route::get('delivery_address/{id}', 'DeliveryAddressController@getDelivery');
    Route::post('delivery_address/create/{id}', 'DeliveryAddressController@createDelivery');
    Route::get('delivery_address/default/{id}', 'DeliveryAddressController@seDefaultDelivery');
    Route::get('delivery_address/delete/{id}', 'DeliveryAddressController@deleteDelivery');
    //end delivery address
    // load address
    Route::get('load_data_district/{id}', 'LoadAddressController@loadDataDistrict');
    Route::get('change_type', 'LoadAddressController@changeType');
    Route::get('get_province', 'LoadAddressController@getProvince');
    Route::get('get_district/{id}', 'LoadAddressController@getDistrict');
    Route::get('get_ward/{id}', 'LoadAddressController@getWard');
    Route::get('get_ward_scope', 'LoadAddressController@getWardScope');
    Route::get('check_province', 'LoadAddressController@checkProvince');
    Route::get('change_province', 'LoadAddressController@changeProvinceType');
    Route::get('check_agency', 'BookingController@checkAgency');
    //end load
    //booking
    Route::get('new_booking', 'BookingController@newBooking');
    Route::get('receive_booking', 'BookingController@receiveBooking');
    Route::get('move', 'BookingController@moveBooking');
    Route::get('delay', 'BookingController@delayBooking');
    Route::get('cancel', 'BookingController@cancelBooking');
    Route::get('sent', 'BookingController@sentBooking');
    Route::get('deny_booking', 'BookingController@denyBooking');
    Route::get('change_cod_status/{id}', 'BookingController@changeCODStatus');
    Route::get('remove_booking', 'BookingController@removeBookingByTime');
    //end booking route
    //COD
    Route::get('total_COD', 'CODController@totalCOD');
    Route::get('COD_details/{id}', 'CODController@codDetails');
    //end COD route

    //setting route
    Route::get('discount', 'SettingController@getDiscount');
    //end setting route

    // price route
    Route::get('provincial', 'SettingController@provincial');
    Route::get('provincial-vip', 'SettingController@provincialVip');
    Route::get('provincial-pro', 'SettingController@provincialPro');
    Route::get('interMunicipal', 'SettingController@interMunicipal');
    Route::get('special_inter_municipal', 'SettingController@specialInterMunicipal');
    Route::get('special_price', 'SettingController@specialPrice');
    //end price route

    Route::get('register/shipper', 'RegisterController@shipper');
    Route::get('register/agency', 'RegisterController@agency');
    Route::get('feedback', 'RegisterController@feedback');
    Route::get('version', 'SettingController@version');

    // notification
    Route::get('notifications', 'NotificationController@getNotification');
    Route::get('promotions', 'PromotionController@getPromotion');
    Route::get('notification-handles', 'NotificationController@getNotificationHandle');
    Route::post('add-notification-handle', 'NotificationController@addNotificationHandle');

    Route::get('quick-assign-receive', 'BookingController@getQuickAssignReceive')->name('get_quick_assign_receive');
    Route::post('quick-assign-receive', 'BookingController@postQuickAssignReceive')->name('post_quick_assign_receive');
    Route::get('quick-assign-new', 'BookingController@getQuickAssignNew')->name('get_quick_assign_new');
    Route::post('quick-assign-new', 'BookingController@postQuickAssignNew')->name('post_quick_assign_new');

    Route::get('wallet/non-payment', 'WalletController@getNonPayment');
    Route::get('wallet/paymented', 'WalletController@getPaymented');
    Route::get('wallet/bookings/{walletId}', 'WalletController@getBookings');
    Route::get('wallet/quick-assign', 'WalletController@getQuickAssign')->name('wallet.get_quick_assign');
    Route::post('wallet/quick-assign', 'WalletController@postQuickAssign')->name('wallet.post_quick_assign');

    Route::get('statistical-book-shipper', 'UserController@statisticalBookShipper');

    Route::get('update-allow-booking/{districtId}', 'DistrictController@updateAllowBooking');
});

Route::group(['prefix' => 'ajax', 'namespace' => 'UI\Ajax'], function () {
    Route::get('search_price', 'SettingController@searchPrice');
    Route::get('check_transport', 'SettingController@checkTransport');
    Route::get('search_agency', 'SettingController@searchAgency');
    Route::get('search_booking/{id}', 'SettingController@searchBooking');
    Route::get('maps', 'SettingController@shipperLocation');
    Route::get('provinces', 'SettingController@getProvince');
    Route::get('districts', 'SettingController@getDistrict');
    Route::get('wards', 'SettingController@getWard');
    Route::get('get-booking-by-receive', 'SettingController@getBookingByReceiver');
    Route::get('get-last-booking', 'SettingController@getLastBooking');
});
