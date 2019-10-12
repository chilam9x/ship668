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
                <H2 align="center"><b>DANH SÁCH CHI TIẾT ĐƠN HÀNG NỢ CỦA KHÁCH HÀNG</b></H2>
                <p align="center"><b>Từ ngày: {{ date('d/m/Y', strtotime($date_from)) }}</b></p>
                <p align="center"><b>Đến ngày: {{ date('d/m/Y', strtotime($date_to)) }}</b></p>
                <p align="center"><b>Số ĐT: {{ $phone }}</b></p>
            </div>
        </div>
        
        <div class="row" style="margin-top: 50px">
            <div class="col-lg-12">
                <div class="bs-example" data-example-id="bordered-table">
                    <table style="font-size: 22px" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã đơn hàng</th>
                            <th>Tên đơn hàng</th>
                            <th>Tên người gửi</th>
                            <th>Trạng thái COD</th>
                            <th>Giá (VND)</th>
                            <th>Tiền thu hộ (VND)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalPrice = 0; $totalCOD = 0 ?>
                        @foreach ( $booking as $index => $b ) 
                        <?php $totalPrice += $b->price; $totalCOD += $b->COD ?>
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $b->uuid }}</td>
                            <td>{{ $b->name }}</td>
                            <td>{{ $b->send_name }}</td>
                            <td>{{ $b->COD_status }}</td>
                            <td align="right">{{ number_format($b->price) }}</td>
                            <td align="right">{{ number_format($b->COD) }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="row" style="font-size: 22px; text-align: right;">
            <div class="col-lg-10">
                <b>Tổng giá:</b> 
            </div>
            <div class="col-lg-2">
                {{ number_format($totalPrice) }} VND
            </div>
            <div class="col-lg-10">
                <b>Tổng tiền thu hộ:</b> 
            </div>
            <div class="col-lg-2">
                {{ number_format($totalCOD) }} VND
            </div>
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