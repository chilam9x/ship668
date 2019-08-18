@extends('admin.app')

@section('title')
    Giá cước
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
                        <span class="caption-subject bold uppercase">Giao diện chỉnh sửa</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {{ Form::open(['url' => url('admin/price/'.$type.'/'.$id), 'method' => 'put', 'enctype' => 'multipart/form-data']) }}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Giá cước (VND)</label>
                                <input name="price" value="{{ old('price',@$price) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập đơn giá">
                                @if ($errors->has('price'))
                                    @foreach ($errors->get('price') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Khối lượng (gam)</label>
                                <input name="weight" value="{{ old('weight',@$weight) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập khối lượng">
                                @if ($errors->has('weight'))
                                    @foreach ($errors->get('weight') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Giá cước tăng thêm (VND)</label>
                                <input name="price_plus" value="{{ old('price_plus',@$price_plus) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập đơn giá">
                                @if ($errors->has('price_plus'))
                                    @foreach ($errors->get('price_plus') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <label class="control-label" for="inputError">Khối lượng tăng thêm (gam)</label>
                                <input name="weight_plus" value="{{ old('weight_plus',@$weight_plus) }}"
                                       class="form-control spinner" type="text"
                                       placeholder="Nhập khối lượng">
                                @if ($errors->has('weight_plus'))
                                    @foreach ($errors->get('weight_plus') as $error)
                                        <span style="color: red" class="help-block">{!! $error !!}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <button onclick="this.disabled=true; this.form.submit();" type="submit" class="btn blue">Thực hiện</button>
                    <a href="{{ url('/admin/price') }}" type="button" class="btn default">Hủy</a>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
