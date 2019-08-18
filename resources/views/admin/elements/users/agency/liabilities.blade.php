@extends('admin.app')

@section('title')
    Đại lý
@endsection

@section('sub-title')
    chi tiết thanh toán công nợ
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'liabilities',
               'title' => [
                       'caption' => 'Dữ liệu công nợ đã thanh toán',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/liabilities/".$id),
               'columns' => [
                       ['data' => 'agency_name', 'title' => 'Tên đại lý'],
                       ['data' => 'agency_address', 'title' => 'Địa chỉ'],
                       ['data' => 'agency_phone', 'title' => 'Hot line'],
                       ['data' => 'value', 'title' => 'Số tiền thanh toán'],
                       ['data' => 'updated_at', 'title' => 'Ngày thanh toán'],
                       ['data' => 'creator', 'title' => 'Người thanh toán'],
                       ['data' => 'action', 'title' => 'Xác nhận đã thanh toán'],
                   ]
               ])
        </div>
    </div>
@endsection
@push('script')
    <script>
        function changeStatus(data) {
            if (confirm("Bạn có chắc chắn đại lý đã thanh toán không ?")) {
                $.ajax({
                    type: "GET",
                    url: '{{url('/ajax/change_liabilities_status')}}/' + data
                }).done(function (response) {
                    location.reload()
                });
            } else {
                return -1;
            }

        }

    </script>
@endpush
