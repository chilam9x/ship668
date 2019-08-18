@extends('admin.app')

@section('title')
    Khách hàng
@endsection

@section('sub-title')
    danh sách địa chỉ gaio/nhận hàng
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="well">
            <a data-target="#addDelivery" onclick="$('#addDelivery').modal('show')" class="btn btn-info"><i class="fa fa-plus" aria-hidden="true"></i> Thêm mới địa chỉ giao/nhận hàng</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'delivery_address',
               'title' => [
                       'caption' => 'Địa chỉ giao/nhận hàng',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/delivery_address/".$id),
               'columns' => [
                       ['data' => 'full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'default', 'title' => 'Trạng thái'],
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'updated_at', 'title' => 'Ngày cập nhật'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
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
                        {{ csrf_field() }}
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

@endsection
@push('script')
    <script>
        loadDistrict();

        function changeDefault(data){
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/delivery_address/default')}}/' + data
            }).done(function (response) {
                    location.reload()
            });
        }

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
                url: '{{url('/ajax/delivery_address/create')}}/' + '{{@$id}}',
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

