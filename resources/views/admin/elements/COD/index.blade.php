@extends('admin.app')

@section('title')
    Thu hộ
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'cod',
               'title' => [
                       'caption' => 'Dữ liệu thu hộ',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/total_COD"),
               'columns' => [
                       ['data' => 'name', 'title' => 'Tên khách hàng'],
                       ['data' => 'email', 'title' => 'Email'],
                       ['data' => 'phone_number', 'title' => 'Số điện thoại'],
                       ['data' => 'full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'bank_account', 'title' => 'Tên chủ khoản'],
                       ['data' => 'bank_account_number', 'title' => 'Số tài khoản'],
                       ['data' => 'bank_name', 'title' => 'Tên ngân hàng'],
                       ['data' => 'bank_branch', 'title' => 'Chi nhánh ngân hàng'],
                       ['data' => 'total_COD', 'title' => 'Tổng tiền thu hộ'],
                       ['data' => 'send_total', 'title' => 'Giá cước người gửi'],
                       ['data' => 'receiver_total', 'title' => 'Giá cước người nhận'],
                       ['data' => 'action', 'title' => 'Hành động'],
                   ]
               ])
        </div>
    </div>
@endsection

