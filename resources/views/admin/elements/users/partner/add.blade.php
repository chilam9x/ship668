@extends('admin.app')

@section('title')
    Đối tác
@endsection

@section('sub-title')
    @if(isset($partner))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">@if(isset($partner))Giao diện chỉnh sửa @else
                                Giao
                                diện thêm mới @endif</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if(isset($partner))
                        {{ Form::open(['route' => ['partners.update', $partner->partner_id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    @else
                        {{ Form::open(['url' => '/admin/partners', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    @endif
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="inputError">Tên đối tác</label>
                                <input class="form-control spinner" value="{{ old( 'name', @$partner->name) }}"
                                       name="name" type="text" placeholder="Nhập tên">
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="{{--has-error--}} form-group">
                        <label style="margin-bottom: 10px" class="control-label">Tải lên ảnh đại
                            diện</label>
                        <input type="file" name="avatar" value="{!! @$partner->avatar !!}" id="exampleInputFile">
                        <input type="hidden" name="old_avatar" value="{!! @$partner->avatar !!}" id="oldInputFile">
                        <img style="margin-top: 5px" id="blah" src="#" alt="your image" width="100px"/>
                        @if ($errors->has('avatar'))
                            @foreach ($errors->get('avatar') as $error)
                                <span style="color: red" class="help-block">{!! $error !!}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="{{--has-error--}} form-group">
                    <div class="row">
                        <div class="col-lg-6">
                            <label class="control-label">Email</label>
                            <div class="input-group">
                                <input type="email" value="{{ old('email',@$partner->email) }}"
                                       class="form-control" placeholder="Địa chỉ email"
                                       name="email">
                                <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                            </div>
                            @if ($errors->has('email'))
                                @foreach ($errors->get('email') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <label class="control-label" for="inputError">Số điện thoại</label>
                            <input name="phone_number"
                                   value="{{ old( 'phone_number', @$partner->phone_number) }}"
                                   class="form-control spinner" type="text"
                                   placeholder="Nhập số điện thoại">
                            @if ($errors->has('phone_number'))
                                @foreach ($errors->get('phone_number') as $error)
                                    <span style="color: red" class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row" style="margin-top: 15px">
                        <div class="col-lg-6">
                            <label>Tỉnh/Thành phố</label>
                            {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$partner->province_id),
                                 ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                            @if (isset($errors) && $errors->has('province_id_fr'))
                                @foreach ($errors->get('province_id_fr') as $error)
                                    <div class="note note-error">{{ $error }}</div>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <label>Quận/Huyện</label>
                            <select id="district" onchange="loadWard(this.value)"
                                    name="district_id"
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
                                   value="{!! @$partner->home_number !!}" type="text"
                                   placeholder="Nhập số nhà">
                            @if ($errors->has('home_number'))
                                @foreach ($errors->get('home_number') as $error)
                                    <span style="color: red"
                                          class="help-block">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="{{--has-error--}} form-group">
                        <div class="row" style="margin-top: 10px">
                            <div class="col-lg-12">
                                <label class="control-label" for="inputError">Api</label>
                                <input class="form-control spinner" value="{{ old( 'api_content', @$partner->api_content) }}"
                                       name="api_content" type="text" placeholder="Nhập api của đối tác">
                                @if ($errors->has('api_content'))
                                    @foreach ($errors->get('api_content') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn blue">Thực hiện</button>
                <a href="{{ url('/admin/partners') }}" type="button" class="btn default">Hủy</a>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $("#mask_date").inputmask("y/m/d", {
            autoUnmask: true
        }); //direct mask

        $('#blah').hide();
        loadDistrict();
        if ($('#oldInputFile').val()) {
            $('#blah').attr('src', '{!! url('/') !!}/' + $('#oldInputFile').val());
            $('#blah').show();
        }

        function readURL(input) {
            $('#blah').hide();
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#blah').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#exampleInputFile").change(function () {
            readURL(this);
            $('#blah').show();
        });

        function loadDistrict() {
            var province = $('#province').val();
            $("#district option[value!='-1']").remove();
            $("#scope option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                }
                if ('{{@$partner->district_id}}') {
                    loadWard('{{@$partner->district_id}}');
                } else if("{{old('district_id')}}"){
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
                    if (msg[i]['id'] == '{{@$partner->ward_id}}' || msg[i]['id'] == '{{old('ward_id')}}') {
                        $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }
    </script>
@endpush
