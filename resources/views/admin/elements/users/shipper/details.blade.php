@extends('admin.app')

@section('title')
    Shipper
@endsection

@section('sub-title')
    chi tiết
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Giao diện chi tiết</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    <form enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="{{--has-error--}} form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Họ tên</label>
                                    <input class="form-control spinner" value="{{ old( 'name', @$user->name) }}"
                                           disabled name="name" type="text" placeholder="Nhập tên">
                                </div>
                                <div class="col-lg-6">
                                    <label>Đại lý</label>
                                    <select name="agency" class="form-control" disabled>
                                        @if(isset($agency))
                                            @foreach($agency as $c)
                                                <option value="{!! $c->id !!}">{!! $c->name !!}</option>
                                            @endforeach
                                        @else
                                            <option value="-1">Không có đại lý</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="{{--has-error--}} form-group">
                            <label style="margin-bottom: 10px" class="control-label">Tải lên ảnh đại
                                diện</label>
                            <input type="file" name="avatar" id="exampleInputFile" disabled>
                            <input type="hidden" value="{!! @$user->avatar !!}" id="oldInputFile">
                            <img style="margin-top: 5px" id="blah" src="#" alt="your image" width="100px"/>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="control-label">Email</label>
                                    <div class="input-group">
                                        <input type="email" value="{{ old('email',@$user->email) }}" disabled
                                               class="form-control" placeholder="Địa chỉ email"
                                               name="email">
                                        <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="control-label">Ngày sinh</label>
                                    <input name="birth_day" value="{{ old( 'birth_day', @$user->birth_day) }}"
                                           disabled class="form-control" id="mask_date" type="text"/>
                                    <span class="help-block"> Năm/Tháng/Ngày</span>
                                </div>
                            </div>
                        </div>
                        <div class="{{--has-error--}} form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Số điện thoại</label>
                                    <input name="phone_number" disabled
                                           value="{{ old( 'phone_number', @$user->phone_number) }}"
                                           class="form-control spinner" type="text"
                                           placeholder="Nhập số điện thoại">
                                </div>
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Số CMND</label>
                                    <input class="form-control spinner" disabled
                                           value="{{ old( 'id_number', @$user->id_number) }}" name="id_number"
                                           placeholder="Nhập số CMND"
                                           type="number">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-3">
                                    <label>Tỉnh/Thành phố</label>
                                    {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$user->province_id),
                                    ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'disabled' => true, 'onchange'=>'loadDistrict()']) }}
                                    @if (isset($errors) && $errors->has('province_id'))
                                        @foreach ($errors->get('province_id') as $error)
                                            <div class="note note-error">{{ $error }}</div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="col-lg-3">
                                    <label>Quận/Huyện</label>
                                    <select id="district" onchange="loadWard(this.value)" name="district_id" disabled
                                            class="form-control">
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label>Xã/Phường</label>
                                    <select id="ward" name="ward_id" class="form-control" disabled>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label>Số nhà / tên đường</label>
                                    <input name="home_number" value="{{ old('home_number',@$user->home_number) }}"
                                           class="form-control spinner" type="text" disabled
                                           placeholder="Nhập số nhà">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Tên tài khoản ngân hàng</label>
                                    <input name="bank_account"
                                           value="{{ old('bank_account',@$user->bank_account) }}"
                                           class="form-control spinner" type="text" disabled
                                           placeholder="Nhập tên tài khoản">

                                </div>

                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Số tài khoản</label>
                                    <input name="bank_account_number"
                                           value="{{ old('bank_account_number',@$user->bank_account_number) }}"
                                           class="form-control spinner" type="number" disabled
                                           placeholder="Nhập số tài khoản">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Tên ngân hàng</label>
                                    <input name="bank_name" value="{{ old('bank_name',@$user->bank_name) }}"
                                           class="form-control spinner" type="text" disabled
                                           placeholder="Nhập tên ngân hàng">
                                </div>
                                <div class="col-lg-6">
                                    <label class="control-label" for="inputError">Nhánh ngân hàng</label>
                                    <input name="bank_branch" value="{{ old('bank_branch',@$user->bank_branch) }}"
                                           class="form-control spinner" type="text" disabled
                                           placeholder="Nhập chi nhánh">
                                </div>
                            </div>
                        </div>
                        <a href="{{ url('/admin/shippers') }}" type="button" class="btn default">Trở lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
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
