<!doctype html>
<html lang="en">
@include('front-ent.partial.html-header')
<body>
<!-- HEADER - PC -->
@include('front-ent.partial.header')
@yield('content')
@include('front-ent.partial.footer')
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="max-width: 600px !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#531500;" id="loginModalLabel">Đăng nhập</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-login" action="{{ url('/front-ent/login') }}" method="post" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-6 control-label">Số điện thoại</label>
                        <div class="col-sm-10">
                            <input style="width: 100% !important; padding: 10px 5px !important; !important; float: none !important;" type="text" name="phone" id="log_phone" class="form-control" placeholder="Nhập số điện thoại">
                            <span style="color: red;" id="login_err" class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_code" class="col-sm-6 control-label">Mật khẩu</label>
                        <div class="col-sm-10">
                            <input style="width: 100% !important; padding: 10px 5px !important; !important; float: none !important;" type="password" name="password_code" id="password_code" class="form-control" placeholder="Nhập mật khẩu">
                            <span style="color: red;" id="login_err_password_code" class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <i style="font-size: 15px;">Nhập 3 ký tự bất kì dạng chữ hoặc số nếu bạn chưa có mật khẩu
                            <br><strong>Lưu ý:</strong> bạn phải nhớ để đăng nhập những lần tiếp theo</i>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="button" class="btn btn-default" id="btn-login">Thực hiện</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('front-ent.custom.search_booking')
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
@include('front-ent.partial.script')
</body>
</html>
