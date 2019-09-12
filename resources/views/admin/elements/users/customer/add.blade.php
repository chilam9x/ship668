@extends('admin.app')

@section('title')
    Khách hàng
@endsection

@section('sub-title')
    @if(isset($customer))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">@if(isset($customer))Giao diện chỉnh sửa @else
                                Giao
                                diện thêm mới @endif</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if(isset($customer))
                        {{ Form::open(['route' => ['customers.update', $customer->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    @else
                        {{ Form::open(['url' => '/admin/customers', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    @endif
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="inputError">Họ tên</label>
                                <input class="form-control spinner" value="{{ old( 'name', @$customer->name) }}"
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
                        <input type="file" name="avatar" value="{!! @$customer->avatar !!}" id="exampleInputFile">
                        <input type="hidden" name="old_avatar" value="{!! @$customer->avatar !!}" id="oldInputFile">
                        <img style="margin-top: 5px" id="blah" src="#" alt="your image" width="100px"/>
                        @if ($errors->has('avatar'))
                            @foreach ($errors->get('avatar') as $error)
                                <span style="color: red" class="help-block">{!! $error !!}</span>
                            @endforeach
                        @endif
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label">Email</label>
                                <div class="input-group">
                                    <input type="email" value="{{ old('email',@$customer->email) }}"
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
                                <input name="birth_day" value="{{ old( 'birth_day', @$customer->birth_day) }}"
                                       class="form-control" id="mask_date" type="text"/>
                                <span class="help-block"> Năm/Tháng/Ngày</span>
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
                                       value="{{ old( 'phone_number', @$customer->phone_number) }}"
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
                                       value="{{ old( 'id_number', @$customer->id_number) }}" name="id_number"
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
                        @if(isset($customer))
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Địa chỉ chính</label> &nbsp
                                    <a data-target="#addDelivery" onclick="$('#addDelivery').modal('show')"
                                       style="margin-bottom: 5px"
                                       class="btn btn-circle btn-info btn-xs">Thêm mới địa chỉ
                                        giao/nhận hàng</a>
                                    <select id="delivery-address" name="delivery_address"
                                            class="form-control">
                                        @if(!empty($delivery))
                                            @foreach($delivery as $d)
                                                <option value="{!! $d['id'] !!}" {{ ($d['id'] == @$selected) ? "selected" : ""  }}>{!! $d['name'] !!}</option>
                                            @endforeach
                                        @else
                                            <option value="">Không có dữ liệu</option>
                                        @endif
                                    </select>
                                    @if ($errors->has('delivery_address'))
                                        @foreach ($errors->get('delivery_address') as $error)
                                            <span style="color: red" class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="modal fade" id="addDelivery" role="dialog">
                                <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h2 style="font-weight: bold; color: #36c6d3" class="modal-title">Thêm mới
                                                địa chỉ giao/nhận hàng</h2>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row" style="margin-top: 15px">
                                                <div class="col-lg-6">
                                                    <label>Tỉnh/Thành phố</label>
                                                    {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$customer->province_id),
                                                    ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
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
                                                <div id="home_data" class="col-lg-6">
                                                    <label>Số nhà / tên đường</label>
                                                    <input id="home_number" name="home_number"
                                                           class="form-control spinner" type="text"
                                                           placeholder="Nhập số nhà">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" onclick="addDelivery()" class="btn btn-primary"
                                                    data-dismiss="modal">Thực hiện
                                            </button>
                                            <button onclick="location.reload()" type="button"
                                                    class="btn btn-default" data-dismiss="modal">Đóng
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row" style="margin-top: 15px">
                                <div class="col-lg-6">
                                    <label>Tỉnh/Thành phố</label>
                                    {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id', @$customer->province_id),
                                                    ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
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
                                           value="{!! old('home_number', @$customer->home_number) !!}" type="text"
                                           placeholder="Nhập số nhà">
                                    @if ($errors->has('home_number'))
                                        @foreach ($errors->get('home_number') as $error)
                                            <span style="color: red"
                                                  class="help-block">{!! $error !!}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tên tài khoản ngân hàng</label>
                                <input name="bank_account"
                                       value="{{ old('bank_account',@$customer->bank_account) }}"
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
                                       value="{{ old('bank_account_number',@$customer->bank_account_number) }}"
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
                                <input name="bank_name" value="{{ old('bank_name',@$customer->bank_name) }}"
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
                                <input name="bank_branch" value="{{ old('bank_branch',@$customer->bank_branch) }}"
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
                                <label class="control-label" for="inputError">Mã mật khẩu</label>
                                <input name="password_code" value="{{ old('password_code',@$customer->password_code) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập mã mật khẩu">
                                @if ($errors->has('password_code'))
                                    @foreach ($errors->get('password_code') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Loại khách hàng</label>
                                <br><input type="radio" class=""  name="is_vip" value="0" @if(@$customer->is_vip == 0) checked="" @endif> Đặt là khách thường
                                <br><input type="radio" class=""  name="is_vip" value="1" @if(@$customer->is_vip == 1) checked="" @endif> Đặt là khách VIP
                                <br><input type="radio" class=""  name="is_vip" value="2" @if(@$customer->is_vip == 2) checked="" @endif> Đặt là khách Pro
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tạm ứng tiền</label>
                                <br><input type="radio" class=""  name="is_advance_money" value="0" @if(@$customer->is_advance_money == 0) checked="" @endif> Không
                                <br><input type="radio" class=""  name="is_advance_money" value="1" @if(@$customer->is_advance_money == 1) checked="" @endif> Có
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/customers') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
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
            $("#scope option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district/')}}/' + province
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    $('select[name="district_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    $('#scope').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                }
                loadWard(msg[0]['id']);
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
                    $('select[name="ward_id"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                }
            });
        }

        function addDelivery() {
            var province = $('#province').val();
            var district = $('#district').val();
            var ward = $('#ward').val();
            var home_number = $('#home_number').val();
            var token = $('input[name = _token]').val();
            $.ajax({
                type: "POST",
                url: '{{url('/ajax/delivery_address/create')}}/' + '{{@$customer->id}}',
                data: {
                    province: province,
                    district: district,
                    ward: ward,
                    home_number: home_number,
                    _token: token
                }
            }).done(function (response) {
                if (response === 'success') {
                    location.reload()
                } else if (response === 'error') {
                    $('#home_data span').remove()
                    $('#home_data').append('<span style="color: red" class="help-block">Dữ liệu không được để trống</span>');
                    $('#addDelivery').modal('show');
                } else {
                    $('.modal-header span').remove()
                    $('.modal-header').append('<span style="color: red" class="help-block">Thêm mới dữ địa điểm không thành công</span>');
                    $('#addDelivery').modal('show');
                }
            });
        }
    </script>
@endpush
