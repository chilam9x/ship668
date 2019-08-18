@extends('admin.app')

@section('title')
    Đại lý
@endsection

@section('sub-title')
    @if(isset($agency))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">@if(isset($agency))Giao diện chỉnh sửa @else Giao
                            diện
                            thêm mới @endif</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if(isset($agency))
                        {{ Form::open(['route' => ['agencies.update', $agency->id], 'method' => 'put']) }}
                    @else
                        {{ Form::open(['url' => 'admin/agencies', 'method' => 'post']) }}
                    @endif
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="control-label" for="inputError">Tên đại lý</label>
                                <input class="form-control spinner" value="{!!old( 'name', @$agency->name) !!}"
                                       type="text"
                                       name="name"
                                       placeholder="Nhập tên">
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label class="control-label" for="inputError">Đường dây nóng</label>
                            <input name="phone"
                                   value="{{ old( 'phone', @$agency->phone) }}"
                                   class="form-control spinner" type="text"
                                   placeholder="Nhập số điện thoại">
                            @if ($errors->has('phone'))
                                @foreach ($errors->get('phone') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-4">
                            <label class="control-label" for="inputError">Chiết khấu</label>
                            <input name="discount"
                                   value="{{ old( 'discount', @$agency->discount) }}"
                                   class="form-control spinner" type="text"
                                   placeholder="Nhập giá trị chiết khấu">
                            @if ($errors->has('discount'))
                                @foreach ($errors->get('discount') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$agency->province_id),
                                  ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                                @if (isset($errors) && $errors->has('province_id_fr'))
                                    @foreach ($errors->get('province_id_fr') as $error)
                                        <div class="note note-error">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label>Quận/Huyện</label>
                                <select id="district" onchange="loadWard(this.value)" name="district_id"
                                        class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <label>Xã/Phường</label>
                                <select id="ward" name="ward_id" class="form-control">
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label>Số nhà / tên đường</label>
                                <input name="home_number" class="form-control spinner"
                                       value="{!! old( 'home_number', @$agency->home_number) !!}" type="text"
                                       placeholder="Nhập số nhà">
                                @if ($errors->has('home_number'))
                                    @foreach ($errors->get('home_number') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 20px">
                        <div class="form-group">
                            <div class="col-lg-4">
                                <label>Phạm vi quản lý (Quận/Huyện)</label>
                                <select id="scope" multiple="" name="scope[]" class="form-control">
                                </select>
                                @if ($errors->has('scope'))
                                    @foreach ($errors->get('scope') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-4">
                                <label>Phạm vi quản lý (Phường/Xã)</label>
                                <select id="ward_scope" multiple="" name="ward_scope[]" class="form-control">
                                </select>
                                @if ($errors->has('ward_scope'))
                                    @foreach ($errors->get('ward_scope') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="form-group">
                                <div class="col-lg-4">
                                    <label>Người quản lý</label>&nbsp;<a href="{{ url('/admin/collaborators/create') }}"
                                                                         class="btn btn-circle btn-xs btn-info">Thêm mới</a>
                                    <select id="scope" multiple="" name="collaborator[]" class="form-control">
                                        @foreach($collaborators as $c)
                                            <option value="{!! $c->id !!}" {{ (in_array($c->id, @old('collaborator', $selected_col))) ? "selected" : ""  }}>{!! $c->name !!}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('collaborator'))
                                        @foreach ($errors->get('collaborator') as $error)
                                            <span style="color: red"
                                                  class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/agencies') }}" type="button" class="btn default">Hủy</a>
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
            loadDistrict();
            function loadDistrict() {
                var province = $('#province').val();
                var scope = [];
                var old_scope = [];
                if('{{@$scope}}' != ''){
                    scope = JSON.parse('{{@$scope}}');
                }
                if({!! json_encode(old('scope')) !!} != null){
                    old_scope = {!! json_encode(old('scope')) !!};
                    loadWardScope(old_scope);
                }
                loadWardScope(scope);
                $("#district option[value!='-1']").remove();
                $("#scope option[value!='-1']").remove();
                $.ajax({
                    type: "GET",
                    url: '{{url('/ajax/get_district/')}}/' + province,
                }).done(function (msg) {
                    var i;
                    for (i = 0; i < msg.length; i++) {
                        if (msg[i]['id'] == '{{@$agency->district_id}}' || msg[i]['id'] == '{{old('district_id')}}') {
                            $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                        } else {
                            $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                        }
                        if (scope.indexOf(msg[i]['id']) !== -1 || old_scope.indexOf(msg[i]['id'].toString()) !== -1) {
                            $('#scope').append('<option value="' + msg[i]['id'] + '" selected onclick="loadWardScope([])" >' + msg[i]['name'] + '</option>')
                        } else {
                            $('#scope').append('<option value="' + msg[i]['id'] + '" onclick="loadWardScope([])">' + msg[i]['name'] + '</option>')
                        }
                    }
                    if (typeof $('select[name=district_id]').val() !== 'undefined') {
                        loadWard($('select[name=district_id]').val());
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
                        if (msg[i]['id'] == '{{@$agency->ward_id}}' || msg[i]['id'] == '{{old('ward_id')}}') {
                            $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                        } else {
                            $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                        }
                    }
                });
            }
            function loadWardScope(data) {
                var ward_scope = [];
                var old_ward_scope = [];
                if('{{@$ward_scope}}' != ''){
                    ward_scope = JSON.parse('{{@$ward_scope}}');
                }
                if({!! json_encode(old('ward_scope')) !!} != null){
                    old_ward_scope = {!! json_encode(old('ward_scope')) !!};
                }
                $("#ward_scope option[value!='-1']").remove();
                if(data.length == 0){
                    data = $('#scope').val();
                }
                if(data != null){
                    $.ajax({
                        type: "GET",
                        url: '{{url('/ajax/get_ward_scope/')}}',
                        data: {agency_id : '{{isset($agency)? $agency->id : null}}', data: data}
                    }).done(function (msg) {
                        var i;
                        for (i = 0; i < msg.length; i++) {
                            if (ward_scope.indexOf(msg[i]['id']) !== -1 || old_ward_scope.indexOf(msg[i]['id'].toString()) !== -1) {
                                $('#ward_scope').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                            } else {
                                $('#ward_scope').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                            }
                        }
                    });
                }
            }
        </script>
    @endpush
@endsection
