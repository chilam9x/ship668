@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Danh sách thu hộ</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Thu hộ</b> </span>
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
                            <li class="nav-item {{isset($active) && $active == 'pending' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/COD/pending') }}">Ship668s sắp chuyển đến bạn</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'finish' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/COD/finish') }}">Ship668 đã chuyển đến bạn</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row">
            <h2 class="col-md-10 offset-md-1 mt-4" align="right">Tổng COD (VND): <span class="text-success">{{ number_format($count) }}</span></h2>
            <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Mã đơn hàng</th>
                        <th scope="col">Tên đơn hàng</th>
                        <th scope="col">Địa chỉ gửi</th>
                        <th scope="col">Địa chỉ nhận</th>
                        <th scope="col">Ngày đặt hàng</th>
                        @if ($active == 'finish')
                        <th scope="col">Ngày thanh toán</th>
                        @endif
                        <th scope="col">COD (VND)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($bookings))
                        @foreach($bookings as $b)
                            <tr>
                                <th scope="row">{!! $b->uuid !!}</th>
                                <td>{!! $b->name !!}</td>
                                <td>{!! $b->send_full_address !!}</td>
                                <td>{!! $b->receive_full_address !!}</td>
                                <td>{!! $b->created_at !!}</td>
                                @if ($active == 'finish')
                                <td scope="col">{!! $b->payment_date !!}</td>
                                @endif
                                <td class="text-success">{{ number_format($b->COD) }}</td>
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
@endsection