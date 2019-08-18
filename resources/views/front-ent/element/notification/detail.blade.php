@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Thông báo</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Chi tiết thông báo</b> </span>
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
                            <li class="nav-item {{isset($active) && $active == 'promotion' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/notifications?type=promotion') }}">Thông báo khuyến mãi</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'book' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/notifications?type=book') }}">Thông báo đơn hàng</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'handle' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/notifications?type=handle') }}">Thông báo từ admin</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="row order-form">
                    <div class="col-md-12 col-sm-12" style="padding-top: 20px">
                        <table style="width: 100%">
                            <tr>
                                <td style="width: 20%; padding: 10px 0;">Tiêu đề</td>
                                <td style="width: 80%">{!! $notification->title !!}</td>
                            </tr>
                            <tr>
                                <td>Nội dung</td>
                                <td style="width: 20%; padding: 10px 0;">{!! nl2br($notification->content) !!}</td>
                            </tr>
                            @if($type == 'promotion')
                            <tr>
                                <td>Ngày khuyến mãi</td>
                                <td style="width: 20%; padding: 10px 0;">
                                    @if(!empty($notification->start_date) && !empty($notification->end_date))
                                    {!! date('d/m/Y', strtotime($notification->start_date)) !!} - {!! date('d/m/Y', strtotime($notification->end_date)) !!}
                                    @elseif(!empty($notification->start_date))
                                    {!! date('d/m/Y', strtotime($notification->start_date)) !!}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>Ngày tạo</td>
                                <td style="width: 20%; padding: 10px 0;">{!! date('d/m/Y', strtotime($notification->created_at)) !!}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="width: 20%; padding: 10px 0;"><a href="{{ url('front-ent/notifications?type=' . $type . '&page=' . $page) }}" style="color: white;" class="btn btn-sm btn-info">Quay lại</a></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->
@endsection
