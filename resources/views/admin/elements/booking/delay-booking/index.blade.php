@extends('admin.app')

@section('title')
    Đơn hàng tạm hoãn
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
                    <input type="hidden" name="status[]" value="taking">
                    <input type="hidden" name="sub_status[]" value="delay">
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
               'id' => 'delay_booking',
               'title' => [
                       'caption' => 'Dữ liệu đơn tạm hoãn',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/delay"),
               'columns' => [
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'user_create', 'title' => 'Người tạo đơn', 'orderable' => false, 'searchable' => false],
                       ['data' => 'uuid', 'title' => 'Mã đơn hàng'],
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
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
@push('script')
    <script>
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
    </script>
@endpush

