@extends('admin.app')

@section('title')
    Chương trình khuyến mãi
@endsection

@section('sub-title')
    @if(isset($promotions))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="portlet light bordered">
                <div class="portlet-body form">
                    @if(isset($promotions))
                        {{ Form::open(['route' => ['promotions.update', $promotions->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    @else
                        {{ Form::open(['url' => '/admin/promotions', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    @endif
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tiêu đề</label>
                                <input class="form-control spinner" value="{{ old( 'title', @$promotions->title) }}"
                                       name="title" type="text" placeholder="Nhập tiêu đề">
                                @if ($errors->has('title'))
                                    @foreach ($errors->get('title') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="{{--has-error--}} form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label">Nội dung</label>
                                <textarea id="content" class="form-control content" placeholder="Nội dung" name="content">{{ old('content',@$promotions->content) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-3">
                                <label class="control-label">Ngày bắt đầu</label>
                                <input name="start_date" value="{{ old( 'start_date', @$promotions->start_date) }}"
                                       class="form-control mask_date" id="mask_date" type="text"/>
                                <span class="help-block"> Năm/Tháng/Ngày</span>
                                @if ($errors->has('start_date'))
                                    @foreach ($errors->get('start_date') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-3">
                                <label class="control-label">Ngày kết thúc</label>
                                <input name="end_date" value="{{ old( 'end_date', @$promotions->end_date) }}"
                                       class="form-control mask_date" id="mask_date" type="text"/>
                                <span class="help-block"> Năm/Tháng/Ngày</span>
                                @if ($errors->has('end_date'))
                                    @foreach ($errors->get('end_date') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/promotions') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{ asset('/assets/global/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        $(".mask_date").inputmask("y/m/d", {
            autoUnmask: true
        }); //direct mask

        CKEDITOR.replace( 'content' );
    </script>
@endpush
