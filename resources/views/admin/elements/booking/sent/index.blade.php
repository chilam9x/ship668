@extends('admin.app')

@section('title')
    Đơn hàng đã hoàn thành
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
                <form action="{!! url('admin/booking/export') !!}" method="get">
                    <input type="hidden" name="status" value="completed">
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
                    <div class="col-lg-12">
                        @if(Auth::user()->role == 'admin')
                            <a href="#" onclick="removeBooking()" class="btn btn-circle btn-danger pull-right"> <i
                                        class="fa fa-trash" aria-hidden="true"></i> Xóa dữ liệu</a>
                        @endif
                        <button type="submit" class="btn btn-circle btn-primary pull-right"><i class="fa fa-print"
                                                                                               aria-hidden="true"></i>
                            Xuất dữ liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'sent_booking',
               'title' => [
                       'caption' => 'Dữ liệu đơn đã hoàn thành',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/sent"),
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
                       ['data' => 'receiveShipper', 'title' => 'Shipper nhận'],
                       ['data' => 'sendShipper', 'title' => 'Shipper giao'],
                       ['data' => 'COD', 'title' => 'COD'],
                       ['data' => 'payment_type', 'title' => 'Ghi chú'],
                       ['data' => 'other_note', 'title' => 'Ghi chú khác'],
                       ['data' => 'note', 'title' => 'Ghi chú hệ thống'],
                       ['data' => 'report_image', 'title' => 'Ảnh báo cáo'],
                       ['data' => 'payment_date', 'title' => 'Ngày thanh toán COD'],
                       ['data' => 'COD_status', 'title' => 'Thanh toán COD'],
                   ]
               ])
        </div>
    </div>
@endsection
@push('script')
    <script>
        function changeCODStatus(data) {
            if (confirm("Bạn có chắc chắn đã thanh toán phí thu hộ cho đơn hàng này không ?")) {
                $.ajax({
                    type: "GET",
                    url: '{{url('/ajax/change_cod_status')}}/' + data
                }).done(function (response) {
                    location.reload()
                });
            } else {
                return -1;
            }

        }

        function removeBooking() {
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            var phone = $('#phone').val();
            if (confirm("Bạn có chắc chắn muốn xóa không ?")) {
                $.ajax({
                    type: "GET",
                    url: '{{url('/ajax/remove_booking')}}',
                    data: {date_from: date_from, date_to: date_to, status: 'completed', phone: phone}
                }).done(function (response) {
                    location.reload()
                });
            } else {
                return -1;
            }

        }

    </script>
@endpush
