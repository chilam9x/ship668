@extends('front-ent.app')
@push('css')
    <style>
        .list_bookings a {
            background: #0a6aa1 !important;
        }

        .list_bookings a:hover {
            background: #007a71 !important;
        }
    </style>
@endpush
@section('content')
    <!-- BANNER -->
    <section class="banner">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="banner-title">Ứng dụng <span>Smart Express</span></div>
                    <p>Để trại nghiệm những tính năng mới nhất, tốt nhất. Tạo đơn hàng nhanh chóng và tiện lợi. Quý
                        khách vui lòng tải ứng dụng trên App Store hoặc Google Play.</p>
                    <a target="_blank"
                       href="https://itunes.apple.com/us/app/giao-h%C3%A0ng-smart-express/id1393897193?ls=1&mt=8"><img
                                src="{!! asset('/landing_page/images/download-ios.png') !!}"/></a>
                    <a target="_blank" href="https://play.google.com/store/apps/details?id=com.smartexpress.android">
                        <img src="{!! asset('/landing_page/images/download-android.png') !!}"/></a>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- ORDER -->
    
    <!-- SERVICE -->
    @include('front-ent.layout.feature')
    <!-- INTRO -->
    @include('front-ent.layout.intro')
    <!-- FUNCTION -->
    @include('front-ent.layout.utilities')
    <!-- CONTACT -->
    @include('front-ent.layout.contact')
    @if (session('success'))
        <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" style="max-width: 600px !important;" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" style="color:#00a99d;" id="exampleModalLabel">Thông báo &nbsp; <img
                                    src="{!! asset('/img/corect.png') !!}" width="30px"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {!! session('success') !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('script')
    <script>
        if ("{{session('success')}}" != null) {
            $('#successModal').modal('show');
        }
        $("#btn-login").on("click", function (event) {
            login();
        })

        $(document).keypress(function(e) {
            if(e.which == 13) {
                login();
            }
        })

        function login() {
            var phone = $('#log_phone').val();
            var phonec = Number($('#log_phone').val());
            var password_code = $('#password_code').val();
            if (phone == ''){
                event.preventDefault();
                $('#login_err').text('Số điện thoại không đúng');
                return false;
            }
            if (password_code == ''){
                event.preventDefault();
                $('#login_err_password_code').text('Vui lòng nhập mật khẩu');
                return false;
            }

            $.ajax({
                type: "POST",
                url: "{!! url('/front-ent/login') !!}",
                data: {
                    phone: phone,
                    password_code: password_code,
                    _token: $('[name=_token]').val()
                }
            }).done(function (res) {
                if (res == false) {
                    $('#login_err_password_code').text('Mật khẩu không đúng');
                    return false;
                }
                location.reload();
                // $('#form-login').submit();
            });
        }

    </script>
@endpush