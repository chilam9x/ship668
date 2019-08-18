@extends('admin.app')

@section('title')
    Chiết khấu
@endsection

@section('sub-title')
    @if(isset($discount))Chỉnh sửa @else Thêm mới @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <i class="fa fa-edit"></i>
                        <span class="caption-subject bold uppercase">@if(isset($discount))Giao diện chỉnh sửa @else
                                Giao
                                diện thêm mới @endif</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    @if(isset($discount))
                        {{ Form::open(['route' => ['discounts.update', $discount->id], 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    @else
                        {{ Form::open(['url' => '/admin/discounts', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                    @endif
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Loại chiết khấu</label>
                                <input name="type" {{ isset($discount) ? 'readonly' : '' }} value="{{ old('type',@$discount->type) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập loại chiết khấu">
                                @if ($errors->has('type'))
                                    @foreach ($errors->get('type') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif

                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Mã chiết khấu</label>
                                <input name="key" {{ isset($discount) ? 'readonly' : '' }} value="{{ old('key',@$discount->key) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập mã chiết khấu">
                                @if ($errors->has('key'))
                                    @foreach ($errors->get('key') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tên chiết khấu</label>
                                <input name="name" value="{{ old('name',@$discount->name) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập tên chiết khấu">
                                @if ($errors->has('name'))
                                    @foreach ($errors->get('name') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Giá trị chiết khấu(%)</label>
                                <input name="value" value="{{ old('value',@$discount->value) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập giá trị chiết khấu">
                                @if ($errors->has('value'))
                                    @foreach ($errors->get('value') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Tên chiết khấu</label>
                                <input name="description" value="{{ old('description',@$discount->description) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập chú thích">
                            </div>
                        </div>
                    </div>
                    <button onclick="this.disabled=true; this.form.submit();" type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/discounts') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
