<!-- App CSS -->
<title>{!! ENV('APP_NAME') !!}</title>
<link rel="shortcut icon" href="{{ asset('public/img/logo.png') }}"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1" name="viewport" />
<meta content="Preview page of Metronic Admin Theme #1 for statistics, charts, recent events and reports" name="description" />
<meta content="" name="author" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.css">

<link href="{{asset('public/assets/global/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/global/plugins/simple-line-icons/simple-line-icons.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/global/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css')}}" rel="stylesheet">

<link href="{{asset('public/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet">

<link href="{{asset('public/assets/global/css/components.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/global/css/plugins.min.css')}}" rel="stylesheet">

<link href="{{asset('public/assets/layouts/layout/css/layout.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/layouts/layout/css/themes/darkblue.min.css')}}" rel="stylesheet">
<link href="{{asset('public/assets/layouts/layout/css/custom.min.css')}}" rel="stylesheet">
<link href="{{asset('public/bower_components/lightbox2/src/css/lightbox.css')}}" rel="stylesheet">
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css"/>
<!-- <link href="{{asset('/css/select2.min.css')}}" rel="stylesheet" /> -->
@stack('css')