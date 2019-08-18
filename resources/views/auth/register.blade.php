@extends('auth.app')
@section('main')
    <form class="login-form" action="{!! url('/registerPage') !!}" method="post">
        {{ csrf_field() }}
        <h3 class="font-green">Register</h3>
        <p class="hint"> Enter your personal details below: </p>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Username</label>
            <input class="form-control" type="text" value="{!! old('name') !!}" placeholder="Username" name="name"/>
            @if ($errors->has('name'))
                <div class="error" style="color: red">{{ $errors->first('name') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Email</label>
            <input class="form-control" type="text" value="{!! old('email') !!}" placeholder="Email" name="email"/>
            @if ($errors->has('email'))
                <div class="error" style="color: red">{{ $errors->first('email') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Password</label>
            <input class="form-control" type="password"  placeholder="Password" name="password"/>
            @if ($errors->has('password'))
                <div class="error" style="color: red">{{ $errors->first('password') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9">Confirm Your Password</label>
            <input class="form-control" type="password" placeholder="Confirm Your Password" name="confirm"/>
            @if ($errors->has('confirm'))
                <div class="error" style="color: red">{{ $errors->first('confirm') }}</div>
            @endif</div>
        <div class="form-actions">
            <a href="{!! url('login') !!}" class="btn green btn-outline">Back</a>
            <button type="submit" class="btn btn-success uppercase pull-right">Submit</button>
        </div>
    </form>
@endsection
