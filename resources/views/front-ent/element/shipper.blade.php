@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Đăng ký làm shipper</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Đăng ký làm shipper</b> </span>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- SUB CREATE ORDER -->
    <section class="sub-content">
        {{ Form::open(['url' => 'front-ent/shipper', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        <div class="container">
            <div class="row sub-title">
                <div class="col-md-12 col-sm-12">
                    <h3>Thông tin cá nhân</h3>
                    <div class="line"></div>
                </div>
            </div>
            <div class="row order-form">
                <div class="col-md-12 col-sm-12">
                    <ul>
                        <li>
                            <label>Họ tên:</label>
                            <input name="name" type="text" value="{!! old( 'name') !!}" placeholder="Họ tên"/>
                            @if ($errors->has('name'))
                                @foreach ($errors->get('name') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Ngày sinh</label>
                            <input style="float: left; width: 70%; padding: 10px 10px; !important; " type="date"
                                   name="birth_day"
                                   value="{!! old( 'birth_day', '1980-01-01') !!}" max="2000-12-31" min="1970-01-01"
                                   class="form-control">
                            @if ($errors->has('birth_day'))
                                @foreach ($errors->get('birth_day') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Email:</label>
                            <input name="email" type="text" value="{!! old( 'email') !!}" placeholder="Email"/>
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
                            <input name="phone_number" type="text" value="{!! old( 'phone_number') !!}"
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
                            <label>Số CMND:</label>
                            <input name="id_number" value="{!! old( 'id_number') !!}" type="text"
                                   placeholder="Số CMND"/>
                            @if ($errors->has('id_number'))
                                @foreach ($errors->get('id_number') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label>Địa chỉ</label>
                            <div class="row">
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Tỉnh/Thành phố</label>
                                        {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$user->province_id),
                                         ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Quận/Huyện</label>
                                        <select style="width:100% !important;" id="district"
                                                onchange="loadWard(this.value)" name="district_id"
                                                class="form-control">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <label></label>
                            <div class="row" style="margin-top: 15px">
                                <div class="col-lg-6" style="padding-left: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Xã/Phường</label>
                                        <select style="width:100% !important;" id="ward" name="ward_id"
                                                class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" style="padding-right: 0px !important;">
                                    <div class="form-group">
                                        <label style="width: 100%">Số nhà / tên đường</label>
                                        <input style="padding: 6px 10px; width: 100% !important;" name="home_number" class="form-control spinner"
                                               value="{!! old( 'home_number', @$user->home_number) !!}" type="text"
                                               placeholder="Nhập số nhà">
                                        @if ($errors->has('home_number'))
                                            @foreach ($errors->get('home_number') as $error)
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
                    <h3>Thông tin tài khoản ngân hàng</h3>
                    <div class="line"></div>
                </div>
            </div>
            <div class="row order-form">
                <div class="col-md-12 col-sm-12">
                    <ul>
                        <li>
                            <label>Tên chủ khoản:</label>
                            <input name="bank_account" value="{!! old( 'bank_account') !!}" type="text"
                                   placeholder="Nhập tên chủ khoản"/>
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
                            <input name="bank_account_number" value="{!! old( 'bank_account_number') !!}" type="text"
                                   placeholder="Nhập số tài khoản"/>
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
                            <input name="bank_name" type="text" value="{!! old( 'bank_name') !!}"
                                   placeholder="Nhập tên ngân hàng"/>
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
                            <input name="bank_branch" type="text" value="{!! old( 'bank_branch') !!}"
                                   placeholder="Nhập tên chi nhánh ngân hàng"/>
                            @if ($errors->has('bank_branch'))
                                @foreach ($errors->get('bank_branch') as $error)
                                    <div style="width: 70%" class="pull-right">
                                        <span style="color: red;" class="help-block">{!! $error !!}</span>
                                    </div>
                                @endforeach
                            @endif
                        </li>
                        <li>
                            <label></label>
                            <button id="myBtn">Đăng ký</button>
                            <a href="{!! url('/') !!}" style="text-transform: none!important; margin-left: 10px"
                               class="btn btn-lg btn-light">Quay lại</a>
                            <!-- The Modal -->
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </section>
@endsection
@push('script')
    <script>
        loadDistrict();

        function loadDistrict() {
            var province = $('#province').val();
            $("#district option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@$user->district_id}}' || msg[i]['id'] == '{{old('district_id')}}') {
                        $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ('{{@$user->district_id}}') {
                    loadWard('{{@$user->district_id}}');
                } else if ("{{old('district_id')}}") {
                    loadWard('{{old('district_id')}}');
                } else {
                    loadWard(msg[0]['id']);

                }
            });
        }

        function loadWard(id) {
            $("#ward option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@$user->ward_id}}' || msg[i]['id'] == '{{old('ward_id')}}') {
                        $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });

        }
    </script>
@endpush
