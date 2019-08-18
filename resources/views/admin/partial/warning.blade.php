@if (isset($successMsg)))
<div class="alert alert-success alert-dismissable">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <p>{{$successMsg}}</p>
</div>
@elseif (\Session::has('successMsg'))
    <div class="alert alert-success alert-dismissable">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <p>{!! \Session::get('successMsg') !!}</p>
    </div>
@endif

@if (isset($errorMsg))
    <div class="alert alert-danger alert-dismissable">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <p>{{$errorMsg}}</p>
    </div>
@elseif (\Session::has('errorMsg'))
    <div class="alert alert-danger alert-dismissable">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <p>{!! \Session::get('errorMsg') !!}</p>
    </div>
@endif