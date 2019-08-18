<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.partial.header_html')
</head>
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
<div class="page-wrapper">
    <!-- BEGIN HEADER -->
    <div class="page-header navbar navbar-fixed-top">
        @include('admin.partial.header')
    </div>
    <div class="clearfix"></div>
    <div class="page-container">
        <div class="page-sidebar-wrapper">
            <div class="page-sidebar navbar-collapse collapse">
                @include('admin.partial.sidebar')
            </div>
        </div>
        <div class="page-content-wrapper">
            <div class="page-content">
                {{--@include('admin.partial.setting')--}}
                <div class="page-bar">
                    <ul class="page-breadcrumb">
                        @php( $num = count($breadcrumb))
                        @foreach($breadcrumb as $key => $value)
                            <li>
                                <a href="#">{!! $value !!}</a>
                                @if($key != $num-1)
                                    <i class="fa fa-circle"></i>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="page-toolbar">
                        <div id="dashboard-report-range" class="pull-right tooltips btn btn-sm" data-container="body"
                             data-placement="bottom" data-original-title="Change dashboard date range">
                            <i class="icon-calendar"></i>&nbsp;
                            <span class="thin uppercase hidden-xs"></span>&nbsp;
                            <i class="fa fa-angle-down"></i>
                        </div>
                    </div>
                </div>
                <h1 class="page-title"> @yield('title')
                    <small><i class="fa fa-angle-right" aria-hidden="true"></i> @yield('sub-title')</small>
                </h1>
                <div class="clearfix"></div>
                @yield('content')
            </div>
        </div>
        @include('admin.partial.quick_sidebar')
    </div>
    <div class="page-footer">
        @include('admin.partial.footer')
    </div>
</div>
@include('admin.partial.script')
</body>
</html>