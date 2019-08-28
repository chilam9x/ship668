@extends('admin.app')

@section('title')
QRCode
@endsection

@section('sub-title')
danh sách
@endsection

@section('content')
<div class="row" style="margin-bottom: 20px">
    <div class="well" style="padding-left: 0px">
        <button type="button" class="btn btn-primary" onclick="showModal()">
            <i class="fa fa-plus" aria-hidden="true"></i> Thêm mới</button>
        <span>Đã sử dụng: {{$countQrcodeUsed}}, </span>
        <span>Chưa sử dụng: {{$countQrcodeUnused}}</span>
    </div>
    <form class="form-inline text-right" action="{{ url('/admin/qrcode/find') }}" method="POST">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <div class="form-group">
            <label for="email">Tên code:</label>
            <input type="text" class="form-control" name="name" placeholder="Nhập tên code">
        </div>
        <button type="submit" class="btn btn-default">Tìm</button>
    </form>
</div>
<div class="row">
    <div class="col-lg-12">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>QRcode</th>
                    <th>Tên code</th>
                    <th>Trạng thái</th>
                    <th>Ngày sử dụng</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($qrcode as $q)
                <tr>
                    <td>{{$q->id}}</td>
                    <td> {!! QrCode::size(100)->generate($q->name); !!} </td>
                    <td>{{$q->name}}</td>
                    <td>
                        @if($q->is_used==1)
                        <span class="bg-primary"> Đã sử dụng </span>
                        @else
                        <span class="bg-warning"> Chưa sử dụng </span>
                        @endif
                    </td>
                    <td>{{$q->used_at}}</td>
                    <td>{{$q->created_at}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $qrcode->links() }}
    </div>

</div>
<!-- Modal create qrcode -->
<div id="createQRCode" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Thêm mới qrcode</h4>
            </div>
            <form class="" action="{{ url('/admin/qrcode/create') }}" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="form-group">
                        <label for="email">Số lượng qrcode:</label>
                        <input type="number" class="form-control" name="qrcode" placeholder=" Nhập số lượng QR code ">
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