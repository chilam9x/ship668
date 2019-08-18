@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Tạo đơn hàng</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Tạo đơn hàng</b> </span>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- SUB CREATE ORDER -->
    <section class="sub-content">
        @if(isset($bookings))
            <script>let isEdit = 1</script>
            {{ Form::open(['url' => 'front-ent/booking/'.$bookings->id, 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
        @else
            <script>let isEdit = 0</script>
            {{ Form::open(['url' => 'front-ent/booking', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        @endif
        <div class="container">
            <div class="row sub-title">
                <div class="col-md-12 col-sm-12">
                    <h3>Người gửi</h3>
                    <div class="line"></div>
                </div>
            </div>
            <div class="row order-form">
                <div class="col-md-12 col-sm-12">
                    <ul>
                        <li>
                            <label>Số điện thoại:</label>
                            <?php
                                $phone_number_fr = '';
                                if (!empty($userLogin->phone_number)) {
                                    $phone_number_fr = $userLogin->phone_number;
                                } else {
                                    $phone_number_fr = old('phone_number_fr', @$bookings->send_phone);
                                }
                            ?>
                            <input name="phone_number_fr" type="text" value="{{ $phone_number_fr }}"
                                   placeholder="Số điện thoại"/>
                            @if ($errors->has('phone_number_fr'))
                                @foreach ($errors->get('phone_number_fr') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Tên người gửi</label>
                            <?php
                                $name_fr = '';
                                if (!empty($userLogin->name)) {
                                    $name_fr = $userLogin->name;
                                } else {
                                    $name_fr = old( 'name_fr', @$bookings->send_name);
                                }
                            ?>
                            <input name="name_fr" type="text" value="{{ $name_fr }}"
                                   placeholder="Tên người gửi"/>
                            @if ($errors->has('name_fr'))
                                @foreach ($errors->get('name_fr') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Địa chỉ</label>
                            <div class="row">
                                <div class="col-lg-12" style="padding-left: 0px !important;">
                                    <div class="form-group" style="margin-bottom: 0 !important;">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#sendAddressModal">
                                            Danh sách gửi
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Tỉnh/Thành phố</label>
                                        <?php
                                            $province_id_fr = '';
                                            if (isset($deliveryAddressDefault) && !empty($deliveryAddressDefault) && !empty($deliveryAddressDefault->province_id)) {
                                                $province_id_fr = $deliveryAddressDefault->province_id;
                                            } else {
                                                $province_id_fr = old('send_province_id', @$bookings->send_province_id);
                                            }
                                        ?>
                                        {{ Form::select('province_id_fr', \App\Models\Province::getProvinceOption() , $province_id_fr,
                                         ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                                        @if ($errors->has('province_id_fr'))
                                            @foreach ($errors->get('province_id_fr') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Quận/Huyện</label>
                                        <select style="width:100% !important;" id="district"
                                                onchange="loadWard(this.value)" name="district_id_fr"
                                                class="form-control">
                                        </select>
                                        @if ($errors->has('district_id_fr'))
                                            @foreach ($errors->get('district_id_fr') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <label></label>
                            <div class="row" style="margin-top: 15px">
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Xã/Phường</label>
                                        <select onchange="searchPrice()" style="width:100% !important;" id="ward"
                                                name="ward_id_fr"
                                                class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Số nhà / tên đường</label>
                                        <?php
                                            $home_number_fr = '';
                                            if (isset($deliveryAddressDefault) && !empty($deliveryAddressDefault) && !empty($deliveryAddressDefault->home_number)) {
                                                $home_number_fr = $deliveryAddressDefault->home_number;
                                            } else {
                                                $home_number_fr = old( 'home_number_fr', @$bookings->send_homenumber);
                                            }
                                        ?>
                                        <input onchange="searchPrice()"
                                               style="padding: 6px 10px; width: 100% !important;" name="home_number_fr"
                                               class="form-control spinner"
                                               value="{!! $home_number_fr !!}" type="text"
                                               placeholder="Nhập số nhà">
                                        @if ($errors->has('home_number_fr'))
                                            @foreach ($errors->get('home_number_fr') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li>
                            <label>Hình thức gửi hàng:</label>
                            <div class="radio">
                                <p>
                                    <input onclick="searchPrice()" class="w3-radio" type="radio" value="1"
                                           @if(old('receive_type') ==  1) checked="checked" @endif name="receive_type"
                                           placeholder="Lấy hàng tại nhà" checked>
                                    <label>Lấy hàng tại nhà</label>
                                </p>
                                <p>
                                    <input onclick="searchPrice()" class="w3-radio" type="radio" value="2"
                                           @if(old('receive_type') ==  2) checked="checked" @endif name="receive_type"
                                           placeholder="Giao hàng đến bưu cục (Giảm 7% cước)">
                                    <label>Giao hàng đến bưu cục (Giảm 7% cước)</label>
                                </p>
                                <p><a onclick="searchAgency()" href="#">Xem địa chỉ bưu cục gân nhất</a></p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row sub-title">
                <div class="col-md-12 col-sm-12">
                    <h3>Người nhận</h3>
                    <div class="line"></div>
                </div>
            </div>
            <div class="row order-form">
                <div class="col-md-12 col-sm-12">
                    <ul>
                        <li>
                            <label>Số điện thoại:</label>
                            <input type="text" id="phone-number-to" name="phone_number_to" placeholder="Số điện thoại"
                                   value="{{ old( 'phone_number_to', @$bookings->receive_phone) }}"/>
                            @if ($errors->has('phone_number_to'))
                                @foreach ($errors->get('phone_number_to') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Tên người nhận</label>
                            <input type="text" name="name_to" value="{{ old( 'name_to', @$bookings->receive_name) }}"
                                   placeholder="Tên người nhận"/>
                            @if ($errors->has('name_to'))
                                @foreach ($errors->get('name_to') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Địa chỉ</label>
                            <div class="row">
                                <div class="col-lg-12" style="padding-left: 0px !important;">
                                    <div class="form-group" style="margin-bottom: 0 !important;">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#receiveAddressModal">
                                            Danh sách nhận
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Tỉnh/Thành phố</label>
                                        {{ Form::select('province_id_to', \App\Models\Province::getProvinceOption() , old('province_id_to', @$bookings->receive_province_id),
                                         ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_id_to', 'onchange'=>'loadDistrictAgency()']) }}
                                        @if ($errors->has('province_id_to'))
                                            @foreach ($errors->get('province_id_to') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Quận/Huyện</label>
                                        <select style="width:100% !important;" id="district_to"
                                                onchange="loadWardAgency(this.value)" name="district_id_to"
                                                class="form-control">
                                        </select>
                                        @if ($errors->has('district_id_to'))
                                            @foreach ($errors->get('district_id_to') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <label></label>
                            <div class="row" style="margin-top: 15px">
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Xã/Phường</label>
                                        <select onchange="searchPrice()" style="width:100% !important;" id="ward_to"
                                                name="ward_id_to"
                                                class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Số nhà / tên đường</label>
                                        <input onchange="searchPrice()"
                                               style="padding: 6px 10px; width: 100% !important;" name="home_number_to"
                                               class="form-control spinner" value="{!! old( 'home_number_to', @$bookings->receive_homenumber) !!}"
                                               type="text"
                                               placeholder="Nhập số nhà">
                                        @if ($errors->has('home_number_to'))
                                            @foreach ($errors->get('home_number_to') as $error)
                                                <div style="width: 100%" class="pull-right">
                                                    <span style="color: red;" class="help-block">{!! $error !!}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row sub-title">
                <div class="col-md-12 col-sm-12">
                    <h3>Thông tin đơn hàng</h3>
                    <div class="line"></div>
                </div>
            </div>
            <div class="row order-form">
                <div class="col-md-12 col-sm-12">
                    <ul>
                        <li>
                            <label>Tên hàng hóa:</label>
                            <input type="text" name="name" value="{{ old('name', @$bookings->name) }}" placeholder="Tên hàng hóa"/>
                            @if ($errors->has('name'))
                                @foreach ($errors->get('name') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif

                        </li>
                        <li>
                            <label>Khối lượng:</label>
                            <input type="text" onchange="searchPrice()" name="weight" value="{{ old('weight', @$bookings->weight) }}"
                                   placeholder="Gram"/>
                            @if ($errors->has('weight'))
                                @foreach ($errors->get('weight') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Hình thức gửi hàng:</label>
                            <div class="radio">
                                <!-- <p>
                                    <input onclick="searchPrice()" class="w3-radio" type="radio" name="transport_type"
                                           @if(old('transport_type', @$bookings->transport_type) ==  2) checked="checked" @endif value="2" checked>
                                    <label>Giao tiết kiệm</label>
                                </p> -->
                                <p id="provincial">
                                    <input onclick="searchPrice()" class="w3-radio" type="radio" id="transport-type1" name="transport_type"
                                           @if(old('transport_type', @$bookings->transport_type) ==  1 || old('transport_type', @$bookings->transport_type) !=  3) checked="checked" @endif value="1">
                                    <label>Giao chuẩn <a href="javascript:void(0)" style="margin: 0; float: none" data-toggle="tooltip" title="{{ $transportTypeDes1->description }}">[Chú thích]</a></label>
                                </p>

                                <p>
                                    <input onclick="searchPrice()" class="w3-radio" id="transport-type3" type="radio" name="transport_type"
                                           @if(old('transport_type', @$bookings->transport_type) ==  3) checked="checked" @endif value="3">
                                    <label>Giao siêu tốc <a href="javascript:void(0)" style="margin: 0; float: none" data-toggle="tooltip" title="{{ $transportTypeDes2->description }}">[Chú thích]</a></label>
                                </p>
                               {{-- <p>
                                    <input onclick="checkCOD(this)" class="w3-radio" type="radio" name="transport_type"
                                           @if(old('transport_type') ==  4) checked="checked" @endif value="4">
                                    <label>Giao COD</label>
                                </p>--}}

                                <p style="margin-bottom: 5px; padding-top: 10px">
                                    <i>Dịch vụ cộng thêm:</i>
                                </p>
                                <ul id="transport-type-service" style="padding-left: 20px">
                                    @foreach($transportTypeServices as $item)
                                    <li>
                                        @if($item->key == 'transport_type_service1')
                                            @if(@$bookings->transport_type_service1 == 1)
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" checked="" onchange="searchPrice()"> 
                                            @else
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" onchange="searchPrice()"> 
                                            @endif
                                            <i>{{ $item->name }} <b>(+{{ number_format($item->value) }} VNĐ)</b> <a href="javascript:void(0)" style="margin: 0; float: none" data-toggle="tooltip" title="{{ $item->description }}">[Trích dẫn]</a></i>
                                        @endif
                                        @if($item->key == 'transport_type_service2')
                                            @if(@$bookings->transport_type_service2 == 1)
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" checked="" onchange="searchPrice()"> 
                                            @else
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" onchange="searchPrice()"> 
                                            @endif
                                            <i>{{ $item->name }} <b>(+{{ number_format($item->value) }} VNĐ)</b> <a href="javascript:void(0)" style="margin: 0; float: none" data-toggle="tooltip" title="{{ $item->description }}">[Trích dẫn]</a></i>
                                        @endif
                                        @if($item->key == 'transport_type_service3')
                                            @if(@$bookings->transport_type_service3 == 1)
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" checked="" onchange="searchPrice()"> 
                                            @else
                                            <input type="checkbox" class="" name="{{ $item->key }}" value="1" onchange="searchPrice()"> 
                                            @endif
                                            <i>{{ $item->name }} <b>(+{{ number_format($item->value) }} VNĐ)</b> <a href="javascript:void(0)" style="margin: 0; float: none" data-toggle="tooltip" title="{{ $item->description }}">[Trích dẫn]</a></i>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                        <li>
                            <label>Số tiền thu hộ:</label>
                            <input type="text" onchange="searchPrice()" id="cod" name="cod" value="{{ old('cod' , isset($bookings) ? @$bookings->COD: 0) }}" placeholder="vnđ"/>
                            @if ($errors->has('cod'))
                                @foreach ($errors->get('cod') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Ghi chú bắt buộc:</label>
                            <select name="payment_type" id="payment-type">
                                <option value="1" {{ old('payment_type', @$bookings->payment_type) == 1 ? 'selected' : '' }}>Người gửi trả cước
                                </option>
                                <option value="2" {{ old('payment_type', @$bookings->payment_type) == 2 ? 'selected' : '' }}>Người nhận trả cước
                                </option>
                            </select>
                        </li>
                        <li>
                            <label>Ghi chú khác:</label>
                            <input type="text" name="other_note" value="{{ old('other_note', @$bookings->other_note) }}">
                        </li>
                        <li>
                            <label>Tổng cước phí:</label>
                            <input type="text" id="price" name="price" value="{{ old('price', isset($bookings) ? @$bookings->price : 0) }}" {{isset($bookings) ? '' : 'readonly'}}
                                   placeholder="vnđ"/>
                            @if ($errors->has('price'))
                                @foreach ($errors->get('price') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label></label>
                            <button {{isset($bookings) ? '' : 'disabled'}} id="myBtn">Tạo đơn hàng</button>
                            <a href="{!! url(isset($bookings) ? '/front-ent/booking/all' : '/') !!}" style="text-transform: none!important; margin-left: 10px"
                               class="btn btn-lg btn-light">Quay lại</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
    <!-- COPYRIGHT -->
    <div class="modal fade" id="searchAgency" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 800px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Danh sách bưu cục gần nhất</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="display table table-bordered dataTable no-footer">
                        <thead>
                        <tr id="title">
                        </tr>
                        </thead>
                        <tbody id="report">
                        </tbody>

                    </table>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="sendAddressModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Danh sách địa chỉ gửi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Chọn địa chỉ gửi</h6>
                    @if (isset($deliveryAddressSend) && count($deliveryAddressSend) > 0)
                        @foreach ($deliveryAddressSend as $address)
                            <label style="display: block">
                                &nbsp;&nbsp;<input type="radio" name="deliveryAddressSend" class="delivery-address-send" id="" ward-id="{{ $address->ward_id }}" district-id="{{ $address->district_id }}" province-id="{{ $address->province_id }}" home-number="{{ $address->home_number }}" value="{{ $address->id }}" @if($address->default == 1) checked="" @endif>
                                {{ $address->home_number }}, {{ $address->wards->name }}, {{ $address->districts->name }}, {{ $address->provinces->name }}
                            </label>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" id="select-delivery-address-send">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="receiveAddressModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Danh sách địa chỉ nhận</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>Chọn địa chỉ nhận</h6>
                    @if (isset($deliveryAddressReceive) && count($deliveryAddressReceive) > 0)
                        @foreach ($deliveryAddressReceive as $address)
                            <label style="display: block">
                                &nbsp;&nbsp;<input type="radio" name="deliveryAddressReceive" class="delivery-address-receive" id="" ward-id-to="{{ $address->ward_id }}" district-id-to="{{ $address->district_id }}" province-id-to="{{ $address->province_id }}" home-number-to="{{ $address->home_number }}" name-to="{{ $address->name }}" phone-to="{{ $address->phone }}" value="{{ $address->id }}" @if($address->default == 1) checked="" @endif>
                                {{ $address->home_number }}, {{ $address->wards->name }}, {{ $address->districts->name }}, {{ $address->provinces->name }}
                            </label>
                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" id="select-delivery-address-receive">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')   
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> 
    <script>
        
        let wardId = '';
        let districtId = '';
        let provinceId = ''; 
        let homeNumber = '';
        let wardIdTo = '';
        let districtIdTo = '';
        let provinceIdTo = '';
        let homeNumberTo = '';
        let nameTo = '';
        let phoneTo = '';
        loadDistrict();
        loadDistrictAgency();

        function checkTransport() {
            var province_fr = $('#province').val();
            var province_to = $('#province_id_to').val();
            var district_fr = $('#district').val();
            var district_to = $('#district_to').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/check_transport/')}}',
                data: {
                    province_fr: province_fr, province_to: province_to,
                    district_fr: district_fr, district_to: district_to
                }
            }).done(function (msg) {
                if (msg == '1') {
                    // $('#provincial').show();
                } else {
                    // $('#provincial').hide();
                }
            });
        }

        function loadDistrict(callback) {
            var province = $('#province').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                $("#district option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == districtId || msg[i]['id'] == '{{@$deliveryAddressDefault->district_id}}' || msg[i]['id'] == '{{@$bookings->send_district_id}}' || msg[i]['id'] == '{{old('district_id_fr')}}') {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if (typeof $('select[name=district_id_fr]').val() !== 'undefined') {
                    loadWard($('select[name=district_id_fr]').val());
                } else if ("{{old('district_id_fr')}}") {
                    loadWard('{{old('district_id_fr')}}');
                } else {
                    loadWard(msg[0]['id']);
                }
                if (callback) {
                    callback();
                }
            });
        }

        function loadDistrictTo(callback) {
            var province = $('#province_id_to').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                $("#district_to option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == districtIdTo || msg[i]['id'] == '{{@$deliveryAddressDefault->district_id}}' || msg[i]['id'] == '{{@$bookings->send_district_id}}' || msg[i]['id'] == '{{old('district_id_to')}}') {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if (typeof $('select[name=district_id_to]').val() !== 'undefined') {
                    loadWardTo($('select[name=district_id_to]').val());
                } else if ("{{old('district_id_to')}}") {
                    loadWardTo('{{old('district_id_to')}}');
                } else {
                    loadWardTo(msg[0]['id']);
                }
                if (callback) {
                    callback();
                }
            });
        }

        function loadWard(id, callback) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                $("#ward option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == wardId || msg[i]['id'] == '{{@$deliveryAddressDefault->ward_id}}' || msg[i]['id'] == '{{@$bookings->send_ward_id}}' || msg[i]['id'] == '{{old('ward_id_fr')}}') {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                checkTransport();
                if (callback) {
                    callback();
                }
            });
        }

        function loadWardTo(id, callback) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                $("#ward_to option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == wardIdTo || msg[i]['id'] == '{{@$deliveryAddressDefault->ward_id}}' || msg[i]['id'] == '{{@$bookings->send_ward_id}}' || msg[i]['id'] == '{{old('ward_id_to')}}') {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                checkTransport();
                if (callback) {
                    callback();
                }
            });
        }

        function loadDistrictAgency(callback, districtId, wardId) {
            var province = $('#province_id_to').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                $("#district_to option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@$bookings->receive_district_id}}' || msg[i]['id'] == districtId ||msg[i]['id'] == '{{old('district_to')}}') {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if (typeof $('select[name=district_id_to]').val() !== 'undefined') {
                    loadWardAgency($('select[name=district_id_to]').val(), function(){}, wardId);
                } else if ("{{old('district_id_to')}}") {
                    loadWardAgency('{{old('district_id_to')}}', function(){}, wardId);
                } else {
                    loadWardAgency(msg[0]['id'], function(){}, wardId);
                }
                if (callback) {
                    callback();
                }
            });
        }

        function loadWardAgency(id, callback, wardId) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                $("#ward_to option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@$bookings->receive_ward_id}}' || (wardId != undefined && msg[i]['id'] == wardId) || msg[i]['id'] == '{{old('ward_id_to')}}') {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                checkTransport();
                if (callback) {
                    callback();
                }
            });
        }

        function searchPrice() {
            var home_number_fr = $('input[name = "home_number_fr"]').val();
            var home_number_to = $('input[name = "home_number_to"]').val();
            $.ajax({
                type: "GET",
                url: '{!! url('/ajax/search_price/') !!}',
                data: {
                    receive_type: $('input[name=receive_type]:checked').val(),
                    transport_type: $('input[name=transport_type]:checked').val(),
                    weight: $('input[name="weight"]').val(),
                    cod: $('input[name="cod"]').val(),
                    transport_type_service1: $('input[name=transport_type_service1]:checked').val(),
                    transport_type_service2: $('input[name=transport_type_service2]:checked').val(),
                    transport_type_service3: $('input[name=transport_type_service3]:checked').val(),

                    province_fr: $('select[name="province_id_fr"]').val(),
                    district_fr: $('select[name="district_id_fr"]').val(),
                    ward_fr: $('#ward').val(),
                    home_number_fr: home_number_fr,

                    province_to: $('select[name="province_id_to"]').val(),
                    district_to: $('select[name="district_id_to"]').val(),
                    ward_to: $('#ward_to').val(),
                    home_number_to: home_number_to,

                    type: 'booking'
                }
            }).done(function (res) {
                if (res > 0){
                    $('#myBtn').removeAttr('disabled');
                }
                $('#price').val(res);
            });
        }

        function searchAgency() {
            $.ajax({
                type: "GET",
                url: '{!! url('/ajax/search_agency/') !!}',
                data: {
                    province_fr: $('select[name="province_id_fr"]').val(),
                    district_fr: $('select[name="district_id_fr"]').val(),
                    ward_fr: $('#ward').val(),
                    home_number_fr:  $('input[name = "home_number_fr"]').val()
                }
            }).done(function (res) {
                $('th').remove();
                $('td').remove();
                title = '';
                title += '<th>Stt</th>';
                $.each(res[0], function (key, value) {
                    if (key == 'distance'){
                        title += '<th> Khoảng cách (km)</th>';
                    }else {
                        title += '<th>' + key + '</th>';
                    }
                });
                var num = 1;
                $.each(res, function (f, data_rep) {
                    $('#report').append("<tr id='" + num + "'></tr>");
                    $.each(data_rep, function (d, value) {
                        $("#" + num + "").append('<td>' + value + '</td>');
                    });
                    $("#" + num + "").prepend("<td>"+num+"</td>");
                    num += 1;
                });
                $('#title').append(title);
                $('#searchAgency').modal('show');
            });
        }

        function getLastBooking() {
            $.ajax({
                type: "GET",
                url: '{!! url('/ajax/get-last-booking/') !!}',
                data: {
                    
                }
            }).done(function (res) {
                setLastBooking(res);
            });
        }

        function setLastBooking(booking) {
            $('input[name="phone_number_to"]').attr('value', booking.receive_phone);
            $('input[name="phone_number_to"]').val(booking.receive_phone);
            $('input[name="name_to"]').attr('value', booking.receive_name);
            $('input[name="name_to"]').val(booking.receive_name);

            $("#province_id_to option").prop("selected",false);
            $("#province_id_to option[value=" + booking.receive_province_id + "]").prop("selected",true);
            loadDistrictAgency(function(){}, booking.receive_district_id, booking.receive_ward_id);
            loadWardAgency(booking.receive_district_id, function(){}, booking.receive_ward_id);
            $('input[name="home_number_to"]').attr('value', booking.receive_homenumber);
            $('input[name="home_number_to"]').val(booking.receive_homenumber);

            $('input[name="name"]').attr('value', booking.name);
            $('input[name="name"]').val(booking.name);
            $('input[name="weight"]').attr('value', booking.weight);
            $('input[name="weight"]').val(booking.weight);
            
            $('input[name="transport_type"]').prop("checked",false);
            if (booking.transport_type == 1) {
                $('#transport-type1').prop("checked", true);
            } else if (booking.transport_type == 3) {
                $('#transport-type3').prop("checked", true);
            }
            if (booking.transport_type_service1 == 1) {
                $('input[name="transport_type_service1"]').prop("checked", true);
            } else {
                $('input[name="transport_type_service1"]').prop("checked", false);
            }
            if (booking.transport_type_service2 == 1) {
                $('input[name="transport_type_service2"]').prop("checked", true);
            } else {
                $('input[name="transport_type_service2"]').prop("checked", false);
            }
            if (booking.transport_type_service3 == 1) {
                $('input[name="transport_type_service3"]').prop("checked", true);
            } else {
                $('input[name="transport_type_service3"]').prop("checked", false);
            }
            $('input[name="cod"]').attr('value', booking.COD);
            $('input[name="cod"]').val(booking.COD);
            $("#payment-type option").prop("selected",false);
            $("#payment-type option[value=" + booking.payment_type + "]").prop("selected",true);

            $('input[name="other_note"]').attr('value', booking.other_note);
            $('input[name="other_note"]').val(booking.other_note);
            $('input[name="price"]').attr('value', booking.price);
            $('input[name="price"]').val(booking.price);
        }

        $(document).ready(function(){
            if (isEdit == 0) {
                getLastBooking();
            }

            $("#district_to").change(function(){
                loadWardAgency($(this).val(), function(){
                    searchPrice();
                });
            });
            $("#district").change(function(){
                loadWard($(this).val(), function(){
                    searchPrice();
                });
            });
            $("#province").change(function(){
                loadDistrict(function(){
                    loadWard($('select[name="district_id_fr"]').val(), function () {
                        searchPrice();
                    });
                });
            });
            $("#province_id_to").change(function(){
                loadDistrictAgency(function(){
                    loadWardAgency($('select[name="district_id_to"]').val(), function () {
                        searchPrice();
                    });
                });
            });
            $('[data-toggle="tooltip"]').tooltip();

            $( "#phone-number-to" ).autocomplete({
                source: function( request, response ) {
                    $.ajax( {
                        url: "{{ url('ajax/get-booking-by-receive') }}",
                        dataType: "json",
                        data: {
                            receive_phone: request.term
                        },
                        success: function( data ) {
                            response( data );
                        }
                    } );
                },
                select: function( event, ui ) {
                    setLastBooking(ui.item);
                    return false;
                }
            })
            .autocomplete( "instance" )._renderItem = function( ul, item ) {
                return $( "<li>" )
                    .append( "<div>Số ĐT: <b>" + item.receive_phone + "</b> (Tên: <b>" + item.receive_name + "</b>)</div>" )
                    .appendTo( ul );
            };
        });

        $('#select-delivery-address-send').click(function(e){
            if ($('.delivery-address-send:checked').length > 0) {
                $( ".delivery-address-send" ).each(function( index ) {
                    if ( $(this).is(':checked') ) { 
                        wardId = $(this).attr('ward-id');
                        districtId = $(this).attr('district-id');
                        provinceId = $(this).attr('province-id'); 
                        homeNumber = $(this).attr('home-number');
                    }
                }); 

                $('#province option:selected').removeAttr("selected");
                $('#province option[value="' + provinceId + '"]').attr("selected", 'selected');
                $('#province').val(provinceId).change(); 

                loadDistrict();
                $('input[name = "home_number_fr"]').val(homeNumber);
            }
            $('#sendAddressModal').modal('hide');
        })

        $('#select-delivery-address-receive').click(function(e){
            if ($('.delivery-address-receive:checked').length > 0) {
                $( ".delivery-address-receive" ).each(function( index ) {
                    if ( $(this).is(':checked') ) { 
                        wardIdTo = $(this).attr('ward-id-to');
                        districtIdTo = $(this).attr('district-id-to');
                        provinceIdTo = $(this).attr('province-id-to'); 
                        homeNumberTo = $(this).attr('home-number-to');
                        nameTo = $(this).attr('name-to');
                        phoneTo = $(this).attr('phone-to');
                    }
                });

                $('#province_id_to option:selected').removeAttr("selected");
                $('#province_id_to option[value="' + provinceIdTo + '"]').attr("selected", 'selected');
                $('#province_id_to').val(provinceIdTo).change(); 

                loadDistrictTo();
                $('input[name = "home_number_to"]').val(homeNumberTo);
                $('input[name = "name_to"]').val(nameTo);
                $('input[name = "phone_number_to"]').val(phoneTo);
            }
            $('#receiveAddressModal').modal('hide');    
        })
    </script>
@endpush
