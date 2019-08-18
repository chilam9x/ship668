@extends('admin.app')

@section('title')
    Thống kê
@endsection

@section('sub-title')
    Thống kê
@endsection

@section('content')
    @push('css')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js"></script>
        <link rel="stylesheet" href="{{ URL::asset('css/report.css')}}">
    @endpush
    <div class="row">
        <div class="col-sm-4">
            <form>
                    <select class="form-control" id="show_by_bookings"
                            style="display:inline-block; width: 200px; margin-right: 5px;">
                        <option disabled selected value>-- Hiển thị theo --</option>
                        <option value="today">Hôm nay</option>
                        <option value="this_week">Tuần này</option>
                        <option value="this_month">Tháng này</option>
                        <option value="this_quarter">Quý này</option>
                        <option value="this_year">Năm này</option>
                        <option value="range_date">Theo ngày</option>
                    </select>
                    <input type="submit" name="bookings_filter" class="btn btn-success btn-md"
                           style="display:inline-block;" value="Thực hiện">
                <!-- Modal -->
                <div id="myModal2" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" style="text-align: center">Chọn ngày để hiển thị</h4>
                            </div>
                            <form method="post" action="{{ url('/admin/get-chart-booking') }}">
                                <div class="modal-body">
                                    <div class="input-group">
                                        <span class="input-group-addon" id="sizing-addon2"><span
                                                    class="glyphicon glyphicon-calendar"> </span> Từ ngày</span>
                                        <input type="date" id="date_from_bookings" class="form-control"
                                               placeholder="Từ ngày" aria-describedby="sizing-addon2">
                                        <span class="input-group-addon" id="sizing-addon2"><span
                                                    class="glyphicon glyphicon-calendar"> </span> Đến ngày</span>
                                        <input type="date" id="date_to_bookings" class="form-control"
                                               placeholder="Đến ngày" aria-describedby="sizing-addon2">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="submit" name="bookings_filter" class="btn btn-success btn-md"
                                           style="display:inline-block;" value="Thực hiện">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End modal -->
            </form>
            <canvas id="bookings_chart" width="200" height="200"></canvas>
        </div>
        <div class="col-sm-8">
            <div class="wrapper">
                <div class="col_fourth">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-user" style="color: white; font-size: 46px"></p>
                                <p>Khách hàng<br> {{number_format($user_counts)}}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/customers') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table"
                                                                                      aria-hidden="true"></i> Chi tiết
                                    </button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="khach_hang" value="1"
                                        data-toggle="modal" data-target="#myModal" style="margin-top:10px"><i
                                            class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col_fourth">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-users" style="color: white; font-size: 46px"></p>
                                <p>Shipper<br> {{number_format($shipper_counts)}} </p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/shippers') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table"
                                                                                      aria-hidden="true"></i> Chi tiết
                                    </button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="giao_hang" value="2"
                                        data-toggle="modal" data-target="#myModal" style="margin-top:10px"><i
                                            class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @if(Auth::user()->role == 'admin')
                    <div class="col_fourth">
                        <div class="hover panel">
                            <div class="front">
                                <div class="box1">
                                    <p class="fa fa-map-marker" style="color: white; font-size: 46px"></p>
                                    <p>Đại lý<br> {{number_format($agency_counts)}} </p>
                                </div>
                            </div>
                            <div class="back">
                                <div class="box2">
                                    <a href="{{ asset('admin/agencies') }}">
                                        <button type="button" class="btn btn-warning"><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                    </a><br/>
                                    <button type="button" class="btn btn-primary" id="daily" value="3" data-toggle="modal"
                                            data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col_fourth end">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-money" style="color: white; font-size: 46px"></p>
                                <p>Tổng doanh thu<br> {{ number_format($sumary).' VND' }}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                {{-- <a href="{{ asset('admin/shippers') }}"><button type="button" class="btn btn-warning"><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button></a> --}}
                                <button type="button" class="btn btn-primary" id="tong_cong" value="4"
                                        data-toggle="modal" data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wrapper" style="">
                <div class="col_fourth" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-undo" style="color: white; font-size: 46px"></p>
                                <p>Tổng đơn hàng <br> {{  number_format($sum_booking) }}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                {{-- <a href="{{ asset('admin/shippers') }}"><button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button></a> --}}
                                <button type="button" class="btn btn-primary " id="sum_booking" value="5"
                                        data-toggle="modal" data-target="#myModal" style="margin-top:10px"><i
                                            class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col_fourth" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-plus-circle" style="color: white; font-size: 46px"></p>
                                <p>Tổng đơn hàng mới<br> {{ number_format($new_booking) }}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/booking/new') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="dh_moi" value="6" data-toggle="modal"
                                        data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col_fourth" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-check-circle-o" style="color: white; font-size: 46px"></p>
                                <p>Tổng đơn thành công<br> {{ number_format($complete_booking) }}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/booking/sent') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="dh_thanhcong" value="7" data-toggle="modal"
                                        data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col_fourth end" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-ban" style="color: white; font-size: 46px"></p>
                                <p>Tổng đơn hàng hủy<br> {{ $cancel_booking }}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/booking/cancel') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                </a> <br/>
                                <button type="button" class="btn btn-primary " id="dh_huy" value="8" data-toggle="modal"
                                        data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col_fourth" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-check-circle-o" style="color: white; font-size: 46px"></p>
                                <p>Đơn hàng giao trong ngày<br> {{ $bookCompleteTodayArr['count'] }} ({{ number_format($bookCompleteTodayArr['amount']) }} VND)</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/booking/sent') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="book-complete-today" value="9" data-toggle="modal"
                                        data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col_fourth" style="margin-top: 10px">
                    <div class="hover panel">
                        <div class="front">
                            <div class="box1">
                                <p class="fa fa-money" style="color: white; font-size: 46px"></p>
                                <p>Tổng COD đã thu trong ngày<br> {{ number_format(@$totalCODToday) }} VND</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="box2">
                                <a href="{{ asset('admin/booking/sent') }}">
                                    <button type="button" class="btn btn-warning "><i class="fa fa-table" aria-hidden="true"></i> Chi tiết</button>
                                </a><br/>
                                <button type="button" class="btn btn-primary " id="total-cod-today" value="10" data-toggle="modal"
                                        data-target="#myModal" style="margin-top:10px"><i class="fa fa-bar-chart" aria-hidden="true"></i> Thống kê
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><b>Thống kê trong khoảng</b></h4>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" id="type_of_report" class="form-control" placeholder="Từ ngày"
                               aria-describedby="sizing-addon2" format="dd/MM/yyyy">
                        <div class="input-group">
                            <span class="input-group-addon" id="sizing-addon2"><span
                                        class="glyphicon glyphicon-calendar"> </span> Từ ngày</span>
                            <input type="date" id="date_from_report" class="form-control" placeholder="Từ ngày"
                                   aria-describedby="sizing-addon2" required="required" value=''>
                            <span class="input-group-addon" id="sizing-addon2"><span
                                        class="glyphicon glyphicon-calendar"> </span> Đến ngày</span>
                            <input type="date" id="date_to_report" class="form-control" placeholder="Đến ngày"
                                   aria-describedby="sizing-addon2" required="required" value='2018-06-01'>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" onclick="loadData()" name="report_by_type" class="btn btn-success btn-md"
                               style="display:inline-block;" value="Thực hiện">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="data-report" tabindex="-1" role="dialog" aria-labelledby="myModalLabel11"
         data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document" style="width:1250px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Dữ liệu</h4>
                </div>
                <div class="modal-body">
                    <table class="display table table-bordered dataTable no-footer">
                        <thead>
                        <tr id="title">
                        </tr>
                        </thead>
                        <tbody id="report">
                        </tbody>

                    </table>
                </div>
                <div><p style="color:black; padding-left: 10px;">Tổng cộng có: <span id='sum'></span></p></div>
                <div class="modal-footer">
                    <form action="{!! url('admin/export') !!}" method="get">
                        <input type="hidden" name="type_export" id="type_export">
                        <input type="hidden" name="date_from" id="date_from">
                        <input type="hidden" name="date_to" id="date_to">
                        <button type="submit" class="btn btn-success">Xuất file Excel</button>
                        <button type="button" class="btn btn-default close-modal" data-dismiss="modal">Đóng</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    {{--modal payment--}}
    <div class="modal fade" id="payment" role="dialog">
        <div class="modal-dialog" style="width: 20%">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: #341907" class="modal-title">Thanh toán chiết khấu đại lý</h4>
                </div>
                <div class="modal-body">
                    <div class="row" style="margin-top: 15px">
                        <input type="hidden" id="agency_id" name="agency_id">
                        <input type="hidden" id="action_type" name="action_type">
                        <div class="col-lg-12">
                            <label>Số tiền thanh toán</label>
                            <input class="form-control spinner" value="0" type="number" id="paid" name="payment" min="0"
                                   placeholder="Nhập số tiền thanh toán">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="paymentAdmin()" class="btn btn-primary"
                            data-dismiss="modal">Thực hiện
                    </button>
                    <button onclick="$('#payment').modal('hide')" type="button"
                            class="btn btn-default" data-dismiss="modal">Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script type="text/javascript" src="{{ URL::asset('js/chartjs.js')}}"></script>
    <script>
        //Ajax Header
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="csrf-token"]').attr('content')
            }
        });
        //Call modal
        $("#show_by_bookings").on("change", function () {
            $modal = $('#myModal2');
            if ($(this).val() === 'range_date') {
                $modal.modal('show');
            }
        });
        // end call modal
        // End Ajax header
        var day = [];
        var month = [];
        var year = [];
        var bookings = [];
        var labels = [];
        var labels_booking = [];

        var ctx = document.getElementById("bookings_chart").getContext('2d');
        var bookings_chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels_booking,
                datasets: [{
                    label: '# Tổng đơn',
                    data: bookings,
                    backgroundColor: 'rgba(76, 175, 80, 0.0)',
                    borderColor: 'rgb(60, 141, 188)',
                    borderWidth: 1
                }]
            },
            options: {
                showLines: true,
                scales: {
                    yAxes: [
                        {
                            id: 'right-y-axis',
                            labelString: 'bookings Value',
                            display: true,
                            type: 'linear',
                            position: 'left',
                            ticks: {

                                suggestedMax: 10,
                                beginAtZero: true,
                                min: 0,
                                stacked: true,
                                display: true,
                            }
                        }],
                    xAxes: [{
                        ticks: {
                            type: 'linear',
                            autoSkip: true,
                            maxTicksLimit: 50,
                            stepSize: 1
                        },
                    }]
                },
                tooltips: {
                    callbacks: {
                        labelColor: function (tooltipItem, chart) {
                            return {
                                backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].borderColor,
                            }
                        }
                    },
                    mode: 'index',
                    animation: false,
                    backgroundColor: 'rgba(0,0,0,0.5)',
                    titleFontSize: 18,
                    _titleAlign: 'top',
                    titleSpacing: 16,
                    titleMarginBottom: 10,
                    bodyFontSize: 16,
                    bodySpacing: 14,
                    width: 400,
                    height: 100,
                },
            }
        });
        // Ajax load page default
        $(window).on('load', (function () {
            $.ajax({

                url: '{{ url("/admin/get-chart-booking") }}',
                type: 'POST',
                dataType: 'html',
                async: false,
                data: {
                    "_token": "{{ csrf_token() }}",
                    'show_by_bookings': 1,
                }
            }).done(function (data_booking_def) {
                obj_booking_def = JSON.parse(data_booking_def);
                for (var f in obj_booking_def) {
                    labels_booking.push(obj_booking_def[f].day);
                    bookings.push(obj_booking_def[f].bookings_counts);
                }
                bookings_chart.update();
            })
        }));
        // End Ajax load page default


        // Ajax load filter
        $("input[name=bookings_filter]").click(function (e) {
            e.preventDefault();
            var show_by_bookings = $('#show_by_bookings').val();
            var date_from_bookings = $('#date_from_bookings').val();
            var date_to_bookings = $('#date_to_bookings').val();
            $.ajax({
                url: "{{ url('/admin/get-chart-booking') }}",
                type: 'POST',
                dataType: 'html',
                async: false,
                data: {
                    "_token": "{{ csrf_token() }}",
                    'date_from_bookings': date_from_bookings,
                    'date_to_bookings': date_to_bookings,
                    'show_by_bookings': show_by_bookings
                }
            }).done(function (data_booking) {
                obj_bookings = JSON.parse(data_booking);
                if (show_by_bookings == 'range_date') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                if (show_by_bookings == 'today') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                if (show_by_bookings == 'this_week') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                if (show_by_bookings == 'this_month') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                if (show_by_bookings == 'this_quarter') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                if (show_by_bookings == 'this_year') {
                    labels_booking.length = 0;
                    bookings.length = 0;
                    for (var n in obj_bookings) {
                        labels_booking.push(obj_bookings[n].day);
                        bookings.push(obj_bookings[n].bookings_counts);
                    }
                }
                bookings_chart.update();
            })
        })
        // End Ajax
    </script>
    <script>
        document.querySelector("#date_from_report").valueAsDate = new Date('2018-01-01');
        document.querySelector("#date_to_report").valueAsDate = new Date();

        // Ajax load filter
        function loadData() {
            var type_of_report = $('#type_of_report').val();
            var date_from_report = $('#date_from_report').val();
            var date_to_report = $('#date_to_report').val();
            $('#type_export').val($('#type_of_report').val());
            $('#date_from').val($('#date_from_report').val());
            $('#date_to').val($('#date_to_report').val());

            $.ajax({
                url: '{{ url("/admin/get-report") }}',
                type: 'POST',
                dataType: 'html',
                async: false,
                data: {
                    "_token": "{{ csrf_token() }}",
                    'date_from_report': date_from_report,
                    'date_to_report': date_to_report,
                    'type_of_report': type_of_report
                }
            }).done(function (data_report) {
                $('td').remove();
                title = '';
                sum = 0;
                $('#data-report').modal();
                data = JSON.parse(data_report);
                $.each(data.data_title, function (i, data_title) {
                    title += '<td>' + data_title + '</td>';
                });
                if (data.action == 3 || data.action == 4) {
                    title += '<td>Hành động</td>'
                }
                var num = 1;
                $.each(data.data_rep, function (f, data_rep) {
                    $('#report').append("<tr id='" + num + "'></tr>");
                    $.each(data_rep, function (d, value) {
                        $("#" + num + "").append('<td>' + value + '</td>');
                    });
                    if (data.action == 3 || data.action == 4) {
                        var params = "{ agency :" + data_rep.id + ", action :" + data.action + "}";
                        $("#" + num + "").append("<td><a class='btn btn-xs btn-primary' onclick='agencyPayment(" + JSON.stringify(params) + ")'>Thanh toán</a></td>");
                    }
                    num += 1;
                });
                sum = data.sum;
                $('#title').append(title);
                $('#report').append(report);
                $('#sum').text(sum);
            })
        }

        function agencyPayment(data) {
            eval('var obj=' + data)
            $("#agency_id").val(obj.agency);
            $("#action_type").val(obj.action);
            $("#payment").modal('show');
        }

        function paymentAdmin() {
            var agency = $("#agency_id").val();
            var paid = $("#paid").val();
            var action_type = $("#action_type").val();
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/payment_agency')}}',
                data: {
                    agency: agency,
                    paid: paid,
                    action_type: action_type
                }
            }).done(function (response) {
                if (response[0] == 'success') {
                    $('#paid').val(0);
                    $('#type_of_report').val(response[1]);
                    loadData()
                } else {
                    alert(response)
                }
            });
        }

        // End Ajax
    </script>
    <script>
        $('#myModal').on('shown.bs.modal', function () {
            $('#myInput').focus()
        });
        $(function () {
            $('#khach_hang').click(function () {
                var inputVal = $('#khach_hang').val();
                $('#type_of_report').val(inputVal);
            });
            $('#giao_hang').click(function () {
                var inputVal = $('#giao_hang').val();
                $('#type_of_report').val(inputVal);
            });
            $('#daily').click(function () {
                var inputVal = $('#daily').val();
                $('#type_of_report').val(inputVal);
            });
            $('#tong_cong').click(function () {
                var inputVal = $('#tong_cong').val();
                $('#type_of_report').val(inputVal);
            });
            $('#dh_moi').click(function () {
                var inputVal = $('#dh_moi').val();
                $('#type_of_report').val(inputVal);
            });
            $('#dh_thanhcong').click(function () {
                var inputVal = $('#dh_thanhcong').val();
                $('#type_of_report').val(inputVal);
            });
            $('#sum_booking').click(function () {
                var inputVal = $('#sum_booking').val();
                $('#type_of_report').val(inputVal);
            });
            $('#dh_huy').click(function () {
                var inputVal = $('#dh_huy').val();
                $('#type_of_report').val(inputVal);
            });
            $('#book-complete-today').click(function () {
                var inputVal = $('#book-complete-today').val();
                $('#type_of_report').val(inputVal);
            });
            $('#total-cod-today').click(function () {
                var inputVal = $(this).val();
                $('#type_of_report').val(inputVal);
            });
        });

    </script>
@endpush

