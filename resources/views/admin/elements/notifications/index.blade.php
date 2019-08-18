@extends('admin.app')

@section('title')
    Quản lý thông báo
@endsection

@section('sub-title')
    Danh sách thông báo thủ công
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/notification-handles/add') !!}" class="btn btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'notification-handles',
               'title' => [
                       'caption' => 'Dữ liệu thông báo thủ công',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/notification-handles"),
               'columns' => [
                       ['data' => 'title', 'title' => 'Tiêu đề'],
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
