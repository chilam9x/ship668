@extends('admin.app')
@push('css')
<style>
    .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn){
        width: 100% !important;
    }
</style>
@endpush
@section('title')
    Quản lý kho
@endsection

@section('sub-title')
    @if(isset($user))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">@if(isset($user))Giao diện chỉnh sửa @else Giao
                            diện thêm mới @endif</span>
                    </div>
                </div>
                <div class="portlet-body form">

                    @if(isset($user))
                        {{ Form::open(['url' => ['admin/warehouse', @$user->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    @else
                        {{ Form::open(['url' => 'admin/warehouse', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    @endif
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Họ tên</label>
                                <input class="form-control spinner" value="{{ old( 'name', @$user->name) }}"
                                       name="name" type="text" placeholder="Nhập tên">
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label>Đại lý</label><br/>
                                <select style="width: 100%" class="selectpicker" data-show-subtext="true"
                                        data-live-search="true"
                                        name="agency">
                                    @if(isset($agency))
                                        @foreach($agency as $k => $c)
                                            <option value="{!! $c->id !!}" {{ $c->id == @$selected ? 'selected' : '' }}>{!! $c->name !!}</option>

                                        @endforeach
                                    @endif
                                </select>
                                @if ($errors->has('agency'))
                                    @foreach ($errors->get('agency') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label style="margin-bottom: 10px" class="control-label">Tải lên ảnh đại
                                    diện</label>
                                <input type="file" name="avatar" value="{!! @$user->avatar !!}"
                                       id="exampleInputFile">
                                <input type="hidden" name="old_avatar" value="{!! @$user->avatar !!}"
                                       id="oldInputFile">
                                <img style="margin-top: 5px" id="blah" src="#" alt="your image" width="100px"/>
                                @if ($errors->has('avatar'))
                                    @foreach ($errors->get('avatar') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            @if(isset($user))
                                <div class="col-lg-6">
                                    <label>Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Kích
                                            hoạt
                                        </option>
                                        <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>
                                            Chưa kích hoạt
                                        </option>
                                    </select>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Mật khẩu</label>
                                <input class="form-control spinner" value="{!! old('password') !!}"
                                       name="password" type="password" placeholder="Nhập mật khẩu">
                                @if ($errors->has('password'))
                                    @foreach ($errors->get('password') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Nhập lại mật khẩu</label>
                                <input class="form-control spinner" value=""
                                       name="cf-password" type="password" placeholder="Nhập mật khẩu">
                                @if ($errors->has('cf-password'))
                                    @foreach ($errors->get('cf-password') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label">Email</label>
                                <div class="input-group">
                                    <input type="email" value="{{ old('email',@$user->email) }}"
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
                                <label class="control-label">Ngày sinh</label>
                                <input name="birth_day" value="{{ old( 'birth_day', @$user->birth_day) }}"
                                       class="form-control" id="mask_date" type="text"/>
                                <span class="help-block">Năm/Tháng/Ngày</span>
                                @if ($errors->has('birth_day'))
                                    @foreach ($errors->get('birth_day') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Số điện thoại</label>
                                <input name="phone_number"
                                       value="{{ old( 'phone_number', @$user->phone_number) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập số điện thoại">
                                @if ($errors->has('phone_number'))
                                    @foreach ($errors->get('phone_number') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Số CMND</label>
                                <input class="form-control spinner"
                                       value="{{ old( 'id_number', @$user->id_number) }}" name="id_number"
                                       placeholder="Nhập số CMND"
                                       type="number">
                                @if ($errors->has('id_number'))
                                    @foreach ($errors->get('id_number') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$user->province_id),
                                 ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                                @if (isset($errors) && $errors->has('province_id'))
                                    @foreach ($errors->get('province_id') as $error)
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
                                       value="{!! old( 'home_number', @$user->home_number) !!}" type="text"
                                       placeholder="Nhập số nhà">
                                @if ($errors->has('home_number'))
                                    @foreach ($errors->get('home_number') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tên tài khoản ngân hàng</label>
                                <input name="bank_account"
                                       value="{{ old('bank_account',@$user->bank_account) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập tên tài khoản">
                                @if ($errors->has('bank_account'))
                                    @foreach ($errors->get('bank_account') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>

                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Số tài khoản</label>
                                <input name="bank_account_number"
                                       value="{{ old('bank_account_number',@$user->bank_account_number) }}"
                                       class="form-control spinner" type="number"
                                       placeholder="Nhập số tài khoản">
                                @if ($errors->has('bank_account_number'))
                                    @foreach ($errors->get('bank_account_number') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tên ngân hàng</label>
                                <input name="bank_name" value="{{ old('bank_name',@$user->bank_name) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập tên ngân hàng">
                                @if ($errors->has('bank_name'))
                                    @foreach ($errors->get('bank_name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Nhánh ngân hàng</label>
                                <input name="bank_branch" value="{{ old('bank_branch',@$user->bank_branch) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập chi nhánh">
                                @if ($errors->has('bank_branch'))
                                    @foreach ($errors->get('bank_branch') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <?php 
                                    $val = old('work_type', @$user->work_type);
                                ?>
                                <label class="control-label" for="inputError">Hình thức làm việc</label>
                                <div class="form-check">
                                  <label class="form-check-label">
                                    <input type="radio" class="form-check-input" @if($val == 'fulltime') checked @endif name="work_type" value="fulltime"> Toàn thời gian
                                  </label>
                                </div>
                                <div class="form-check">
                                  <label class="form-check-label">
                                    <input type="radio" class="form-check-input" @if($val == 'parttime') checked @endif name="work_type" value="parttime"> Bán thời gian
                                  </label>
                                </div>
                                @if ($errors->has('work_type'))
                                    @foreach ($errors->get('work_type') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/warehouse') }}" type="button" class="btn default">Hủy</a>
                    {!! Form::close() !!}
                </div>
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
