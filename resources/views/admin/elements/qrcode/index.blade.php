


@extends('admin.app')

@section('title')
    QR Code
@endsection

@section('sub-title')
    Danh sách
@endsection

@section('content')
    <div class="row">
        @include('admin.partial.log.err_log',['name' => 'delete'])
        @include('admin.partial.log.success_log',['name' => 'success'])
        <div class="well" style="padding-left: 0px">
        <button type="button" class="btn btn-primary" onclick="showModal()"><i class="fa fa-plus" aria-hidden="true"></i> Thêm mới</button>
            <span>Đã sử dụng: {{$countQrcodeUsed}}, </span>
            <span>Chưa sử dụng: {{$countQrcodeUnused}}</span>
        </div>

        <div class="col-lg-12">
            @include('admin.table', [
            'id' => 'qrcode',
            'title' => [
                'caption' => 'Dữ liệu QRCode',
                'icon' => 'fa fa-table',
                'class' => 'portlet box green',
            ],
            'url' => url("/ajax/qrcode"),
            'columns' => [
                ['data' => 'id', 'title' => 'ID QRCode'],
                ['data' => 'id_booking', 'title' => 'ID Đơn hàng'],
                ['data' => 'name', 'title' => 'QRCode'],
                ['data' => 'is_used', 'title' => 'Trạng thái'],
                ['data' => 'used_at', 'title' => 'Ngày sử dụng'],
                ['data' => 'created_at', 'title' => 'Ngày tạo'],
            ]])
        </div>
    </div>
    <!-- Modal create qrcode -->
    <div id="createQRCode" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Thêm mới QR Code</h4>
                </div>
                <form class="" action="{{ url('/admin/qrcode/create') }}" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <div class="form-group">
                            <label for="email">Số lượng QR Code:</label>
                            <input type="number" class="form-control" name="qrcode" placeholder=" Nhập số lượng QR Code ">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times"></i> Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Thêm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
function showModal() {
    $('#createQRCode').modal('show');
}
</script>
@endpush