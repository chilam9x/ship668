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
                            <li class="nav-item {{isset($active) && $active == 'totalPrice' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/total-price') }}">Tổng tiền cước</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'totalCOD' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/total-COD') }}">Tổng tiền thu hộ COD</a>
                            </li>
                            <li class="nav-item {{isset($active) && $active == 'wallet' ? 'active' : ''}}">
                                <a class="nav-link" href="{{ url('/front-ent/wallet') }}">Ví tiền</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="row">
            <h2 class="col-md-10 offset-md-1 mt-4" align="right">
                Tổng đơn hàng: 
                <span class="text-success">{{ number_format($count) }}</span>
            </h2>
            <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Mã đơn hàng</th>
                        <th scope="col">Tên đơn hàng</th>
                        <th scope="col">Địa chỉ gửi</th>
                        <th scope="col">Địa chỉ nhận</th>
                        <th scope="col">Ngày đặt hàng</th>
                        <th scope="col">Tiền cước (VND)</th>
                        <th scope="col">Phụ phí (VND)</th>
                        <th scope="col">Tiền thu hộ COD (VND)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($bookings) && count($bookings) > 0)
                        @foreach($bookings as $b)
                            <tr>
                                <th scope="row">{!! $b->uuid !!}</th>
                                <td>{!! $b->name !!}</td>
                                <td>{!! $b->send_full_address !!}</td>
                                <td>{!! $b->receive_full_address !!}</td>
                                <td>{!! $b->created_at !!}</td>
                                <td class="text-success">{{ number_format($b->price) }}</td>
                                <td class="text-success">{{ number_format($b->incurred) }}</td>
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