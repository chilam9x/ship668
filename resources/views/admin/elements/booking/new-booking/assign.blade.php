@extends('admin.app')

@section('title')
    Đơn hàng mới
@endsection

@section('sub-title')
    Phân công
@endsection

@section('content')
    <div class="col-lg-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-red-sunglo">
                    <i class="fa fa-edit"></i>
                    <span class="caption-subject bold uppercase">Giao diện phân công</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form method="post"
                      action="{{ isset($reAssign) ? url('/admin/booking/reassign/'.$cate.'/'.$id) : url('/admin/booking/assign/'.$id) }}">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Chọn Shipper</label>
                                <select name="shipper" class="form-control">
                                    @if(isset($shipper))
                                        @foreach($shipper as $s)
                                            <option value="{{@$s->id}}">{{@$s->name}}</option>
                                        @endforeach
                                    @else
                                        <option value="">Không có shipper trong hệ thống</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        @if(!isset($reAssign))
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Chọn hình thức vận chuyển</label>
                                    <select name="category" class="form-control">
                                        <option value="receive">Lấy hàng</option>
                                        <option value="r-and-s">Vừa lấy vừa giao</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button onclick="this.disabled=true; this.form.submit();" type="submit" class="btn blue">Thực hiện
                    </button>
                    <a href="{{ url('/admin/booking/new') }}" type="button" class="btn default">Hủy</a>
                </form>
            </div>
        </div>
    </div>
    <script>
        $('#blah').hide();

        function readURL(input) {
            $('#blah').hide();
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                console.log(reader);

                reader.onload = function (e) {
                    $('#blah').attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#exampleInputFile").change(function () {
            readURL(this);
            $('#blah').show();
        });
    </script>
@endsection
