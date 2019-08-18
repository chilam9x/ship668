@extends('admin.app')

@section('title')
    Phiên bản ứng dụng
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
               'id' => 'ver',
               'title' => [
                       'caption' => 'Danh sách phiên bản ứng dụng',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/version"),
               'columns' => [
                       ['data' => 'version_code', 'title' => 'Mã'],
                       ['data' => 'version_name', 'title' => 'Tên phiên bản'],
                       ['data' => 'description', 'title' => 'Mô tả'],
                       ['data' => 'force_upgrade', 'title' => 'Nâng cấp'],
                       ['data' => 'category', 'title' => 'Loại ứng dụng'],
                       ['data' => 'device_type', 'title' => 'Loại thiết bị'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
@endsection
