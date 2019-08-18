@if (\Session::has('warning'))
    <div class="alert alert-warning">
        <p>{!! \Session::get('warning') !!}</p>
    </div>
@endif