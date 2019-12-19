<div class="container function">
    <div class="row">
        <div class="col-md-2 col-sm-12"></div>
        <div class="col-md-8 col-sm-12 title-large">
            <h1>Tiện ích</h1>
            <p>Để giải đáp thêm về thắc mắc hoặc cần hỗ trợ thêm. Quý khách vui lòng liên hệ với Ship668 theo
                thông tin dưới đây.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-sm-12 search-order">
            <h2 class="cyan">Tra cứu đơn hàng</h2>
            <input type="text" id="booking_id" placeholder="Nhập mã đơn hàng"/>
            <button style="margin-top: 15px" type="button" onclick="searchBooking()">
                Tra cứu
            </button>
        </div>
        <div class="col-md-6     col-sm-12 search-order">
            <h2 class="cyan">Tra trước giá cước</h2>
            <p>Để biết thêm về giá cước đơn hàng của bạn trước khi tạo. Bạn có thể tra cứu thêm, vui lòng nhấn nút phía
                dưới.</p>
            <button type="button" onclick="$('#searchPrice').modal('show')">
                Tra cứu
            </button>
        </div>
    </div>
</div>
<div class="modal fade" id="searchPrice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 800px !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Giao diện tra cứu giá cước</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Phương thức nhận hàng</label>
                                    <select name="search_receive_type" class="form-control">
                                        <option value="1">Nhận hàng tại nhà</option>
                                        <option value="2">Nhận hàng tại bưu cục</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">

                                    <label>Phương thức vận chuyển</label>
                                    <select name="search_transport_type" class="form-control">
                                        <option value="1">Giao chuẩn</option>
                                        <!-- <option value="2">Giao tiết kiệm</option> -->
                                        <option value="3">Giao siêu tốc</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label" for="inputError">Khối lượng (gram)</label>
                                    <input name="search_weight" value="{{ old('search_weight') }}"
                                           class="form-control" type="email"
                                           placeholder="Nhập khối lượng">
                                    <span style="color: red" id="weight" class="help-block"></span>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label" for="inputError">Tiền thu hộ</label>
                                    <input name="search_cod" value="{{ old('search_cod') }}"
                                           class="form-control spinner" type="email"
                                           placeholder="Nhập số tiền thu hộ">
                                    <span style="color: red" id="cod" class="help-block"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <h3><b>Từ</b></h3>
                            </div>
                            <div class="col-lg-6">
                                <h3><b>Đến</b></h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id_fr', \App\Models\Province::getProvinceOption() , old('province_id_fr'),
                                 ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_fr', 'onchange'=>'loadDistrictFrom()']) }}
                            </div>
                            <div class="col-lg-6">
                                <label>Tỉnh/Thành phố</label>
                                {{ Form::select('province_id_to', \App\Models\Province::getProvinceOption() , old('province_id_to'),
                                ['class' => 'form-control', 'style' => 'width:100%', 'id'=>'province_to', 'onchange'=>'loadDistrictTo()']) }}
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <label>Quận/Huyện</label>
                                <select id="district_fr" onchange="loadWardFrom(this.value)"
                                        name="district_id_fr"
                                        class="form-control">
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label>Quận/Huyện</label>
                                <select id="district_to" onchange="loadWardTo(this.value)"
                                        name="district_id_to"
                                        class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px">
                            <div class="col-lg-6">
                                <label>Xã/Phường</label>
                                <select id="ward_fr" name="ward_id_fr" class="form-control">
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label>Xã/Phường</label>
                                <select id="ward_to" name="ward_id_to" class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px; margin-bottom: 20px">
                            <div class="col-lg-6">
                                <label>Số nhà / tên đường</label>
                                <input name="search_home_number_fr" class="form-control spinner" type="email"
                                       placeholder="Nhập số nhà / tên đường">
                                <span style="color: red" id="home_number_fr" class="help-block"></span>
                            </div>
                            <div class="col-lg-6">
                                <label>Số nhà / tên đường</label>
                                <input name="search_home_number_to" class="form-control spinner" type="email"
                                       placeholder="Nhập số nhà / tên đường">
                                <span style="color: red" id="home_number_to" class="help-block"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12" style="float: left">
                                <h4 style="float: left">Kết quả: </h4><h5 id="result"
                                                                          style="float: left; margin: 5px 0px 0px 10px; color: red"></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="searchPrice()">Tra cứu</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal_loading"></div>
