<ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true"
    data-slide-speed="200" style="padding-top: 20px">
    <li class="sidebar-toggler-wrapper hide">
        <div class="sidebar-toggler">
            <span></span>
        </div>
    </li>
    {{--<li class="sidebar-search-wrapper">--}}
    {{--<form class="sidebar-search  " action="page_general_search_3.html" method="POST">--}}
    {{--<a href="javascript:;" class="remove">--}}
    {{--<i class="icon-close"></i>--}}
    {{--</a>--}}
    {{--<div class="input-group">--}}
    {{--<input type="text" class="form-control" placeholder="Search...">--}}
    {{--<span class="input-group-btn">--}}
    {{--<a href="javascript:;" class="btn submit">--}}
    {{--<i class="icon-magnifier"></i>--}}
    {{--</a>--}}
    {{--</span>--}}
    {{--</div>--}}
    {{--</form>--}}
    {{--</li>--}}


    {{--  <li class="heading">
          <h3 class="uppercase">Chức năng</h3>
      </li>--}}
    <li class="nav-item start @if(isset($active)&& $active == 'report') active @endif">
        <a href="{{ url('/admin/report') }}" class="nav-link">
            <i class="fa fa-line-chart" aria-hidden="true"></i>
            <span class="title">Quản lý doanh thu</span>
            @if(isset($active)&& $active == 'report')<span class="selected"></span>
            @endif
        </a>
    </li>
    @if(\Auth::user()->role == 'admin')
        <li class="nav-item start @if(isset($active)&& $active == 'qrcode') active @endif">
            <a href="{{ url('/admin/qrcode') }}" class="nav-link">
                <i class="fa fa-qrcode" aria-hidden="true"></i>
                <span class="title">Quản lý QR Code</span>
                @if(isset($active)&& $active == 'qrcode')<span class="selected"></span>
                @endif
            </a>
        </li>
        <li class="nav-item start @if(isset($active)&& $active == 'district_type') active @endif">
            <a href="{{ url('/admin/district_type') }}" class="nav-link">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                <span class="title">Quản lý loại quận/huyện</span>
                @if(isset($active)&& $active == 'district_type')<span class="selected"></span>
                @endif
            </a>
        </li>
    @endif
    <li class="nav-item start @if(isset($active)&& $active == 'col_register' || $active == 'shipper_register') active open @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-user-plus" aria-hidden="true"></i>
            <span class="title">Quản lý lượt đăng ký mới</span>
            <span class="arrow @if(isset($active)&& $active == 'col_register' || $active == 'shipper_register') open @endif"></span>
            @if(isset($active)&& $active == 'col_register'|| $active == 'shipper_register')<span class="selected"></span>
            @endif
        </a>
        <ul class="sub-menu">
            @if(\Auth::user()->role == 'admin')
                <li class="nav-item start @if(isset($active)&& $active == 'col_register') active @endif">
                    <a href="{{ url('/admin/register/agency') }}" class="nav-link">
                        <span class="title">Cộng tác viên</span>
                    </a>
                </li>
            @endif
            <li class="nav-item   @if($active == 'shipper_register') active @endif">
                <a href="{{ url('/admin/register/shippers') }}" class="nav-link">
                    <span class="title">Shipper</span>
                </a>
            </li>
        </ul>

    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'collaborators' || $active == 'shipper' || $active == 'agency' || $active == 'warehouse') active open @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-user" aria-hidden="true"></i>
            <span class="title">Quản lý thành viên</span>
            <span class="arrow @if(isset($active)&& $active == 'collaborators' || $active == 'shipper' || $active == 'warehouse') open @endif"></span>
            @if(isset($active)&& $active == 'user'|| $active == 'shipper')<span class="selected"></span>
            @endif
        </a>
        <ul class="sub-menu">
            @if(\Auth::user()->role == 'admin')
                <li class="nav-item start @if(isset($active)&& $active == 'agency') active @endif">
                    <a href="{{ url('/admin/agencies') }}" class="nav-link">
                        {{--<i class="fa fa-shopping-cart" aria-hidden="true"></i>--}}
                        <span class="title">Quản lý đại lý</span>
                    </a>
                </li>
                <li class="nav-item   @if($active == 'collaborators') active @endif">
                    <a href="{{ url('/admin/collaborators') }}" class="nav-link">
                        {{--<i class="fa fa-user" aria-hidden="true"></i>--}}
                        <span class="title">Cộng tác viên</span>
                    </a>
                </li>
            @endif
            <li class="nav-item   @if($active == 'shipper') active @endif">
                <a href="{{ url('/admin/shippers') }}" class="nav-link">
                    {{--<i class="fa fa-user" aria-hidden="true"></i>--}}
                    <span class="title">Shipper</span>
                </a>
            </li>
            <li class="nav-item   @if($active == 'warehouse') active @endif">
                <a href="{{ url('/admin/warehouse') }}" class="nav-link">
                    {{--<i class="fa fa-user" aria-hidden="true"></i>--}}
                    <span class="title">Quản lý kho</span>
                </a>
            </li>
        </ul>

    </li>
    <li class="nav-item  @if(isset($active)&& $active == 'customer' || $active == 'partner') active @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-users"></i>
            <span class="title">Đối tác & khách hàng</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            @if(\Auth::user()->role == 'admin')
                <li class="nav-item start @if(isset($active)&& $active == 'partner') active @endif">
                    <a href="{{ url('/admin/partners') }}" class="nav-link">
                        <span class="title">Quản lý đối tác</span>
                    </a>
                </li>
            @endif
            <li class="nav-item  @if(isset($active)&& $active == 'customer') active @endif">
                <a href="{{ url('/admin/customers') }}" class="nav-link ">
                    <span class="title">Quản lý khách hàng</span>
                </a>
            </li>

        </ul>
    </li>

    {{-- <li class="nav-item  ">
         <a href="javascript:;" class="nav-link nav-toggle">
             <i class="fa fa-file"></i>
             <span class="title">Quản lý đơn hàng</span>
         </a>
         <ul class="sub-menu">
             <li class="nav-item  ">
                 <a href="ui_tiles.html" class="nav-link ">
                     <span class="title">Đơn hàng chưa giao</span>
                 </a>
             </li>
             <li class="nav-item  ">
                 <a href="ui_datepaginator.html" class="nav-link ">
                     <span class="title">Đơn hàng đã giao</span>
                 </a>
             </li>
         </ul>
     </li>--}}
    <li class="nav-item @if(isset($active)&& $active == 'received' || $active == 'sent' || $active == 'delay' ||  $active == 'move_booking' ||
    $active == 'cancel' || $active == 'new_booking'|| $active == 'create'|| $active == 'deny') active @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-home"></i>
            <span class="title">Quản lý đơn hàng</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item @if(isset($active)&& $active == 'create') active @endif">
                <a href="{{ url('/admin/booking/create') }}" class="nav-link ">
                    <span class="title">Tạo đơn hàng thủ công</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'new_booking') active @endif">
                <a href="{{ url('/admin/booking/new') }}" class="nav-link ">
                    <span class="title">Đơn hàng mới</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'received') active @endif">
                <a href="{{ url('/admin/booking/received') }}" class="nav-link ">
                    <span class="title">Đơn hàng chưa giao</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'move_booking') active @endif">
                <a href="{{ url('/admin/booking/move_booking') }}" class="nav-link ">
                    <span class="title">Đơn hàng chuyển kho</span>
                </a>
            </li>
            <li class="nav-item start @if(isset($active)&& $active == 'delay') active @endif">
                <a href="{{ url('/admin/booking/delay') }}" class="nav-link">
                    {{--<i class="fa fa-file" aria-hidden="true"></i>--}}
                    <span class="title">Đơn hàng Delay</span>
                </a>
            </li>
            <li class="nav-item start @if(isset($active)&& $active == 'deny') active @endif">
                <a href="{{ url('/admin/booking/return') }}" class="nav-link">
                    {{--<i class="fa fa-file" aria-hidden="true"></i>--}}
                    <span class="title">Đơn hàng giao tiếp/trả lại</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'cancel') active @endif">
                <a href="{{ url('/admin/booking/cancel') }}" class="nav-link ">
                    <span class="title">Đơn hàng bị hủy</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'sent') active @endif">
                <a href="{{ url('/admin/booking/sent') }}" class="nav-link ">
                    <span class="title">Đơn hàng đã hoàn tất</span>
                </a>
            </li>
        </ul>
    </li>
    {{--    <li class="nav-item @if(isset($active)&& $active == 'cus_discount' || $active == 'age_discount'|| $active == 'par_discount') active @endif">
            <a href="javascript:;" class="nav-link nav-toggle">
                <i class="fa fa-calculator" aria-hidden="true"></i>
                <span class="title"></span>
                <span class="arrow"></span>
            </a>
            <ul class="sub-menu">
                <li class="nav-item @if(isset($active)&& $active == 'cus_discount') active @endif">
                    <a href="{{ url('/admin/discount/customer') }}" class="nav-link ">
                        <span class="title">Chiết khấu khách hàng</span>
                    </a>
                </li>
                <li class="nav-item @if(isset($active)&& $active == 'age_discount') active @endif">
                    <a href="{{ url('/admin/discount/agency') }}" class="nav-link ">
                        <span class="title">Chiết khấu đại lý</span>
                    </a>
                </li>
                <li class="nav-item start @if(isset($active)&& $active == 'par_discount') active @endif">
                    <a href="{{ url('/admin/discount/partner') }}" class="nav-link">
                        --}}{{--<i class="fa fa-file" aria-hidden="true"></i>--}}{{--
                        <span class="title">Chiết khấu đối tác</span>
                    </a>
                </li>
            </ul>
        </li>--}}
    @if(\Auth::user()->role == 'admin')
        <li class="nav-item start @if(isset($active)&& $active == 'discount') active @endif" p>
            <a href="{{ url('/admin/discounts') }}" class="nav-link">
                <i class="fa fa-calculator" aria-hidden="true"></i>
                <span class="title">Quản lý giá trị</span>
                @if(isset($active)&& $active == 'discount')<span class="selected"></span>
                @endif
            </a>
        </li>
    @endif{{--
    <li class="nav-item start @if(isset($active)&& $active == 'cod') active @endif">
        <a href="{{ url('/admin/cod') }}" class="nav-link">
            <i class="fa fa-money" aria-hidden="true"></i>
            <span class="title">Thu Hộ</span>
            @if(isset($active)&& $active == 'cod')<span class="selected"></span>
            @endif
        </a>
    </li>--}}
    @if(\Auth::user()->role == 'admin')
        <li class="nav-item start @if(isset($active)&& $active == 'price') active @endif">
            <a href="{{ url('/admin/price') }}" class="nav-link">
                <i class="fa fa-usd" aria-hidden="true"></i>
                <span class="title">Quản lý giá cước</span>
                @if(isset($active)&& $active == 'price')<span class="selected"></span>
                @endif
            </a>
        </li>
    @endif
    <li class="nav-item @if(isset($active)&& ($active == 'non_payment' || $active == 'paymented')) active @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="fa fa-money" aria-hidden="true"></i>
            <span class="title">Rút tiền</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item @if(isset($active)&& $active == 'non_payment') active @endif">
                <a href="{{ url('/admin/wallet/non-payment') }}" class="nav-link ">
                    <span class="title">Chưa thanh toán</span>
                </a>
            </li>
            <li class="nav-item @if(isset($active)&& $active == 'paymented') active @endif">
                <a href="{{ url('/admin/wallet/paymented') }}" class="nav-link ">
                    <span class="title">Đã thanh toán</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item @if(isset($active)&& $active == 'notification-handle') active @endif">
        <a href="javascript:;" class="nav-link nav-toggle">
            <i class="icon-bell" aria-hidden="true"></i>
            <span class="title">Quản lý thông báo</span>
            <span class="arrow"></span>
        </a>
        <ul class="sub-menu">
            <li class="nav-item @if(isset($active)&& $active == 'notification-handle') active @endif">
                <a href="{{ url('/admin/notification-handles') }}" class="nav-link ">
                    <span class="title">Thông báo thủ công</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'promotions') active @endif">
        <a href="{{ url('/admin/promotions') }}" class="nav-link">
            <i class="fa fa-ticket" aria-hidden="true"></i>
            <span class="title">Chương trình khuyến mãi</span>
            @if(isset($active)&& $active == 'promotions')<span class="selected"></span>
            @endif
        </a>
    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'policies') active @endif">
        <a href="{{ url('/admin/policies') }}" class="nav-link">
            <i class="fa fa-bicycle" aria-hidden="true"></i>
            <span class="title">Đồng hành cùng bạn</span>
            @if(isset($active)&& $active == 'policies')<span class="selected"></span>
            @endif
        </a>
    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'search_price') active @endif">
        <a href="{{ url('/admin/search_price') }}" class="nav-link">
            <i class="fa fa-search" aria-hidden="true"></i>
            <span class="title">Tra cứu trước cước</span>
            @if(isset($active)&& $active == 'search_price')<span class="selected"></span>
            @endif
        </a>
    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'feedback') active @endif">
        <a href="{{ url('/admin/feedback') }}" class="nav-link">
            <i class="fa fa-comment-o" aria-hidden="true"></i>
            <span class="title">Phản hồi</span>
            @if(isset($active)&& $active == 'feedback')<span class="selected"></span>
            @endif
        </a>
    </li>
    <li class="nav-item start @if(isset($active)&& $active == 'version') active @endif">
        <a href="{{ url('/admin/versions') }}" class="nav-link">
            <i class="fa fa-level-up" aria-hidden="true"></i>
            <span class="title">Version</span>
            @if(isset($active)&& $active == 'version')<span class="selected"></span>
            @endif
        </a>
    </li>
</ul>