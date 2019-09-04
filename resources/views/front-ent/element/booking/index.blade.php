@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Danh sách đơn hàng</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Danh sách đơn hàng</b> </span>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- SUB CREATE ORDER -->
    <section class="sub-content" style="padding: 5px 0 50px 0 !important;">
        <div class="row" style="margin-bottom: 10px">
            <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                    <div class="collapse navbar-collapse" id="navbarColor01">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item {{isset($active) && $active == 'all' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/all') }}">Tất cả đơn hàng ({{$countBookStatus['all']}})</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'received' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/received') }}">Đơn hàng đã nhận ({{$countBookStatus['received']}})</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'sent' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/sent') }}">Đơn hàng đã giao ({{$countBookStatus['sent']}})</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'return' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/return') }}">Đơn hàng giao tiếp/trả lại ({{$countBookStatus['return']}})</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'cancel' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/get-cancel') }}">Đơn hàng hủy ({{$countBookStatus['cancel']}})</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'sent' ? 'active' : ''}}">
                                <div class="dropdown">
                                    <a class="dropdown-toggle nav-link" style="cursor: pointer;" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Thu hộ</a>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="top: calc(100% + 5px);">
                                        <a class="dropdown-item" href="{{ url('/front-ent/total-price') }}">Tổng tiền cước</a>
                                        <a class="dropdown-item" href="{{ url('/front-ent/total-COD') }}">Tổng tiền COD</a>
                                        <a class="dropdown-item" href="{{ url('/front-ent/wallet') }}">Ví tiền</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/front-ent/profile') }}">Cập nhật TK ngân hàng</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/front-ent/booking/create') }}">Tạo ĐH mới</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <ul style="padding-left: 10px">
                            <li>- Tỉ lệ đơn đã giao: <b>{{ ($countBookStatus['all'] > 0) ? round($countBookStatus['sent'] * 100 / $countBookStatus['all']) : 0 }}%</b></li>
                            <li>- Tỉ lệ đơn hủy: <b>{{ ($countBookStatus['all'] > 0) ? round($countBookStatus['cancel'] * 100 / $countBookStatus['all']) : 0 }}%</b></li>
                            <li>- Tỉ lệ đơn trả lại: <b>{{ ($countBookStatus['all'] > 0) ? round($countBookStatus['return'] * 100 / $countBookStatus['all']) : 0 }}%</b></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ $url }}" id="form-search">
                            <button type="button" class="btn btn-info" id="export-excel-book" data-toggle="modal" data-target="#export" style="float: right; padding: 6px 10px; margin-right: 5px; margin-bottom: 5px">Xuất Excel đơn hàng</button>
                            <input type="text" class="form-control" name="keyword" value="{{ $keyword }}" id="input-search" placeholder="Nhấn Enter để tìm kiếm" style="width: 250px; float: right; padding: 6px 10px; margin-right: 5px; margin-bottom: 5px">
                        </form>
                    </div>
                </div>
                <!-- <a href="{{ url('front-ent/print/book-new-talking') }}" class="btn btn-info btn-xs" style="float: right; margin-bottom: 5px">In ĐH chưa lấy</a> -->
                <!-- <a href="{{ url('front-ent/create-book-by-import') }}" class="btn btn-info btn-xs" style="float: right; margin-bottom: 5px; margin-right: 5px">Tạo đơn hàng loạt</a> -->
                
            </div>
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">QR Code</th>
                        <th scope="col">Ảnh đơn hàng</th>
                        <th scope="col">Tên đơn hàng</th>
                        <th scope="col">Họ tên</th>
                        <th scope="col">Địa chỉ</th>
                        <th scope="col">Người nhận</th>
                        <th scope="col">SĐT người nhận</th>
                        <th scope="col">Địa chỉ người nhận</th>
                        <th scope="col">Ngày đặt hàng</th>
                        <th scope="col">Ngày hoàn thành</th>
                        <th scope="col">Ghi chú bắt buộc</th>
                        <th scope="col">Khách ghi chú</th>
                        <th scope="col">Ghi chú</th>
                        @if($active == 'all')
                        <th scope="col">Trạng thái</th>
                        @endif
                        <th scope="col">Hành động</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($bookings))
                        @foreach($bookings as $b)
                            <tr>
                                <th> {!! QrCode::size(100)->generate($b->uuid); !!} <br>{!! $b->uuid !!}</th>
                                <th>
                                @if($b->image_order!=null )
                                <img src="/{{$b->image_order}}" width="100">
                                @else
                                <img src='/img/not-found.png' width="100">
                                @endif
                                 </th>
                                <td>{!! $b->name !!}</td>
                                <td>{!! $b->send_name !!}</td>
                                <td>{!! $b->send_full_address !!}</td>
                                <td>{!! $b->receive_name !!}</td>
                                <td>{!! $b->receive_phone !!}</td>
                                <td>{!! $b->receive_full_address !!}</td>
                                <td>{!! $b->created_at !!}</td>
                                <td>{{ $b->completed_at != null ? $b->completed_at : '' }}</td>
                                <td><span class="text-info">{!! $b->payment_type == 1 ? 'Người gửi trả cước' : 'Người nhận trả cước' !!}</span></td>
                                <td><span style="color: red">{!! $b->other_note !!}</span></td>
                                <td>{!! $b->note !!}</td>
                                @if($active == 'all')
                                    @if(isset($b->status))
                                        @if($b->status == 'new')
                                            <td>Mới</td>
                                        @elseif ($b->status == 'return')
                                            <td>Trả lại</td>
                                        @elseif($b->sub_status == 'delay')
                                            <td>Delay</td>
                                        @elseif($b->status == 'cancel')
                                            <td>Hủy</td>
                                         @elseif($b->status == 'taking')
                                            <td>Đang đi lấy</td>
                                        @else
                                            <td>{{ $b->status  == 'sending' ? 'Đang giao hàng' : 'Đã giao hàng'}}</td>
                                        @endif
                                    @else
                                        <td></td>
                                    @endif
                                @endif
                                <td>
                                    <div style="">
                                    <a style="color: white;" onclick="searchBooking('{!! $b->uuid !!}')"
                                            class="btn btn-sm btn-info">Chi tiết
                                    </a><br>
                                    <a style="color: white; margin-top: 5px" href="{{ url('front-ent/booking/print/' . $b->id) }}" class="btn btn-sm btn-info">In
                                    </a><br>
                                    @if($b->status == 'new' || $b->status == 'taking')
                                        <a style="margin-top: 5px" href="{{ url('/front-ent/booking/'.$b->id.'/edit') }}" class="btn btn-sm btn-primary">Chỉnh sửa</a><br>
                                        <a style="margin-top: 5px" href="{{ url('/front-ent/booking/cancel/'.$b->id) }}" class="btn btn-sm btn-danger">Hủy đơn</a><br>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                @if ($bookings->lastPage() > 1)
                    @include('front-ent.custom.pagination', ['obj' => $bookings])
                @endif
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->

    <!-- xuất excel đơn hàng -->
    <div class="modal fade" id="export" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Xuất Excel đơn hàng</h4>
                </div>
                <form id="import" method="get" action="{!! url('front-ent/export-excel-book') !!}"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <!-- <input id="shipper_id" type="hidden" name="shipper"> -->
                            <div class="col-lg-6">
                                <label>Từ ngày: </label>
                                <input type="date" name="date_assign" class="form-control" aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            </div> 
                            <div class="col-lg-6 date_assign_to">
                                <label>Đến ngày: </label>
                                <input type="date" name="date_assign_to" class="form-control" aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            </div>
                            <div class="col-lg-6">
                                <label>Trạng thái đơn hàng: </label>
                                <select name="status" class="form-control change-status" onchange="changeStatus(this.value);">
                                    <option value="new">Mới</option>
                                    <option value="return">Trả lại</option>
                                    <option value="delay">Delay</option>
                                    <option value="cancel">Hủy</option>
                                    <option value="taking">Đang đi lấy</option>
                                    <option value="sending">Đang giao hàng</option>
                                    <option value="completed">Đã giao hàng</option>
                                    <option value="all">Tất cả</option>
                                </select>
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
@endsection

@push('script')
    <script>
        function searchBooking(data) {
            $.ajax({
                type: "GET",
                url: '{{url('/ajax/search_booking')}}/' + data
            }).done(function (res) {
                if (typeof res == 'object') {
                    $('#completed').remove();
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
                } else {
                    alert(res)
                }
            });
        }
    </script>
@endpush
