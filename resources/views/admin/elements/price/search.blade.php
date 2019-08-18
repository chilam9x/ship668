@extends('admin.app')

@section('title')
    Giá cước
@endsection

@section('sub-title')
    tra cứu
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Giao diện tra cứu giá cước</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if (\Session::has('data'))
                        <div class="row" style="margin-bottom: 20px">
                            <div class="col-lg-6">
                                <label><b>Kết quả</b></label>
                                <input disabled name="result" class="form-control spinner" type="text"
                                       value="{{ \Session::get('data') }}">
                            </div>
                        </div>
                        <a href="{{ url('admin/search_price') }}" type="reset" class="btn default">reset</a>
                    @else
                        {{ Form::open(['url' => 'admin/search_price', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="row">
                            <div class="form-group">
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
                                    <label>Phương thức nhận hàng</label>
                                    <select name="receive_type" class="form-control">
                                        <option value="1">Nhận hàng tại nhà</option>
                                        <option value="2">Nhận hàng tại bưu cục</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label>Phương thức vận chuyển</label>
                                    <select onclick="checkCOD()" name="transport_type" class="form-control">
                                        <option value="1">Giao chuẩn</option>
                                        <!-- <option value="2">Giao tiết kiệm</option> -->
                                        <option value="3">Giao siêu tốc</option>
                                        <!-- <option value="4">Giao thu COD</option> -->
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="control-label" for="inputError">Tiền thu hộ</label>
                                    <input id="cod" name="cod" readonly value="{{ old('cod') }}"
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
                        <div class="row">
                            <div class="col-lg-6">
                                <h3><b>Từ</b></h3>
                            </div>
                            <div class="col-lg-6">
                                <h3><b>Đến</b></h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id_fr', \App\Models\Province::getProvinceOption() , old('province_id_fr'),
                                 ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_fr', 'onchange'=>'loadDistrictFrom()']) }}
                                @if (isset($errors) && $errors->has('province_id_fr'))
                                    @foreach ($errors->get('province_id_fr') as $error)
                                        <div class="note note-error">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id_to', \App\Models\Province::getProvinceOption() , old('province_id_to'),
                                ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_to', 'onchange'=>'loadDistrictTo()']) }}
                                @if (isset($errors) && $errors->has('province_id_to'))
                                    @foreach ($errors->get('province_id_to') as $error)
                                        <div class="note note-error">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <label>Quận/Huyện</label>
                                <select id="district_fr" onchange="loadWardFrom(this.value)"
                                        name="district_id_fr"
                                        class="form-control">
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label>Quận/Huyện</label>
                                <select id="district_to" onchange="loadWardTo(this.value)"
                                        name="district_id_to"
                                        class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <label>Xã/Phường</label>
                                <select id="ward_fr" name="ward_id_fr" class="form-control">
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label>Xã/Phường</label>
                                <select id="ward_to" name="ward_id_to" class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px; margin-bottom: 20px">
                            <div class="col-lg-6">
                                <label>Số nhà / tên đường</label>
                                <input name="home_number_fr" class="form-control spinner" type="text"
                                       placeholder="Nhập số nhà / tên đường">
                                @if ($errors->has('home_number_fr'))
                                    @foreach ($errors->get('home_number_fr') as $error)
                                        <span style="color: red"
                                              class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label>Số nhà / tên đường</label>
                                <input name="home_number_to" class="form-control spinner" type="text"
                                       placeholder="Nhập số nhà / tên đường">
                                @if ($errors->has('home_number_to'))
                                    @foreach ($errors->get('home_number_to') as $error)
                                        <span style="color: red"
                                              class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <button type="submit" class="btn blue">Thực hiện</button>
                    @endif
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

        function checkCOD() {
            var data = $('select[name="transport_type"]').val();
            if (data === '4') {
                $('#cod').removeAttr("readonly");
            } else {
                $('#cod').attr("readonly", "readonly");
            }
        }

        function loadDistrictFrom() {
            var province_fr = $('#province_fr').val();
            $("#district_fr option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district')}}/' + province_fr
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('district_id_fr')}}') {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ("{{old('district_id_fr')}}") {
                    loadWardFrom('{{old('district_id_fr')}}');
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
                    if (msg[i]['id'] == '{{old('ward_id_fr')}}') {
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
                    if (msg[i]['id'] == '{{old('district_id_to')}}') {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ("{{old('district_id_to')}}") {
                    loadWardTo("{{old('district_id_to')}}");
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
                    if (msg[i]['id'] == '{{old('ward_id_to')}}') {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }
    </script>
@endpush
