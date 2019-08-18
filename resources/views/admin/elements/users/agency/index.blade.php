@extends('admin.app')

@section('title')
    Đại lý
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">

            @include('admin.partial.log.err_log',['name' => 'delete'])
            @include('admin.partial.log.success_log',['name' => 'success'])
        @if(Auth::user()->role == 'admin')
        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/agencies/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                   aria-hidden="true"></i> Thêm mới</a>
        </div>
        @endif
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'agency',
               'title' => [
                       'caption' => 'Dữ liệu đại lý',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/agency"),
               'columns' => [
                       ['data' => 'name', 'title' => 'Tên'],
                       ['data' => 'phone', 'title' => 'Đường dây nóng'],
                       ['data' => 'address', 'title' => 'Địa chỉ'],
                       ['data' => 'collaborator_name', 'title' => 'Người quản lý'],
                       ['data' => 'discount', 'title' => 'Chiết khấu (%)'],
                       ['data' => 'scope', 'title' => 'Phạm vi quản lý'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
