@if(session('success'))
    <div class="alert alert-success">
        {!! session('success') !!}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {!! session('error') !!}
    </div>
@endif


@if($errors->count() > 0)
    <div class="alert alert-danger">
        <i class="fa fa-info-circle"></i> @lang('app.form_error')
    </div>
@endif