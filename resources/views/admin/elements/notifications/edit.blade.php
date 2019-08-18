@extends('admin.app')

@section('title')
    Quản lý thông báo
@endsection

@section('sub-title')
    Sửa
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    {{ Form::open(['url' => '/admin/notification-handles/edit/' . $notificationHandle->id, 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="">Thông báo được gửi tới {{ count($users) }} người dùng.</label>
                                
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="inputError">Tiêu đề</label>
                                <input class="form-control spinner" value="{{ old( 'title', @$notificationHandle->title) }}"
                                       name="title" type="text" placeholder="Nhập tiêu đề" disabled="">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label">Nội dung</label>
                                <textarea disabled="" rows="10" id="content" class="form-control content" placeholder="Nội dung" name="content">{{ old('content',@$notificationHandle->content) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url('/admin/notification-handles') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('/assets/global/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function(){
            $(".user-id").selectpicker();

            CKEDITOR.replace( 'content' );
        });
    </script>
@endpush