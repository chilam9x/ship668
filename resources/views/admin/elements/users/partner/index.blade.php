@extends('admin.app')

@section('title')
    Đối tác
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/partners/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                   aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'partner',
               'title' => [
                       'caption' => 'Dữ liệu đối tác',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/partner"),
               'columns' => [
                       ['data' => 'name', 'title' => 'Tên đối tác'],
                       ['data' => 'avatar', 'title' => 'Ảnh đại diện'],
                       ['data' => 'email', 'title' => 'Email'],
                       ['data' => 'full_address', 'title' => 'Địa chỉ'],
                       ['data' => 'phone_number', 'title' => 'Hotline'],
                       ['data' => 'api_content', 'title' => 'API'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
