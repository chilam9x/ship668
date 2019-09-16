@extends('admin.app')

@section('title')
    Đơn hàng trả lại
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="well" style="padding-left: 0px">
            <div class="row">
                <form action="{!! url('admin/booking/exportAdvance') !!}" method="get">
                    <input type="hidden" name="status[]" value="return">
                    <input type="hidden" name="sub_status[]" value="none">
                    <input type="hidden" name="sub_status[]" value="deny">
                    <div class="col-lg-8">
                        <div class="input-group">
                             <span class="input-group-addon" id="sizing-addon2"><span
                                         class="glyphicon glyphicon-calendar"> </span> Từ ngày</span>
                            <input type="date" id="date_from" name="date_from" class="form-control"
                                   aria-describedby="sizing-addon2" value="{!! $time_from !!}">
                            <span class="input-group-addon" id="sizing-addon2"><span
                                        class="glyphicon glyphicon-calendar"> </span> Đến ngày</span>
                            <input type="date" id="date_to" name="date_to" class="form-control"
                                   aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            <span class="input-group-addon">Số điện thoại</span>
                            <input style="min-width: 180px" type="text" id="phone" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12" style="margin-top: 5px">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <span class="input-group-addon" id="sizing-addon2">Tỉnh / TP</span>
                                    {{ Form::select('province_id', \App\Models\Province::getProvinceOption(1) , old('province_id', @$user->province_id),
                                 ['class' => 'form-control', 'style' => 'min-width: 180px', 'id'=>'province', 'onchange'=>'loadDistrict()']) }}
                                    <span class="input-group-addon" id="sizing-addon2">Quận / Huyện</span>
                                    <select style="min-width: 180px" id="district" onchange="loadWard(this.value)" name="district_id"
                                            class="form-control">
                                        <option value="-1" selected>Tất cả</option>
                                    </select>
                                    <span class="input-group-addon">Phường / xã</span>
                                    <select style="min-width: 180px" id="ward" name="ward_id" class="form-control">
                                        <option value="-1" selected>Tất cả</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <button type="submit" class="btn btn-circle btn-primary pull-right"><i
                                            class="fa fa-print"
                                            aria-hidden="true"></i>
                                    Xuất dữ liệu
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'deny_booking',
               'title' => [
                       'caption' => 'Dữ liệu đơn hàng trả lại',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/deny_booking"),
               'columns' => [
                    ['data' => 'image_order', 'title' => 'Ảnh đơn hàng'],
                    ['data' => 'uuid', 'title' => 'QR Code'],
                    ['data' => 'created_at', 'title' => 'Ngày tạo'],
                    ['data' => 'user_create', 'title' => 'Người tạo đơn', 'orderable' => false, 'searchable' => false],
                    ['data' => 'name', 'title' => 'Tên đơn hàng'],
                    ['data' => 'send_name', 'title' => 'Người gửi'],
                    ['data' => 'send_phone', 'title' => 'Số điện thoại'],
                    ['data' => 'send_full_address', 'title' => 'Địa chỉ'],
                    ['data' => 'receive_name', 'title' => 'Người nhận'],
                    ['data' => 'receive_phone', 'title' => 'Số điện thoại'],
                    ['data' => 'receive_full_address', 'title' => 'Địa chỉ'],
                    ['data' => 'weight', 'title' => 'Khối lượng(gram)'],
                    ['data' => 'transport_type', 'title' => 'Phương thức vận chuyển'],
                    ['data' => 'price', 'title' => 'Giá'],
                    ['data' => 'incurred', 'title' => 'Chi phí phát sinh'],
                    ['data' => 'paid', 'title' => 'Số tiền đã thanh toán'],
                    ['data' => 'COD', 'title' => 'Thu hộ'],
                    ['data' => 'status', 'title' => 'Trạng thái'],
                    ['data' => 'payment_type', 'title' => 'Ghi chú'],
                    ['data' => 'other_note', 'title' => 'Ghi chú khác'],
                    ['data' => 'note', 'title' => 'Ghi chú hệ thống'],
                    ['data' => 'report_image', 'title' => 'Ảnh báo cáo'],
                    ['data' => 'shipper', 'title' => 'Tên Shipper'],
                    ['data' => 'first_agency', 'title' => 'Đại lý lấy hàng'],
                    ['data' => 'current_agency', 'title' => 'Kho hiện tại'],
                    ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="modal fade" id="move" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: #1d0c09" class="modal-title">Giao diện phân công đại lý nhận
                        hàng</h4>
                </div>
                <form id="import" method="post" action="{!! url('admin/booking/move') !!}"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <input id="booking_id" type="hidden" name="booking">
                            <div class="col-lg-8">
                                <label>Đại lý (Mặc định là đại lý quản lý khu vực nhận hàng)</label>
                                <select class="selectpicker" data-show-subtext="true" data-live-search="true"
                                        name="agency">
                                    @if(isset($agency))
                                        @foreach($agency as $k => $v)
                                            <option value="{!! $k !!}">{!! $v !!}</option>
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
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Thực hiện</button>
                        <button onclick="$('#importData').modal('hide')" type="button"
                                class="btn btn-default" data-dismiss="modal">Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        function moveBooking(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/check_agency/')}}',
                data : {
                    id : id, type : 'deny'
                }
            }).done(function (res) {
                $('.selectpicker').find('[value='+ res['current'] + ']').remove();
                $('.selectpicker').selectpicker('refresh');
                $('.selectpicker').selectpicker('val', res['agency']);
                $('#booking_id').val(res['id']);
                $('#move').modal('show')
            });
        }
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
        setTimeout(function(){ $('[data-toggle="popover"]').popover(); }, 1000);
        function changeUrl(data) {
            var href = $('#owe_submit').attr('href');
            if ($('#owe').is(':checked')){
                if (href.indexOf('?owe=0') > -1){
                    $('#owe_submit').attr("href", href.replace('?owe=0', '?owe=1'));
                }else{
                    $('#owe_submit').attr("href", href+'?owe=1');
                }
            }else {
                $('#owe_submit').attr("href",  href.replace('?owe=1', '?owe=0'));
            }
        }
    </script>
@endpush
