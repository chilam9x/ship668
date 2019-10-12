<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="{{ asset('public/img/logo.png') }}"/>
    <title>{!! ENV('APP_NAME') !!}</title>
    <link href="{{asset('public/css/metronic/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('public/css/metronic/components.min.css')}}" rel="stylesheet" id="style_components" type="text/css" />
    <link href="{{asset('public/css/metronic/login.min.css')}}" rel="stylesheet" type="text/css" />
<!-- END HEAD -->

<body class="login">
<div class="content">
    @yield('main')
</div>
<div class="copyright">Copyright &copy; UITShop 2017</div>
<script src="{{asset('public/js/jquery.js')}}" type="text/javascript"></script>
<script src="{{asset('public/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/js/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{ asset('public/js/app.min.js') }}" type="text/javascript"></script>
</body>


</html>