@extends('admin.app')

@section('title')
    Khách hàng
@endsection

@section('sub-title')
    danh sách đơn nợ
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="" style="padding-left: 0px">
            <div class="row">
                <form action="{!! url('admin/customers/export_print_owe/' . $id) !!}" method="post">
                    {!! csrf_field() !!}
                    <div class="col-lg-8">
                        <div class="input-group">
                        <span class="input-group-addon" id="sizing-addon2"><span
                                    class="glyphicon glyphicon-calendar"> </span> Từ ngày</span>
                            <input type="date" id="date_from" name="date_from" class="form-control"
                                   aria-describedby="sizing-addon2" value="{!! @$time_from !!}">
                            <span class="input-group-addon" id="sizing-addon2"><span
                                        class="glyphicon glyphicon-calendar"> </span> Đến ngày</span>
                            <input type="date" id="date_to" name="date_to" class="form-control"
                                   aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            <span class="input-group-addon">Số điện thoại</span>
                            <input style="min-width: 180px" type="text" id="phone" name="phone" class="form-control">
                        </div>
                    </div>
                    @if ($count > 0)
                    <div class="col-lg-4">
                        <button type="submit" name="print" value="print" class="btn btn-circle btn-default pull-right"><i class="fa fa-print" aria-hidden="true"></i>
                            In
                        </button>
                        <button type="submit" name="export" value="export" class="btn btn-circle btn-primary pull-right"><i class="fa fa-print" aria-hidden="true"></i>
                            Xuất dữ liệu
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="well" style="padding-left: 0px">
            @if($count > 0)
            <a href="{!! url('admin/customers/paidAll/'.$id) !!}" onclick="if(!confirm('Bạn chắc chắn đã thanh toán toàn bộ nợ của khách hàng này?')) return false;"
               class="btn btn-primary"> <i class="fa fa-refresh" aria-hidden="true"></i> Thanh toán tất cả</a>
            @endif
            <a href="{!! url('/admin/customers') !!}" class="btn btn-default">Quay lại</a>
        </div>

        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'owe_booking',
               'title' => [
                       'caption' => 'Dữ liệu đơn còn nợ',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/owe_details/".$id),
               'columns' => [
                       ['data' => 'completed_at', 'title' => 'Ngày hoàn thành'],
                       ['data' => 'uuid', 'title' => 'Mã'],
                       ['data' => 'name', 'title' => 'Tên'],
                       ['data' => 'send_name', 'title' => 'Người gửi'],
                       ['data' => 'send_phone', 'title' => 'Số điện thoại'],
                       ['data' => 'send_full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'receive_name', 'title' => 'Người nhận'],
                       ['data' => 'receive_phone', 'title' => 'Số điện thoại'],
                       ['data' => 'receive_full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'weight', 'title' => 'Khối lượng(gram)'],
                       ['data' => 'price', 'title' => 'Giá'],
                       ['data' => 'incurred', 'title' => 'Chi phí phát sinh'],
                       ['data' => 'paid', 'title' => 'Số tiền đã thanh toán'],
                       ['data' => 'COD', 'title' => 'COD'],
                       ['data' => 'COD_status', 'title' => 'Trạng thái COD'],
                       ['data' => 'receiveShipper', 'title' => 'Shipper nhận'],
                       ['data' => 'sendShipper', 'title' => 'Shipper giao'],
                       ['data' => 'returnShipper', 'title' => 'Shipper trả'],
                       ['data' => 'payment_type', 'title' => 'Ghi chú'],
                       ['data' => 'note', 'title' => 'Ghi chú hệ thống'],
                       ['data' => 'report_image', 'title' => 'Ảnh báo cáo'],
                       ['data' => 'action', 'title' => 'Thanh toán đơn nợ'],
                   ]
               ])
        </div>
    </div>
@endsection
@push('script')
    <script>
        function changeOweStatus(data) {
            if (confirm("Bạn có chắc chắn đơn hàng đã được thanh toán không ?")) {
                $.ajax({
                    type: "GET",
                    url: '{{url('/ajax/change_owe_status')}}/' + data
                }).done(function (response) {
                    location.reload()
                });
            } else {
                return -1;
            }
        }
    </script>
@endpush