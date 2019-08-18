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
            <div class="col-md-10 offset-md-1">
                Ví tiền = Tổng tiền thu hộ COD - Tổng tiền cước
            </div>
            <h2 class="col-md-10 offset-md-1 mt-4" align="right">
                Tổng tiền trong ví (VND): 
                <span class="text-success">{{ number_format($count) }}</span>
                @if($count > 0 && $countWallet < 3)
                    <a href="{{ url('front-ent/wallet/withdrawal') }}" class="btn btn-success"><i class="far fa-money-bill-alt"></i> Rút tiền</a>
                @else
                    <button style="float: right; margin-left: 10px" disabled="" class="btn btn-success"><i class="far fa-money-bill-alt"></i> Rút tiền</button>
                @endif
                
            </h2>
            <div class="col-md-10 offset-md-1">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Mã rút tiền</th>
                        <th scope="col">Số tiền rút (VND)</th>
                        <th scope="col">Hình thức rút</th>
                        <th scope="col">Trạng thái thanh toán</th>
                        <th scope="col">Ngày rút</th>
                        <th scope="col">Ngày thanh toán</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($wallets) && count($wallets) > 0)
                        @foreach($wallets as $wallet)
                            <tr>
                                <td>{{ $wallet->payment_code }}</td>
                                <td>{{ number_format($wallet->price) }}</td>
                                <td>
                                    <?php
                                        if (!empty($wallet->withdrawal_type)) {
                                            echo $wallet->withdrawal_type == 'cash' ? 'Tiền mặt' : 'Chuyển khoản'; 
                                        }
                                    ?>
                                            
                                </td>
                                <td><?php echo $wallet->payment_status == 0 ? 'Chưa thanh toán' : 'Đã thanh toán' ?></td>
                                <td>{{ date('d/m/Y', strtotime($wallet->created_at)) }}</td>
                                <td>{{ !empty($wallet->payment_date) ? date('d/m/Y', strtotime($wallet->payment_date)) : '' }}</td>
                                <td><a href="{{ url('front-ent/wallet/list-books', $wallet->id) }}" class="btn btn-primary">Xem DS đơn hàng</a></td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                @if ($wallets->lastPage() > 1)
                    @include('front-ent.custom.pagination', ['obj' => $wallets])
                @endif
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->
@endsection