@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Đồng hành cùng bạn</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Đồng hành cùng bạn</b> </span>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </section>
    <!-- SUB CREATE ORDER -->
    <section class="sub-content" style="padding: 5px 0 50px 0 !important;">
        
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="row order-form">
                    <div class="col-md-12 col-sm-12" style="padding-top: 20px">
                        {!! @$policy->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->
@endsection
