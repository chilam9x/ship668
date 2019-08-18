@extends('front-ent.app')
@section('content')
    <!-- BANNER -->
    <section class="banner-sub">
        <div class="container">
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <h1>Thông báo</h1>
                    <span><a href="{!! url('/') !!}">Trang chủ</a> / <b>Danh sách thông báo</b> </span>
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
                <table class="table table-bordered">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Tiêu đề</th>
                        <th scope="col">Hành động</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($notifications) && count($notifications) > 0)
                        @foreach($notifications as $b)
                            <tr>
                                <td scope="row">
                                    @if ($b->is_readed == 1)
                                    {!! $b->title !!}
                                    @else
                                    <b>{!! $b->title !!}</b>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: inline-flex;">
                                        <a href="{{ url('front-ent/notifications/detail/' . $b->notification_id) . '?type=' . $b->type . '&page=' . $notifications->currentPage() }}" style="color: white;" class="btn btn-sm btn-info">Chi tiết</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                @if ($notifications->lastPage() > 1)
                    @include('front-ent.custom.pagination', ['obj' => $notifications])
                @endif
            </div>
        </div>
    </section>
    <!-- COPYRIGHT -->
@endsection

@push('script')
    <script>
    </script>
@endpush
