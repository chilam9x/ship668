<!-- HEADER - PC -->
<style>
    .dropdown-menu li {
        float: left !important;
        clear: left;
    }
</style>
<div class="container header">
    <div class="row">
        <div class="col-md-3 logo">
            <a href="{!! url('/') !!}"><img alt="Logo" src="{!! asset('/landing_page/images/Logo.png') !!}" width="64%"></a>
        </div>
        <div class="col-md-9">
            <ul class="menu">
                @if(Auth::check())
                    <li class="dropdown" style="margin-top: -5px">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                            <img style="margin-top: -10px" width="40px" alt=""
                                 src="{{Auth::user()->avatar != null ? url(Auth::user()->avatar) : url('/img/default-avatar.jpg')}}">
                            <span class="username username-hide-on-mobile">{!! @Auth::user()->name !!}</span>
                            @if(@Auth::user()->is_vip == 1)
                            <img src="{{ asset('img/vip.png') }}" width="30px" title="Khách hàng VIP">
                            @elseif(@Auth::user()->is_vip == 2)
                            <img src="{{ asset('img/pro.png') }}" width="30px" title="Khách hàng Pro">
                            @endif
                        </a>
                        <ul style="min-width: 200px" class="dropdown-menu">
                            <li>
                                <a href="{{ url('/front-ent/profile/') }}">
                                    <i class="fa fa-user"></i> <b>T</b>rang cá nhân </a>
                            </li>
                            <li>
                                <a href="{{ url('/front-ent/total-price') }}">
                                    <i class="far fa-money-bill-alt"></i> <b>T</b>hu hộ </a>
                            </li>
                            <li>
                                <a href="{{ url('/front-ent/notifications') }}">
                                    <i class="fa fa-bell"></i> <b>T</b>hông báo </a>
                            </li>
                            <li>
                                <a href="{{ url('/front-ent/logout') }}">
                                    <i class="fas fa-sign-out-alt"></i> <b>L</b>og Out </a>
                            </li>
                        </ul>
                    </li>
                @else
                    <li><a href="#" data-toggle="modal" data-target="#loginModal"><i class="fa fa-user"></i> Đăng nhập</a></li>
                @endif
                <li>Hotline: <b>(028) 22 419 555</b></li>
                <li><a href="{{ url('/front-ent/policy') }}">Đồng hành cùng bạn</a></li>
                <li><a href="{!! url('/') !!}">Trang chủ</a></li>
            </ul>
        </div>
    </div>
</div>

<section class="order">
        <div class="container">
            @if(Auth::check())
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <h2>cần gửi hàng tạo đơn hàng ngay</h2>
                            <span>Cá nhân, cửa hàng, doanh nghiệp đều dễ dàng gửi hàng</span>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <a href="{!! url('front-ent/booking/create') !!}">Tạo đơn hàng</a>
                            <a href="{!! url('front-ent/create-book-by-import') !!}">Tạo đơn hàng loạt</a>
                            <a href="{!! url('front-ent/print/book-new-talking') !!}">In đơn hàng loạt</a>
                        </div>
                        <div class="col-md-3 col-sm-12 list_bookings">
                            <a href="{!! url('front-ent/booking/all') !!}">Quản lý đơn hàng</a>
                            <a href="{!! url('front-ent/total-price') !!}">Thu hộ</a>
                            <a href="{!! url('front-ent/notifications') !!}">Thông báo</a>
                        </div>
                    </div>
            @else
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <h2>cần gửi hàng tạo đơn hàng ngay</h2>
                        <span>Cá nhân, cửa hàng, doanh nghiệp đều dễ dàng gửi hàng</span>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <a href="#"  data-toggle="modal" data-target="#loginModal">Tạo đơn hàng</a>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <a href="#"  data-toggle="modal" data-target="#loginModal">Tạo đơn hàng loạt</a>
                    </div>
                </div>
            @endif
        </div>
    </section>