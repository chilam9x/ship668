@extends('admin.app')

@section('title')
    Đồng hành cùng bạn
@endsection

@section('sub-title')
    @if(isset($policy))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    {{ Form::open(['url' => ['/admin/policies/edit', $policy->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label">Nội dung</label>
                                <textarea id="content" class="form-control content" placeholder="Nội dung" name="content">{{ old('content',@$policy->content) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/policies') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{ asset('/assets/global/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        // $(".mask_date").inputmask("y/m/d", {
        //     autoUnmask: true
        // });

        CKEDITOR.replace( 'content' );
    </script>
@endpush
