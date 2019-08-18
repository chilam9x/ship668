<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="{{ asset('/img/logo.png') }}"/>
    <title>{!! ENV('APP_NAME') !!}</title>
    <link href="{{asset('/css/metronic/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('/css/metronic/components.min.css')}}" rel="stylesheet" id="style_components" type="text/css" />
    <link href="{{asset('/css/metronic/login.min.css')}}" rel="stylesheet" type="text/css" />
<!-- END HEAD -->

<body class="login">
<div class="content">
    @yield('main')
</div>
<div class="copyright">Copyright &copy; UITShop 2017</div>
<script src="{{asset('/js/jquery.js')}}" type="text/javascript"></script>
<script src="{{asset('/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('/js/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{ asset('/js/app.min.js') }}" type="text/javascript"></script>
</body>


</html>