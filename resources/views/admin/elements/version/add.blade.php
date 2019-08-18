@extends('admin.app')

@section('title')
    Phiên bản ứng dụng
@endsection

@section('sub-title')
    Chỉnh sửa
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">Giao diện chỉnh sửa </span>
                    </div>
                </div>
                <div class="portlet-body form">
                        {{ Form::open(['route' => ['versions.update', $version->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}

                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-4">
                                <label class="control-label" for="inputError">Mã</label>
                                <input name="version_code" value="{{ old('version_code',@$version->version_code) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập loại chiết khấu">
                                @if ($errors->has('version_code'))
                                    @foreach ($errors->get('version_code') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                            <div class="col-lg-4">
                                <label class="control-label" for="inputError">Tên phiên bản</label>
                                <input name="version_name" value="{{ old('version_name',@$version->version_name) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập tên phiên bản">
                                @if ($errors->has('version_name'))
                                    @foreach ($errors->get('version_name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-4">
                                <label class="control-label" for="inputError">Được nâng cấp</label>
                                <select name="force_upgrade" class="form-control">
                                    <option value="1">Có</option>
                                    <option value="0">không</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="control-label" for="inputError">Mô tả</label>
                                <input name="description" value="{{ old('description',@$version->description) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập mô tả">
                                @if ($errors->has('description'))
                                    @foreach ($errors->get('description') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/discounts') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
