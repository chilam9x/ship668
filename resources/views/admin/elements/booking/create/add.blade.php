@extends('admin.app')

@section('title')
    Đơn hàng
@endsection

@section('sub-title')
    tạo mới
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Giao diện tạo mới</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {{ Form::open(['url' => 'admin/booking/create-booking', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    <legend>Thông tin khách hàng</legend>
                    <div class="row">
                        <div class="col-lg-6">
                            <legend>Người gửi</legend>
                        </div>
                        <div class="col-lg-6">
                            <legend>Người nhận</legend>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 15px">
                        <div class="{{--has-error--}} form-group">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Họ tên</label>
                                <input class="form-control spinner" value="{{ old( 'name_fr') }}"
                                       name="name_fr" type="text" placeholder="Nhập tên">
                                @if ($errors->has('name_fr'))
                                    @foreach ($errors->get('name_fr') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Họ tên</label>
                                <input class="form-control spinner" value="{{ old( 'name_to') }}"
                                       name="name_to" type="text" placeholder="Nhập tên">
                                @if ($errors->has('name_to'))
                                    @foreach ($errors->get('name_to') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 15px">
                        <div class="{{--has-error--}} form-group">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Số điện thoại</label>
                                <input name="phone_number_fr"
                                       value="{{ old( 'phone_number_fr') }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập số điện thoại">
                                @if ($errors->has('phone_number_fr'))
                                    @foreach ($errors->get('phone_number_fr') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Số điện thoại</label>
                                <input name="phone_number_to"
                                       value="{{ old( 'phone_number_to') }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập số điện thoại">
                                @if ($errors->has('phone_number_to'))
                                    @foreach ($errors->get('phone_number_to') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <label>Tỉnh/Thành phố</label>
                            {{ Form::select('province_id_fr', \App\Models\Province::getProvinceOption() , old('province_id_fr'), ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_fr', 'onchange'=>'loadDistrictFrom()']) }}
                            @if (isset($errors) && $errors->has('province_id_fr'))
                                @foreach ($errors->get('province_id_fr') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                            {{--<select onchange="loadDistrictFrom()" id="province_fr" name="province_id_fr"
                                    class="form-control">
                                @foreach($province as $p)
                                    <option value="{!! $p->id !!}{{ $p->id == old('province_id_fr') ? "selected" : ""  }}">{!! $p->name !!}</option>
                                @endforeach
                            </select>--}}
                        </div>
                        <div class="col-lg-3">
                            <label>Quận/Huyện</label>
                            <select id="district_fr" onchange="loadWardFrom(this.value)"
                                    name="district_id_fr"
                                    class="form-control">
                            </select>
                            @if (isset($errors) && $errors->has('district_id_fr'))
                                @foreach ($errors->get('district_id_fr') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-3">
                            <label>Tỉnh/Thành phố</label>
                            {{ Form::select('province_id_to', \App\Models\Province::getProvinceOption() , old('province_id_to'),
                             ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_to', 'onchange'=>'loadDistrictTo()']) }}
                            @if (isset($errors) && $errors->has('province_id_to'))
                                @foreach ($errors->get('province_id_to') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-3">
                            <label>Quận/Huyện</label>
                            <select id="district_to" onchange="loadWardTo(this.value)"
                                    name="district_id_to"
                                    class="form-control">
                            </select>
                            @if (isset($errors) && $errors->has('district_id_to'))
                                @foreach ($errors->get('district_id_to') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px">
                        <div class="col-lg-3">
                            <label>Xã/Phường</label>
                            <select id="ward_fr" name="ward_id_fr" class="form-control">
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label>Số nhà</label>
                            <input name="home_number_fr" class="form-control spinner" type="text"
                                   value="{{ old('home_number_fr') }}" placeholder="Nhập số nhà">
                            @if ($errors->has('home_number_fr'))
                                @foreach ($errors->get('home_number_fr') as $error)
                                    <span style="color: red"
                                          class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-3">
                            <label>Xã/Phường</label>
                            <select id="ward_to" name="ward_id_to" class="form-control">
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label>Số nhà</label>
                            <input name="home_number_to" class="form-control spinner" type="text" value="{{ old('home_number_to') }}"
                                   placeholder="Nhập số nhà">
                            @if ($errors->has('home_number_to'))
                                @foreach ($errors->get('home_number_to') as $error)
                                    <span style="color: red"
                                          class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <legend style="margin-top: 20px">Thông tin cơ bản</legend>
                    <div class="row" style="margin-bottom: 15px">
                        <div class="form-group">
                            <div class="col-lg-3">
                                <label>Phương thức nhận hàng</label>
                                <select name="receive_type" class="form-control">
                                    <option value="1">Nhận hàng tại nhà</option>
                                    <option value="2">Nhận hàng tại bưu cục</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label>Ghi chú bắt buộc</label>
                                <select name="payment_type" class="form-control">
                                    <option value="1">Người gửi trả cước</option>
                                    <option value="2">Người nhận trả cước</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label>Phương thức vận chuyển</label>
                                <select name="transport_type" class="form-control">
                                    <!-- <option value="2">Giao tiết kiệm</option> -->
                                    <option value="1">Giao chuẩn</option>
                                    <option value="3">Giao siêu tốc</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="control-label" for="inputError">Tiền thu hộ</label>
                                <input id="cod" name="cod" value="{{ old('cod') }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập số tiền thu hộ">
                                @if ($errors->has('cod'))
                                    @foreach ($errors->get('cod') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="row" style="margin-bottom: 15px">
                        <div class="form-group">
                            <div class="col-lg-3">
                                <label class="control-label" for="inputError">Tên đơn hàng</label>
                                <input name="name" value="{{ old('name') }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập tên đơn hàng">
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-3">
                                <label class="control-label" for="inputError">Khối lượng (gram)</label>
                                <input name="weight" value="{{ old('weight') }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập khối lượng">
                                @if ($errors->has('weight'))
                                    @foreach ($errors->get('weight') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-3">
                                <label class="control-label" for="inputError">Ghi chú khác</label>
                                <input class="form-control spinner"
                                       value="{{ old( 'other_note') }}"
                                       name="other_note" type="text">
                            </div>
                            <div class="col-lg-3">
                                <label class="control-label" for="inputError">Giá đơn hàng</label>
                                <input class="form-control spinner"
                                       value="{{ old( 'price') }}"
                                       name="price" type="text">
                                @if ($errors->has('price'))
                                    @foreach ($errors->get('price') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                    </div>
                    <button onclick="this.disabled=true; this.form.submit();" type="submit" class="btn blue">Thực hiện</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        loadDistrictFrom();
        loadDistrictTo();

        /*function checkCOD() {
            var data = $('select[name="transport_type"]').val();
            if (data === '4') {
                $('#cod').removeAttr("readonly");
            } else {
                $('#cod').attr("readonly", "readonly");
            }
        }*/

        function loadDistrictFrom() {
            var province_fr = $('#province_fr').val();
            $("#district_fr option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district')}}/' + province_fr
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@old('district_id_fr')}}') {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ('{{@old('district_id_fr')}}') {
                    loadWardFrom('{{@old('district_id_fr')}}');
                } else {
                    loadWardFrom(msg[0]['id']);
                }
            });
        }

        function loadWardFrom(id) {
            $("#ward_fr option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@old('ward_id_fr')}}') {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }

        function loadDistrictTo() {
            var province_to = $('#province_to').val();
            $("#district_to option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district')}}/' + province_to
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@old('district_id_to')}}') {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ('{{@old('district_id_to')}}') {
                    loadWardTo('{{@old('district_id_to')}}');
                } else {
                    loadWardTo(msg[0]['id']);
                }
            });
        }

        function loadWardTo(id) {
            $("#ward_to option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{@old('ward_id_to')}}') {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }
    </script>
@endpush
