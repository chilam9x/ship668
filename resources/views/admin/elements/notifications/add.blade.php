@extends('admin.app')

@section('title')
    Quản lý thông báo
@endsection

@section('sub-title')
    Thêm mới
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    {{ Form::open(['url' => '/admin/notification-handles/add', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="">Thông báo tới tài khoản</label>
                                <select class="form-control" title="Không chọn để gửi đến tất cả" name="user_id" data-live-search="true">
                                    <option value="all">Tất cả</option>
                                    <option value="all_customer">Tất cả khách hàng</option>
                                    <option value="all_customer_booked">Tất cả khách hàng đã đặt đơn</option>
                                    <option value="all_shipper">Tất cả shipper</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="">Tiêu đề</label>
                                <input class="form-control spinner" value="{{ old( 'title') }}"
                                       name="title" type="text" placeholder="Nhập tiêu đề">
                                <!-- @if ($errors->has('title'))
                                    @foreach ($errors->get('title') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif -->
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label">Nội dung</label>
                                <textarea rows="10" id="content" class="form-control content" placeholder="Nội dung" name="content">{{ old('content') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn blue">Thực hiện</button>
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