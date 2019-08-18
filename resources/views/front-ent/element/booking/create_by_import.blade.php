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
                                <a class="nav-link" href="{{ url('/front-ent/booking/all') }}">Tất cả đơn hàng</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'received' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/received') }}">Đơn hàng đã nhận</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'sent' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/sent') }}">Đơn hàng đã giao</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'return' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/return') }}">Đơn hàng giao tiếp/trả lại</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'cancel' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/booking/get-cancel') }}">Đơn hàng hủy</a>
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
                                <a class="nav-link" href="{{ url('/front-ent/booking/create') }}">Tạo đơn hàng mới</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <h3 style="margin: 25px 0">Tạo hàng loạt đơn hàng</h3>
                <p>Chức năng tạo hàng loạt đơn hàng từ file Excel của bạn. Hãy upload file Excel theo định dạng sẵn của hệ thống.</p>
                <p>
                    Tải về file Excel mẫu:
                    <a href="{{ asset('file_excel_mau.xlsx') }}">Tải về</a>
                    <!-- <a href="{{ url('front-ent/export-excel-example-book') }}">Tải về</a> -->
                </p>

                {{ Form::open(['url' => 'front-ent/create-book-by-import', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal', 'id' => 'frm-upload']) }}
                    
                    <p>Upload file Excel:</p>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="email">File excel:</label>
                        <div class="col-sm-9" style="display: inline-block;">
                            <input type="file" class="form-control" name="file">
                            @if ($errors->has('file'))
                                @foreach ($errors->get('file') as $error)
                                    <span class="text-danger">{!! $error !!}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="offset-sm-2 col-sm-8" style="display: inline-block;">
                            <button type="button" class="btn btn-default" onclick="frmSubmit()" id="btn-submit">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->
@endsection

@push('script')
<script type="text/javascript">
    function frmSubmit() {
        $('#btn-submit').attr( 'disabled','disabled' );
        $('#frm-upload').submit();
    }
</script>
@endpush
