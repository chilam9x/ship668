@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Trang cá nhân</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Thông tin cá nhân</b> </span>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- SUB CREATE ORDER -->
    <section class="sub-content" style="padding: 5px 0 50px 0 !important;">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <!-- profile -->
                <div class="row sub-title">
                    <div class="col-md-12 col-sm-12">
                        <h3>Thông tin chung</h3>
                        <div class="line"></div>
                    </div>
                </div>
                <div class="row order-form">
                    <div class="col-md-12 col-sm-12">
                        {{ Form::open(['url' => 'front-ent/profile', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <ul>
                            <li>
                                <label>Tên:</label>
                                <input name="name" type="text" value="{{ old('name', Auth::user()->name) }}"
                                       placeholder="Tên"/>
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Email:</label>
                                <input name="email" type="text" value="{{ old( 'email', Auth::user()->email) }}"
                                       placeholder="Email"/>
                                @if ($errors->has('email'))
                                    @foreach ($errors->get('email') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Số điện thoại:</label>
                                <input name="phone_number" type="text" value="{{ old( 'phone_number', Auth::user()->phone_number) }}"
                                       placeholder="Số điện thoại"/>
                                @if ($errors->has('phone_number'))
                                    @foreach ($errors->get('phone_number') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Tên tài khoản:</label>
                                <input name="bank_account" type="text" value="{{ old( 'bank_account', Auth::user()->bank_account) }}"
                                       placeholder="Tên tài khoản ngân hàng"/>
                                @if ($errors->has('bank_account'))
                                    @foreach ($errors->get('bank_account') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Số tài khoản:</label>
                                <input name="bank_account_number" type="text" @if(!empty(Auth::user()->bank_account_number)) disabled @endif value="{{ old( 'bank_account_number', Auth::user()->bank_account_number) }}"
                                       placeholder="Số tài khoản ngân hàng"/>
                                @if (!empty(Auth::user()->bank_account_number))
                                <div style="width: 70%" class="pull-right">
                                    <span class="text-success">*Liên hệ admin để thay đổi số tài khoản</span>
                                </div>
                                @endif
                                @if ($errors->has('bank_account_number'))
                                    @foreach ($errors->get('bank_account_number') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Tên ngân hàng:</label>
                                <input name="bank_name" type="text" value="{{ old( 'bank_name', Auth::user()->bank_name) }}"
                                       placeholder="Tên ngân hàng"/>
                                @if ($errors->has('bank_name'))
                                    @foreach ($errors->get('bank_name') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Chi nhánh:</label>
                                <input name="bank_branch" type="text" value="{{ old( 'bank_branch', Auth::user()->bank_branch) }}"
                                       placeholder="Chi nhánh ngân hàng"/>
                                @if ($errors->has('bank_branch'))
                                    @foreach ($errors->get('bank_branch') as $error)
                                        <div style="width: 70%" class="pull-right">
                                            <span style="color: red;" class="help-block">{!! $error !!}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </li>
                            <li>
                                <label>Tổng COD:</label>
                                <p>{{ number_format(Auth::user()->total_COD) }} VND</p>
                            </li>
                            <li>
                                <label></label>
                                <button type="submit" name="update" value="update" id="">Cập nhật</button>
                            </li>
                        </ul>
                        {!! Form::close() !!}
                    </div>
                </div>
                <!-- end profile -->

                <!-- default address -->
                <div class="row sub-title">
                    <div class="col-md-12 col-sm-12">
                        <h3>Địa chỉ nhận hàng</h3>
                        <div class="line"></div>
                    </div>
                </div>
                <div class="row order-form">
                    <div class="col-md-12 col-sm-12">
                        <label>Danh sách địa chỉ: (chọn để gán địa chỉ chính)</label>
                        <div class="radio">
                            @foreach ( $deliveryAddress as $item )
                            <p>
                                <input onclick="" class="w3-radio set-default-delivery-address" delivery-address-id="{{ $item->id }}" type="radio" value="{{ $item->id }}"
                                       @if($item->default ==  1) checked="checked" @endif name="delivery_address_default" >
                                <label>
                                    {{ $item->home_number }},
                                    {{ $item->ward_name }},
                                    {{ $item->district_name }},
                                    {{ $item->province_name }}
                                </label>
                                @if ($item->default == 0)
                                <button type="button" id="" delivery-address-id="{{ $item->id }}" class="btn btn-secondary btn-sm delete-delivery-address">Xóa</button>
                                @endif
                            </p>
                            @endforeach
                            <button type="button" name="" id="" data-toggle="modal" data-target="#addAddressModal">Thêm địa chỉ</button>
                        </div>
                    </div>
                </div>
                <!-- end default address -->
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->

    <!-- Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Thêm mới địa chỉ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ Form::open(['url' => 'front-ent/profile', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) }}
                        <div class="form-group">
                            <div class="row">
                                <label class="control-label col-sm-4" for="email">Tỉnh/Thành phố:</label>
                                <div class="col-sm-8" style="display: inline-block">
                                    <!-- <input type="email" class="form-control" id="email" placeholder="Enter email" name="email"> -->
                                    {{ Form::select('province_id_add', \App\Models\Province::getProvinceOption() , old('province_id_add'),
                                    ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_id_add', 'onchange'=>'loadDistrictAdd()']) }}
                                </div>
                            </div>
                            <div class="row mt-4">
                                <label class="control-label col-sm-4" for="email">Quận/Huyện:</label>
                                <div class="col-sm-8" style="display: inline-block">
                                    <select style="width:100% !important;" id="district_id_add"
                                            onchange="loadWardAdd(this.value)" name="district_id_add"
                                            class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <label class="control-label col-sm-4" for="email">Xã/Phường:</label>
                                <div class="col-sm-8" style="display: inline-block">
                                    <select onchange="" style="width:100% !important;" id="ward_id_add"
                                            name="ward_id_add"
                                            class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <label class="control-label col-sm-4" for="email">Số nhà/tên đường:</label>
                                <div class="col-sm-8" style="display: inline-block">
                                    <input onchange=""
                                            id="home_number_add" 
                                           style="padding: 6px 10px; width: 100% !important;" name="home_number_add"
                                           class="form-control spinner"
                                           value="{!! old( 'home_number_add') !!}" type="text"
                                           placeholder="Nhập số nhà">
                                    <span id="show-error" class="text-danger" style="display: none">Vui lòng nhập đầy đủ thông tin!</span>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <label class="control-label col-sm-4" for="email">Địa chỉ chính:</label>
                                <div class="col-sm-8" style="display: inline-block">
                                    <label><input type="checkbox" id="default-address" name="default_address" value="{!! old( 'home_number_add', 1) !!}" >
                                    Chọn làm địa chỉ chính</label>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save-delivery-address">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        loadDistrictAdd();

        function loadDistrictAdd(callback) {
            var province = $('#province_id_add').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                $("#district_id_add option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('district_id_add')}}') {
                        $('select[name="district_id_add"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_add"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if (typeof $('select[name=district_id_add]').val() !== 'undefined') {
                    loadWardAdd($('select[name=district_id_add]').val());
                } else if ("{{old('district_id_add')}}") {
                    loadWardAdd('{{old('district_id_add')}}');
                } else {
                    loadWardAdd(msg[0]['id']);
                }
                if (callback) {
                    callback();
                }
            });
        }

        function loadWardAdd(id, callback) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                $("#ward_id_add option[value!='-1']").remove();
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('ward_id_add')}}') {
                        $('select[name="ward_id_add"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_add"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if (callback) {
                    callback();
                }
            });
        }

        $(document).ready(function(){
            $("#district_id_add").change(function(){
                loadWardAdd($(this).val(), function(){
                    // searchPrice();
                });
            });
            $("#province_id_add").change(function(){
                loadDistrictAdd(function(){
                    loadWardAdd($('select[name="district_id_add"]').val(), function () {
                        // searchPrice();
                    });
                });
            });

            $('#save-delivery-address').click(function(e){
                var provinceIdAdd = $('#province_id_add').val();
                var districtIdAdd = $('#district_id_add').val();
                var wardIdAdd = $('#ward_id_add').val();
                var homeNumberAdd = $('#home_number_add').val();
                var defaultAddress = $('#default-address:checked').val();
                var token = $('input[name = _token]').val();
                if (homeNumberAdd.trim().length == 0) {
                    $('#show-error').show();
                    return false;
                } else {
                    $('#show-error').hide();
                }
                $.ajax({
                    type: "post",
                    url: '{!! url("/ajax/delivery_address/create/" . Auth::user()->id) !!}',
                    data: {
                        province: provinceIdAdd,
                        district: districtIdAdd,
                        ward: wardIdAdd,
                        home_number: homeNumberAdd,
                        default: defaultAddress,
                        _token: token
                    }
                }).done(function (res) {
                    if ( res == 'success' ) {
                        $('#addAddressModal').modal('hide');
                        location.reload();
                    }
                });
            });

            $('.delete-delivery-address').click(function(e){
                var id = $(this).attr('delivery-address-id');
                $.ajax({
                    type: "get",
                    url: '{!! url("/ajax/delivery_address/delete") !!}/' + id,
                }).done(function (res) {
                    if ( res == 'success' ) {
                        location.reload();
                    }
                })
            })

            $('.set-default-delivery-address').click(function(e){
                var id = $(this).attr('delivery-address-id');
                $.ajax({
                    type: "get",
                    url: '{!! url("/ajax/delivery_address/default") !!}/' + id,
                }).done(function (res) {
                    if ( res == 'success' ) {
                        location.reload();
                    }
                })
            })
        });
    </script>
@endpush
