@extends('admin.app')

@section('title')
    Quận/huyện
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row" style="margin-bottom: 20px">
        <div class="col-lg-8">
            <div class="row">
                <div class="col-lg-4">
                    <label><b style="color: rgba(85,5,5,0.98)">CHỌN TỈNH/TP</b></label>
                    {{ Form::select('province_id', \App\Models\Province::getProvinceOption() , old('province_id'),
                                          ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province', 'onchange'=>'loadData(this.value)']) }}
                </div>
                <div class="col-lg-4" style="margin-top: 25px" id="province_acive">
                    <label><b style="color: rgba(131,27,27,0.98)">Áp dụng giao hàng</b></label>
                </div>
                <div class="col-lg-4" style="margin-top: 25px" id="check_province">
                    <label><b style="color: rgba(85,5,5,0.98)">Áp dụng giao hàng nội tỉnh</b></label>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered">
                <thead>
                <tr>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div>
    <div class="modal fade" id="changeType" role="dialog">
        <div class="modal-dialog" style="width: 20%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: red" class="modal-title">Thay đổi loại Quận/Huyện</h4>
                </div>
                <div class="modal-body">
                    <div class="row" style="margin-top: 15px">
                        {{csrf_field()}}
                        <input type="hidden" id="district_id" name="district">
                        <div class="col-lg-12">
                            <label>Tỉnh/Thành phố</label>
                            {{ Form::select('district_type', \App\Models\DistrictType::getAllOption() , old('district_type'),
                                 ['class' => 'form-control', 'style' => 'width:100%', 'id' => 'district_type']) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="changeType()" class="btn btn-primary"
                            data-dismiss="modal">Thực hiện
                    </button>
                    <button onclick="$('#changeType').modal('hide')" type="button"
                            class="btn btn-default" data-dismiss="modal">Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        var province = $('#province').val();
        loadData(province);
        provinceType(province);
        province_active(province);
        function loadData(province) {
            province_active(province);
            provinceType(province);
            $("table thead tr th").remove();
            $("table tbody tr").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/load_data_district/')}}/' + province
            }).done(function (msg) {
                $("table thead tr").append('<th> STT </th>');
                $.each(msg[0], function (f, title) {
                    if (f == 'allow_booking') {
                        f = 'Trạng thái áp dụng';
                    }
                    if (f == 'district_name') {
                        f = 'Tên huyện';
                    }
                    if (f == 'district_type_name') {
                        f = 'Loại';
                    }
                    $("table thead tr").append('<th>' + f + '</th>');
                });
                $("table thead tr").append('<th> Hành động </th>');
                var num = 1;
                $.each(msg, function (f, data_rep) {
                    $("table tbody").append("<tr id='" + num + "'></tr>");
                    $("#" + num + "").append('<th>' + num + '</th>');
                    
                    // $.each(data_rep, function (d, value) {
                    //     // console.log(d + '--' + value);
                    //     let url = "";
                    //     if (d == 'id') {
                    //         url += "{{ url('update-allow-booking') }}/" + value;
                    //     }
                    //     if (d == 'allow_booking') {
                    //         console.log(url);
                    //         if (value == 1) {
                    //             value = '<a href="' + url + '" class="btn btn-xs btn-success">Áp dụng giao hàng</a>';
                    //         } else {
                    //             value = '<a href="" class="btn btn-xs btn-danger">Không áp dụng giao hàng</a>';
                    //         }
                    //     }
                    //     $("#" + num + "").append('<td>' + value + '</td>');
                    // });

                    var btn = '';
                    if (data_rep.allow_booking == 1) {
                        btn += '<button id="btn-update-allow-booking-' + data_rep.id + '" onclick="updateAllowBooking(' + data_rep.id + ', 0)" class="btn btn-xs btn-success">Áp dụng giao hàng</button>';
                    } else {
                        btn += '<button id="btn-update-allow-booking-' + data_rep.id + '" onclick="updateAllowBooking(' + data_rep.id + ', 1)" class="btn btn-xs btn-danger">Không áp dụng giao hàng</button>';
                    }
                    $("#" + num + "").append('<td>' + data_rep.id + '</td>');
                    $("#" + num + "").append('<td>' + data_rep.district_name + '</td>');
                    $("#" + num + "").append('<td>' + data_rep.district_type_name + '</td>');
                    $("#" + num + "").append('<td>' + btn + '</td>');
                    $("#" + num + "").append('<td> <button onclick="showModal(' + data_rep.id + ')" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> sửa</button></td>');
                    num += 1;
                });
            });
        }

        function province_active(data){
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/check_province/')}}',
                data : {id : data, type : 'active'}
            }).done(function (res) {
                $("#province_acive img").remove();
                if (res == 1) {
                    $("#check_province").show();
                    $("#province_acive").append('<img onclick="changeProvinceActive(' + data + ')" src="{{asset('public/img/corect.png')}}" width="30px"></img>');
                } else {
                    $("#province_acive").append('<img onclick="changeProvinceActive(' + data + ')" src="{{asset('public/img/incorect.png')}}" width="30px"></img>');
                    $("#check_province").hide();
                }
            });
        }

        function changeProvinceActive(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/change_province/')}}',
                data : {id : id, type : 'active'}
            }).done(function (res) {
                $("#province_acive img").remove();
                if (res == 1) {
                    $("#check_province").show();
                    $("#province_acive").append('<img onclick="changeProvinceActive(' + id + ')" src="{{asset('public/img/corect.png')}}" width="30px"></img>');
                } else {
                    $("#province_acive").append('<img onclick="changeProvinceActive(' + id + ')" src="{{asset('public/img/incorect.png')}}" width="30px"></img>');
                    $("#check_province").hide();
                }
            });
        }

        function provinceType(data) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/check_province/')}}',
                data : {id : data, type : 'type'}
            }).done(function (res) {
                $("#check_province img").remove();
                if (res == 1) {
                    $("#check_province").append('<img onclick="changeProvinceType(' + data + ')" src="{{asset('public/img/corect.png')}}" width="30px"></img>');
                } else {
                    $("#check_province").append('<img onclick="changeProvinceType(' + data + ')" src="{{asset('public/img/incorect.png')}}" width="30px"></img>');
                }
            });
        }

        function changeProvinceType(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/change_province/')}}',
                data : {id : id, type : 'type'}

            }).done(function (res) {
                $("#check_province img").remove();
                if (res == 1) {
                    $("#check_province").append('<img onclick="changeProvinceType(' + id + ')" src="{{asset('public/img/corect.png')}}" width="30px"></img>');
                } else {
                    $("#check_province").append('<img onclick="changeProvinceType(' + id + ')" src="{{asset('public/img/incorect.png')}}" width="30px"></img>');
                }
            });
        }

        function showModal(data) {
            $('#district_id').val(data);
            $('#changeType').modal('show');
        }

        function changeType() {
            var province = $('#province').val();
            var district_id = $('#district_id').val();
            var district_type = $('#district_type').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/change_type')}}',
                data: {
                    province: province,
                    district_id: district_id,
                    district_type: district_type,
                }
            }).done(function (response) {
                if (response === 'success') {
                    $('#province').val(province);
                    loadData(province)
                }
            });
        }

        function updateAllowBooking(districtId, allow_booking){
            $.ajax({
                type: "GET",
                url: "{{url('/ajax/update-allow-booking')}}/" + districtId,
                data : {allow_booking : allow_booking},
                dataType: "JSON"
            }).done(function (res) {
                var btn = $('#btn-update-allow-booking-' + res.id);
                if (res.allow_booking == 1) {
                    btn.attr('onclick', 'updateAllowBooking(' + res.id + ', 0)');
                    btn.attr('class', 'btn btn-xs btn-success');
                    btn.html('Áp dụng giao hàng');
                } else {
                    btn.attr('onclick', 'updateAllowBooking(' + res.id + ', 1)');
                    btn.attr('class', 'btn btn-xs btn-danger');
                    btn.html('Không áp dụng giao hàng');
                }
            });
        }
    </script>
@endpush

