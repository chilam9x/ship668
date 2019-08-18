@extends('admin.app')

@section('title')
    Chiết khấu
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/discounts/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                       aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table', [
               'id' => 'discount',
               'title' => [
                       'caption' => 'Dữ liệu cộng tác viên',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/discount"),
               'columns' => [
                       ['data' => 'type', 'title' => 'Loại chiết khấu'],
                       ['data' => 'name', 'title' => 'Tên chiết khấu'],
                       ['data' => 'value', 'title' => 'Giá trị chiết khấu (%)'],
                       ['data' => 'description', 'title' => 'Chú thích'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
