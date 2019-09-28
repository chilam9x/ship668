@extends('admin.app')

@section('title')
    Shipper
@endsection

@section('sub-title')
    Quản lý khu vực
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-10">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Giao diện chỉnh sửa</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {{ Form::open(['url' => 'admin/shippers/manage-scope/' . $shipperId, 'method' => 'post']) }}
                    
                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-md-12">
                            <h4>Khu vực quản lý hiện tại</h4>
                            <table cellspacing="0">
                                <tr>
                                    <td style="vertical-align: top; padding-bottom: 10px">Tỉnh/TP quản lý:</td>
                                    <td style="vertical-align: top; padding: 0 0 10px 10px">
                                        @foreach($provinceScopes as $province)
                                            {{ $province->name }},
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; padding-bottom: 10px">Quận/Huyện quản lý:</td>
                                    <td style="vertical-align: top; padding: 0 0 10px 10px">
                                        @foreach($districtScopes as $district)
                                            {{ $district->name }},
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align: top; padding-bottom: 10px">Phường/Xã quản lý:</td>
                                    <td style="vertical-align: top; padding: 0 0 10px 10px">
                                        @foreach($wardScopes as $ward)
                                            {{ $ward->name }},
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-md-12">
                            <div class="form-group">
                                <h4>Chọn khu vực</h4>
                                <div class="col-md-12" style="margin-bottom: 10px">
                                    <input type="radio" id="type-keep" name="type" value="1" checked=""> Giữ lại khu vực &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="radio" id="type-new" name="type" value="2"> Chọn mới khu vực
                                </div>
                                <div class="col-lg-4">
                                    <label>Phạm vi quản lý (Tỉnh/Thành phố)</label>
                                    <select id="province_scope" multiple="" name="province_scope[]" class="form-control" style="height: 200px">
                                    </select>
                                    @if ($errors->has('province_scope'))
                                        @foreach ($errors->get('province_scope') as $error)
                                            <span style="color: red" class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="col-lg-4">
                                    <label>Phạm vi quản lý (Quận/Huyện)</label>
                                    <select id="scope" multiple="" name="scope[]" class="form-control" style="height: 200px">
                                    </select>
                                    @if ($errors->has('scope'))
                                        @foreach ($errors->get('scope') as $error)
                                            <span style="color: red" class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="col-lg-4">
                                    <label>Phạm vi quản lý (Phường/Xã)</label>
                                    <select id="ward_scope" multiple="" name="ward_scope[]" class="form-control" style="height: 200px">
                                    </select>
                                    @if ($errors->has('ward_scope'))
                                        @foreach ($errors->get('ward_scope') as $error)
                                            <span style="color: red" class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 20px">
                        <div class="col-md-12">
                            <h4>Cho phép tự động</h4>
                            <input type="checkbox" name="auto_receive" value="1" @if($shipper->auto_receive == 1) checked="" @endif> Cho phép tự lấy hàng &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="checkbox" name="auto_send" value="1" @if($shipper->auto_send == 1) checked="" @endif> Cho phép tự giao hàng
                        </div>
                    </div>

                    <button type="submit" class="btn blue">Thực hiện</button>
                    <!-- <a href="{{ url('/admin/shippers/manage-scope/' . $shipperId) }}" type="button" class="btn default">Làm mới</a> -->
                    <a href="{{ url('/admin/shippers') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    @push('script')
        <script>

            /*function addItem() {
                var countries = [];
                $.each($("#scope option:selected"), function () {
                    countries.push($(this).text());
                });
                $("#item_select").val(countries.join(", "));
            }*/
            let arrProvince = [];
            let arrDistrict = [];
            
            function loadProvince() {
                $.ajax({
                    type: "GET",
                    url: "{{url('/ajax/get_province/')}}",
                }).done(function (msg) {
                    console.log(msg);
                    for (i = 0; i < msg.length; i++) {
                        $('#province_scope').append('<option value="' + msg[i]['id'] + '" onclick="loadDistrict(' + msg[i]['id'] + ')">' + msg[i]['name'] + '</option>')
                    }

                });
            }

            function loadDistrict(provinceId) {
                if ($.inArray(provinceId, arrProvince) < 0) {
                    arrProvince.push(provinceId);

                    $.ajax({
                        type: "GET",
                        url: '{{url('/ajax/get_district/')}}/' + provinceId,
                    }).done(function (msg) {
                        var i;
                        for (i = 0; i < msg.length; i++) {
                            $('#scope').append('<option value="' + msg[i]['id'] + '" onclick="loadWard(' + msg[i]['id'] + ')">' + msg[i]['name'] + '</option>')
                        }
                    });
                }
            }

            function loadWard(districtId) {
                if ($.inArray(districtId, arrDistrict) < 0) {
                    arrDistrict.push(districtId);
                    
                    $.ajax({
                        type: "GET",
                        url: '{{url('/ajax/get_ward/')}}/' + districtId
                    }).done(function (msg) {
                        var i;
                        for (i = 0; i < msg.length; i++) {
                            $('#ward_scope').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                        }
                    });
                }
            }

            $(document).ready(function() {
                if($("#type-keep").is(":checked")) {
                    $('#province_scope').prop('disabled', true);
                    $('#scope').prop('disabled', true);
                    $('#ward_scope').prop('disabled', true);
                }
                $('#type-keep').click(function(e){
                    $('#province_scope').prop('disabled', true);
                    $('#scope').prop('disabled', true);
                    $('#ward_scope').prop('disabled', true);
                    $('#province_scope').empty();
                    $('#scope').empty();
                    $('#ward_scope').empty();
                })
                $('#type-new').click(function(e){
                    $('#province_scope').prop('disabled', false);
                    $('#scope').prop('disabled', false);
                    $('#ward_scope').prop('disabled', false);
                    loadProvince();
                })
            });

        </script>
    @endpush
@endsection
