@extends('admin.app')

@section('title')
    Rút tiền
@endsection

@section('sub-title')
    Đã thanh toán
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'wallets',
               'title' => [
                       'caption' => 'Yêu cầu rút tiền đã thanh toán',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/wallet/paymented"),
               'columns' => [
                       ['data' => 'created_at', 'title' => 'Ngày rút'],
                       ['data' => 'customer_name', 'title' => 'Người rút tiền'],
                       ['data' => 'customer_phone_number', 'title' => 'Số ĐT'],
                       ['data' => 'bank_account', 'title' => 'Tên TK ngân hàng', 'orderable' => false],
                       ['data' => 'bank_account_number', 'title' => 'Số TK ngân hàng', 'orderable' => false],
                       ['data' => 'bank_name', 'title' => 'Tên ngân hàng', 'orderable' => false],
                       ['data' => 'bank_branch', 'title' => 'Chi nhánh ngân hàng', 'orderable' => false],
                       ['data' => 'price', 'title' => 'Số tiền'],
                       ['data' => 'payment_code', 'title' => 'Mã số rút tiền'],
                       ['data' => 'payment_date', 'title' => 'Ngày thanh toán'],
                       ['data' => 'withdrawal_type', 'title' => 'Hình thức thanh toán'],
                       ['data' => 'action', 'title' => 'Thao tác', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