@push('css')
    <style>
        .modal_loading {
            display: none;
            position: fixed;
            z-index: 1000;
            top: -200px;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(255, 255, 255, .8) url('http://i.stack.imgur.com/FhHRx.gif') 50% 50% no-repeat;
        }

        /* When the body has the loading class, we turn
           the scrollbar off with overflow:hidden */
    </style>
@endpush
@push('script')
    <script>
        loadDistrictFrom();
        loadDistrictTo();

        /*function checkCOD() {
            var data = $('select[name="search_transport_type"]').val();
            if (data === '4') {
                $('input[name="search_cod"]').removeAttr("readonly");
            } else {
                $('input[name="search_cod"]').attr("readonly", "readonly");
            }
        }*/

        function loadDistrictFrom() {
            var province_fr = $('#province_fr').val();
            $("#district_fr option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district')}}/' + province_fr
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('district_id_fr')}}') {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ("{{old('district_id_fr')}}") {
                    loadWardFrom('{{old('district_id_fr')}}');
                } else {
                    loadWardFrom(msg[0]['id']);
                }
            });
        }

        function loadWardFrom(id) {
            $("#ward_fr option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('ward_id_fr')}}') {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_fr"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }

        function loadDistrictTo() {
            var province_to = $('#province_to').val();
            $("#district_to option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_district')}}/' + province_to
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('district_id_to')}}') {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="district_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
                if ("{{old('district_id_to')}}") {
                    loadWardTo("{{old('district_id_to')}}");
                } else {
                    loadWardTo(msg[0]['id']);
                }
            });
        }

        function loadWardTo(id) {
            $("#ward_to option[value!='-1']").remove();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/get_ward/')}}/' + id
            }).done(function (msg) {
                var i;
                for (i = 0; i < msg.length; i++) {
                    if (msg[i]['id'] == '{{old('ward_id_to')}}') {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '" selected>' + msg[i]['name'] + '</option>')
                    } else {
                        $('select[name="ward_id_to"]').append('<option value="' + msg[i]['id'] + '">' + msg[i]['name'] + '</option>')
                    }
                }
            });
        }

        function searchPrice() {
            $(".modal-content").hide();
            $("#searchPrice").addClass("modal_loading");
            var home_number_fr = $('input[name = "search_home_number_fr"]').val();
            var home_number_to = $('input[name = "search_home_number_to"]').val();
            $.ajax({
                type: "GET",
                url: '{!! url('/ajax/search_price/') !!}',
                data: {
                    receive_type: $('select[name="search_receive_type"]').val(),
                    transport_type: $('select[name="search_transport_type"]').val(),
                    weight: $('input[name="search_weight"]').val(),
                    cod: $('input[name="search_cod"]').val(),
                    district_fr: $('select[name="district_id_fr"]').val(),
                    district_to: $('select[name="district_id_to"]').val(),
                    ward_fr: $('select[name="ward_id_fr"]').val(),
                    ward_to: $('select[name="ward_id_to"]').val(),
                    province_fr: $('select[name="province_id_fr"]').val(),
                    province_to: $('select[name="province_id_to"]').val(),
                    home_number_fr: home_number_fr,
                    home_number_to: home_number_to,
                    type: 'index'
                }
            }).done(function (res) {
                $('#weight').text('');
                $('#cod').text('');
                $('#home_number_fr').text('');
                $('#home_number_to').text('');
                if (typeof res == 'object') {
                    $.each(res, function (k, v) {
                        $('#' + k).text(v);
                    });
                } else {
                    $('#result').text(res + ' vnđ');
                }
                $("#searchPrice").removeClass("modal_loading");
                $(".modal-content").show();
            });
        }

        function searchBooking() {
            var id = $('#booking_id').val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/search_booking')}}/' + id
            }).done(function (res) {
                if (typeof res == 'object') {
                    $.each(res, function (key, value) {
                        if (key == 'completed_at') {
                            $('#ui_search_booking').append(" <li id='completed'><label>Ngày hoàn thành:</label> <input type=\"text\" name='" + key + "' value='" + value + "' readonly/> </li>")
                        }
                        if (key == 'return_at') {
                            $('#ui_search_booking').append(" <li id='completed'><label>Ngày trả lại:</label> <input type=\"text\" name='" + key + "' value='" + value + "' readonly/> </li>")
                        }
                        if (key == 'cancel_at') {
                            $('#ui_search_booking').append(" <li id='completed'><label>Ngày hủy đơn:</label> <input type=\"text\" name='" + key + "' value='" + value + "' readonly/> </li>")
                        }
                        $('input[name = ' + key + ']').val(value);
                    });
                    $("#searchBooking").modal('show');
                }else {
                    alert(res)
                }
            });
        }
    </script>
@endpush
