@extends('admin.app')

@section('title')
    Khách hàng
@endsection

@section('sub-title')
    danh sách chi tiết thu hộ
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            @if($count > 0)
                <a href="{!! url('admin/paid_COD/' . $id) !!}" onclick="if(!confirm('Bạn chắc chắn đã thanh toán toàn bộ tiền thu hộ của đơn hàng này?')) return false;"
                   class="btn btn-primary"> <i class="fa fa-refresh" aria-hidden="true"></i> Thanh toán tất cả</a>
            @endif
            <a href="{!! url('/admin/customers') !!}" class="btn btn-default">Quay lại</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'sent_booking',
               'title' => [
                       'caption' => 'Dữ liệu đơn hàng có thu hộ',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/COD_details/".$id),
               'columns' => [
                       ['data' => 'uuid', 'title' => 'Mã đơn hàng'],
                       ['data' => 'name', 'title' => 'Tên đơn hàng'],
                       ['data' => 'send_name', 'title' => 'Người gửi'],
                       ['data' => 'send_phone', 'title' => 'Số điện thoại'],
                       ['data' => 'send_full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'receive_name', 'title' => 'Người nhận'],
                       ['data' => 'receive_phone', 'title' => 'Số điện thoại'],
                       ['data' => 'receive_full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'weight', 'title' => 'Khối lượng(gram)'],
                       ['data' => 'price', 'title' => 'Giá'],
                       ['data' => 'incurred', 'title' => 'Chi phí phát sinh'],
                       ['data' => 'payment_type', 'title' => 'Ghi chú'],
                       ['data' => 'shipper', 'title' => 'Tên Shipper'],
                       ['data' => 'COD', 'title' => 'COD'],
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
                    console.log(response);
                    location.reload()
                });
            } else {
                return -1;
            }

        }

    </script>
@endpush
