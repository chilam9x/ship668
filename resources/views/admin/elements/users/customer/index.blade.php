@extends('admin.app')

@section('title')
    Khách hàng
@endsection

@section('sub-title')
    danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])

        <div class="well" style="padding-left: 0px">
            <a href="{!! url('admin/customers/create') !!}" class="btn btn-primary"> <i class="fa fa-plus"
                                                                                        aria-hidden="true"></i> Thêm mới</a>
        </div>
        <div class="col-lg-12">
            @include('admin.table_paging', [
                'id' => 'customer',
                'title' => [
                    'caption' => 'Dữ liệu khách hàng',
                    'icon' => 'fa fa-table',
                    'class' => 'portlet box green',
                ],
                'url' => url("/ajax/customer"),
                'columns' => [
                    ['data' => 'name', 'title' => 'Tên'],
                    ['data' => 'avatar', 'title' => 'Ảnh đại diện'],
                    ['data' => 'email', 'title' => 'Email'],
                    ['data' => 'password_code', 'title' => 'Mã mật khẩu'],
                    ['data' => 'phone_number', 'title' => 'Số điện thoại'],
                    ['data' => 'role', 'title' => 'Vai trò'],
                    ['data' => 'status', 'title' => 'Trạng thái'],
                    ['data' => 'owe', 'title' => 'Tổng tiền cước'],
                    ['data' => 'total_COD', 'title' => 'Tổng tiền thu hộ COD'],
                    ['data' => 'wallet', 'title' => 'Ví tiền'],
                    ['data' => 'created_at', 'title' => 'Ngày tạo'],
                    ['data' => 'updated_at', 'title' => 'Ngày cập nhật'],
                    ['data' => 'action', 'title' => 'Hành động', 'orderable' => false]
                ]])
        </div>
    </div>

    <div class="modal fade" id="export" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 style="font-weight: bold; color: #1d0c09" class="modal-title">Xuất đơn hàng theo khách hàng</h4>
                </div>
                <form id="import" method="get" action="{!! url('admin/customers/list_booking') !!}" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row" style="margin-top: 15px">
                            {{csrf_field()}}
                            <input id="customer_id" type="hidden" name="customer_id">
                            <div class="col-lg-6">
                                <label>Ngày tạo: </label>
                                <input type="date" name="date_assign" class="form-control" aria-describedby="sizing-addon2" value="{{\Carbon\Carbon::today()->toDateString()}}">
                            </div> <div class="col-lg-6">
                                <label>Trạng thái đơn hàng: </label>
                                <select name="status" class="form-control change-status" onchange="changeStatus(this.value);">
                                    <option value="all">Tất cả</option>
                                    <option value="new">Mới</option>
                                    <option value="return">Trả lại</option>
                                    <option value="delay">Delay</option>
                                    <option value="cancel">Hủy</option>
                                    <option value="taking">Đang đi lấy</option>
                                    <option value="sending">Đang giao hàng</option>
                                    <option value="sended">Đã giao hàng</option>
                                </select>
                            </div>
                            <div class="col-lg-6 date_assign_to">
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

    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Thông báo nhanh</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(['url' => '', 'method' => 'post', 'class' => 'form-horizontal']) }}
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="title">Tiêu đề*:</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="title" value="" placeholder="Tiêu đề thông báo" name="title">
                            <span id="error-title" class="text-danger" style="display: none;">*Vui lòng nhập tiêu đề</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="content">Nội dung*:</label>
                        <div class="col-sm-10">          
                            <textarea name="content" class="form-control" placeholder="Nội dung thông báo" id="content" cols="30" rows="5"></textarea>
                            <span id="error-content" class="text-danger" style="display: none;">*Vui lòng nhập tiêu đề</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn-send" onclick="" data-dismiss="modal">Gửi</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>

      </div>
    </div>
@endsection

@push('script')
    <script>
        let user_id = 0;
        let title = '';
        let content = '';

        function showModal(userId) {
            user_id = userId;
            $('#myModal').modal('show');
            $('#title').val('');
            $('#content').val('');
        }

        function exportBooking(id) {
            $('#customer_id').val(id);
            $('#export').modal('show');
        }

        $('#btn-send').click(function(e){
            title = $('#title').val();
            content = $('#content').val();
            var token = $('input[name = _token]').val();

            if (title.length == 0) {
                $('#error-title').show();
                return false;
            } else {
                $('#error-title').hide();
            }
            if (content.length == 0) {
                $('#error-content').show();
                return false;
            } else {
                $('#error-content').hide();
            }

            $.ajax({
                type: "POST",
                url: "{{ url('/ajax/add-notification-handle') }}",
                data: {
                    user_id: user_id,
                    title: title,
                    content: content,
                    _token: token
                }
            }).done(function (response) {
                console.log(response);
                if (response.status) {
                    $('#myModal').modal('hide');
                }
            });
        })
    </script>
@endpush
