@extends('admin.app')

@section('title')
    Cộng tác viên
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/collaborators/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                   aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'collaborators',
               'title' => [
                       'caption' => 'Dữ liệu cộng tác viên',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/collaborators"),
               'columns' => [
                       ['data' => 'name', 'title' => 'Tên'],
                       ['data' => 'avatar', 'title' => 'Ảnh đại diện'],
                       ['data' => 'email', 'title' => 'Email'],
                       ['data' => 'role', 'title' => 'Vai trò'],
                       ['data' => 'status', 'title' => 'Trạng thái'],
                       ['data' => 'agency_name', 'title' => 'Tên đại lý'],
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
