<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <link rel="shortcut icon" href="{{ asset('public/img/logo.png') }}"/>
    <title>{!! ENV('APP_NAME') !!}</title>
    <link href="{{asset('/css/metronic/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- END HEAD -->
    <style>
        p {
            font-size: 22px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-fixed-top navbar-inverse">
    <div style="padding: 0">
        <div id="navbar" class="collapse navbar-collapse" style="text-align: center">
            <button onclick="printBuild('bill')" style="margin-top: 2px" class="btn btn-lg btn-primary">In hóa đơn
            </button>
            <button onclick="goBack()" style="margin-top: 2px" class="btn btn-lg btn-default">Trở lại</button>
        </div>
    </div><!-- /.container -->
</nav><!-- /.navbar -->
<div class="row row-offcanvas row-offcanvas-right" id="bill" style="margin-top: 55px; border: 1px solid !important;">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-lg-2 pull left">
                <img src="{{asset('public/img/logo.png')}}" width="100%">
            </div>
            <div class="col-lg-10 pull right">
                <h1><b>Dịch vụ vận chuyển hàng hóa Ship668</b></h1>
                <div class="row" style="text-align: left">
                    <div class="col-lg-4">
                        <p><b>Đại lý: </b>{{  @$agency->name != null ? $agency->name : '......................................................' }}</p>
                    </div>
                    <div class="col-lg-8">
                        <P><b>Địa chỉ: </b>{{ @$agency->address != null ? $agency->address : '...........................................................................................................................................' }}</p>
                    </div>
                </div>
                <div class="row" style="text-align: left">
                     <div class="col-lg-6">
                        <p><b>Điện thoại: </b>{{  @$agency->phone != null ? $agency->phone : '................................................................................'}}</p>
                        <p><b>Tên chủ khoản: </b>{{@$collaborator->bank_account != null ? $collaborator->bank_account: '........................................................................' }}</p>
                        <p><b>Ngân hàng: </b>{{ @$collaborator->bank_name != null ? $collaborator->bank_name : '...............................................................................' }}</p>
                        <p>
                    </div>
                    <div class="col-lg-6">
                        <p><b>Fax: </b>.........................................................................................................</p>
                        <p><b>Số tài khoản: </b>{{ @$collaborator->bank_account_number != null ? $collaborator->bank_account_number : '.........................................................................................'}}</p>
                        <p><b>Chi nhánh: </b> {{ @$collaborator->bank_branch != null ? $collaborator->bank_branch : '.............................................................................................'}}</p>
                    </div>
                </div>
                <!-- Footer -->
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-lg-8 pull left" style="text-align: right">
                <H2><b>HÓA ĐƠN VẬN CHUYỂN HÀNG HÓA</b></H2>
                <p><b>Ngày: </b>............
                    <b>Tháng: </b>...........
                    <b>Năm: </b>20..............</p>
            </div>
            <div class="col-lg-3 pull-right" style="text-align: left; margin-top: 25px">
                <p style="color: red"><b style="color: black">Mã đơn hàng: </b>{{@$booking->uuid}}</p>
                <p><b>Tên đơn hàng: </b>{{@$booking->name}}</p>
            </div>
        </div>
        <hr/>
        <div class="row" style="margin-top: 50px">
            <div class="col-lg-6">
                <p><b>Họ tên người gửi: </b>{{@$booking->send_name}}</p>
                <p><b>Số điện thoại: </b>{{@$booking->send_phone}}</p>
                <p><b>Địa chỉ gửi: </b>{{@$booking->send_full_address}}</p>
            </div>
            <div class="col-lg-6">
                <p><b>Họ tên người nhận: </b>{{@$booking->receive_name}}</p>
                <p><b>Số điện thoại: </b>{{@$booking->receive_phone}}</p>
                <p><b>Địa chỉ nhận: </b>{{@$booking->receive_full_address}}</p>
            </div>
            <div class="col-lg-12">
                @if(@$booking->transport_type == 1 )
                    <p><b>Hình thức giao hàng: </b> Giao chuẩn -
                @elseif(@$booking->transport_type == 2)
                    <p><b>Hình thức giao hàng: </b> Giao thường -
                @elseif(@$booking->transport_type == 3)
                    <p><b>Hình thức giao hàng: </b> Giao siêu tốc -
                @else
                    <p><b>Hình thức giao hàng: </b> Giao thu COD -
                        @endif
                        @if(@$booking->receive_type == 1 )
                            <b>Hình thức nhận hàng: </b> Nhận hàng tại nhà -
                        @else
                            <b>Hình thức nhận hàng: </b> Nhận hàng tại bưu cục (giảm 7%) -
                        @endif
                        @if(@$booking->payment_type == 1 )

                            <b>Hình thức thanh toán: </b> Người gửi thanh toán</p>
                    @else

                        <b>Hình thức thanh toán: </b> Người nhận thanh toán</p>
                    @endif
            </div>
        </div>
        <div class="row" style="margin-top: 50px">
            <div class="col-lg-12">
                <div class="bs-example" data-example-id="bordered-table">
                    <table style="font-size: 22px" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>STT</th>
                            <th>Khối lương</th>
                            <th>Phí thu hộ</th>
                            <th>Ghi chú</th>
                            <th>Giá cước</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">1</th>
                            <td>{!! number_format(@$booking->weight) !!}</td>
                            <td>{!! number_format(@$booking->COD) !!}</td>
                            <td>{!! @$booking->other_note !!}</td>
                            <td>{{ number_format(@$booking->price + @$booking->incurred) }} VND</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <p><b>Tổng tiền: </b>
                    @if($booking->status == 'new' || $booking->status == 'taking')
                        {{ $booking->payment_type == 1 ? number_format(@$booking->price + @$booking->incurred) : 0 }}
                    @else
                        {{ $booking->payment_type == 1 ? number_format(@$booking->COD) : number_format(@$booking->price + @$booking->incurred + @$booking->COD) }}
                    @endif
                    VND
                </p>
            </div>
            <div class="col-lg-9">
                <p><b>Tổng tiền viết bằng chữ: </b>.....................................................................................................................................................................
                </p>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-lg-4" style="text-align: center">
                <p><b>Người nhận hàng ký(ghi rõ họ tên)</b></p>
            </div>
            <div class="col-lg-4" style="text-align: center">
                <p><b>Người giao hàng ký(ghi rõ họ tên)</b></p>
            </div>
            <div class="col-lg-4" style="text-align: center">
                <p><b>Xác nhận của đại lý</b></p>
                <p><b>Ngày: </b>............
                    <b>Tháng: </b>...........
                    <b>Năm: </b>20..............</p>
            </div>

        </div>
        <div class="page-footer-inner" style="text-align: center; margin-top: 150px">
            Dịch vụ vận chuyển hàng hóa Smart-express
        </div>
    </div>
</div>
<script src="{{asset('/js/jquery.js')}}" type="text/javascript"></script>
<script src="{{asset('/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script>
    function printBuild(el) {
        var restoragepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restoragepage;
        window.close();
    }

    function goBack() {
        window.history.back();
    }
</script>
</body>


</html>