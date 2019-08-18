@extends('admin.app')

@section('title')
    Chương trình khuyến mãi
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/promotions/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                   aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'promotions',
               'title' => [
                       'caption' => 'Dữ liệu chương trình khuyến mãi',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/promotions"),
               'columns' => [
                       ['data' => 'title', 'title' => 'Tiêu đề'],
                       ['data' => 'start_date', 'title' => 'Ngày bắt đầu'],
                       ['data' => 'end_date', 'title' => 'Ngày kết thúc'],
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
