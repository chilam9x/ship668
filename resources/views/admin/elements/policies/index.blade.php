@extends('admin.app')

@section('title')
    Đồng hành cùng bạn
@endsection

@section('sub-title')
    Chính sách đồng hành cùng bạn
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="col-lg-12">
            <div class="portlet light bordered">
                {!! @$policy->content !!}
            </div>
        </div>

        <div class="well" style="padding-left: 0px">
            <div class="col-lg-12">
                @if(!isset($policy) || empty($policy))
                <a href="{!! url('admin/policies/add') !!}" class="btn btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Thêm mới</a>
                @else
                <a href="{!! url('admin/policies/edit', $policy->id) !!}" class="btn btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Chỉnh sửa</a>
                <a href="{!! url('admin/policies/delete', $policy->id) !!}" class="btn btn-danger"> <i class="fa fa-plus" aria-hidden="true"></i> Xóa</a>
                @endif
            </div>
        </div>
    </div>
@endsection
