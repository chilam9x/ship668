@extends('admin.app')

@section('title')
    Shipper
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        {{--@if(Auth::user()->role == 'collaborators')--}}
            <div class="well" style="padding-left: 0px">
                <a href="{!! url('admin/shippers/create') !!}" class="btn btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Thêm mới</a>
                <a href="{!! url('admin/shippers/maps') !!}" class="btn btn-info"> <i class="fa fa-location-arrow" aria-hidden="true"></i> Xem trên bản đồ</a>
            </div>
        {{--@endif--}}
        <div class="col-lg-12">
            @include('admin.table_paging', [
               'id' => 'shipper',
               'title' => [
                       'caption' => 'Dữ liệu shipper',
                       'icon' => 'fa fa-table',
                       'class' => 'portlet box green',
               ],
               'url' => url("/ajax/shipper"),
               'columns' => [
                       ['data' => 'name', 'title' => 'Tên'],
                       ['data' => 'uuid', 'title' => 'Mã shipper'],
                       ['data' => 'avatar', 'title' => 'Ảnh đại diện'],
                       ['data' => 'email', 'title' => 'Email'],
                       ['data' => 'phone_number', 'title' => 'Số điện thoại'],
                       ['data' => 'role', 'title' => 'Vai trò'],
                       ['data' => 'status', 'title' => 'Trạng thái'],
                       ['data' => 'agency', 'title' => 'Đại lý'],
                       ['data' => 'revenue_price', 'title' => 'Tổng giá cước đã thu'],
                       ['data' => 'revenue_cod', 'title' => 'Tổng COD đã thu'],
                       ['data' => 'created_at', 'title' => 'Ngày tạo'],
                       ['data' => 'updated_at', 'title' => 'Ngày cập nhật'],
                       ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                   ]
               ])
        </div>
    </div>
    <div class="modal fade" id="export" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: #1d0c09" class="modal-title">Giao diện xuất đơn hàng theo shipper</h4>
                </div>
                <form id="import" method="get" action="{!! url('admin/shippers/list_booking') !!}"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <input id="shipper_id" type="hidden" name="shipper">
                            <div class="col-lg-6">
                                <label>Ngày được phân công: </label>
                                <input type="date" name="date_assign" class="form-control" aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            </div> <div class="col-lg-6">
                                <label>Trạng thái đơn hàng: </label>
                                <select name="status" class="form-control change-status" onchange="changeStatus(this.value);">
                                <option value="receive">Đi lấy</option>
                                            <option value="send">Đi giao</option>
                                            <option value="deny">Trả lại</option>
                                            <option value="all">Tất cả</option>
                                </select>
                            </div>
                            <div class="col-lg-6 date_assign_to" style="display: none">
                                <label>Đến ngày: </label>
                                <input type="date" name="date_assign_to" class="form-control" aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Thực hiện</button>
                        <button onclick="$('#export').modal('hide')" type="button"
                                class="btn btn-default" data-dismiss="modal">Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="statistical" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: #1d0c09" class="modal-title">Thống kê Shipper & Đơn hàng</h4>
                </div>
                <form id="import" method="get" action="{!! url('admin/shippers/list_booking') !!}"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <b>Trong ngày: {{ date('Y-m-d') }}</b> 
                                <ul>
                                    <li>Tổng đơn lấy: <span id="day-receive-book">10</span></li>
                                    <li>Tổng đơn giao: <span id="day-send-book">9</span></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <b>Trong tháng: {{ date('Y-m') }}</b> 
                                <ul>
                                    <li>Tổng đơn lấy: <span id="month-receive-book">100</span></li>
                                    <li>Tổng đơn giao: <span id="month-send-book">99</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="submit" class="btn btn-primary">Thực hiện</button> -->
                        <button onclick="$('#statistical').modal('hide')" type="button"
                                class="btn btn-default" data-dismiss="modal">Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="shipper_paid" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 id="paid_title" style="font-weight: bold; color: #1d0c09" class="modal-title"></h4>
                </div>
                <form id="import" method="get" action="{!! url('admin/shippers/paid') !!}"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <input id="user_id" type="hidden" name="user_id">
                            <input id="type" type="hidden" name="type">
                            <div class="col-lg-8">
                                <label>Số tiền shipper đã thanh toán </label>
                                <input type="number" name="paid" id="paid" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Thực hiện</button>
                        <button onclick="$('#shipper_paid').modal('hide')" type="button"
                                class="btn btn-default" data-dismiss="modal">Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="shipper-online" style="display: none">{{ @$shipperOnline }}</div>
@endsection
@push('script')
    <script>
        $(document).ready(function(){
            var shipperOnline = $('#shipper-online').html();
            console.log(JSON.parse(shipperOnline));
        });

        function exportBooking(id) {
            $('#shipper_id').val(id);
            $('#export').modal('show');
        }
        function shipperPaid(data) {
            $('#user_id').val(data[0]);
            $('#type').val(data[1]);
            $('#paid').val(data[2]);
            if (data[1] === 'price_paid'){
                $('#paid_title').text('Giao diện thanh toán doanh thu tổng giá cước đơn hàng');
            }else {
                $('#paid_title').text('Giao diện thanh toán doanh thu COD');
            }
            $('#shipper_paid').modal('show');
        }

        function changeStatus(value) {
            if (value == 'all') {
                $('.date_assign_to').show();
            } else {
                $('.date_assign_to').hide();
            }
        }

        function statistical(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/statistical-book-shipper')}}',
                data: {id: id},
                dataType: "JSON"
            }).done(function (res) {
                $('#day-receive-book').html(res.day_receive_book);
                $('#day-send-book').html(res.day_send_book);
                $('#month-receive-book').html(res.month_receive_book);
                $('#month-send-book').html(res.month_send_book);
                $('#statistical').modal('show');
            });
        }
    </script>
@endpush


