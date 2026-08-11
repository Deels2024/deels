@extends('layouts.admin.app_neon')

@section('title') @if(! empty($title)) {{$title}} @endif @parent @endsection

@section('content')

    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                <div id="page-wrapper">
                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {{ $title }}  </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    <div class="row">
                        <div class="col-md-12">

                            <div class="account-deletion-wrap">

                                @if( ! $user->is_admin())
                                    @if($user->request_deletion === 1)

                                        <p class="bg-info text-white p-3">Deletion Requested at <strong>{!! date(get_option('date_format') .' '.get_option('time_format') , strtotime($user->request_deletion_time)) !!}</strong></p>
                                    @endif

                                    <div class="alert alert-warning" style="background: #000000">
                                        <h5>@lang('app.account_deletion_warning')</h5>

                                        <h4>@lang('app.below_data_will_be_deleted')</h4>
                                        <h5>=> @lang('app.profile_deletion_info')</h5>
                                        <h5>=> @lang('app.all_of_your_campaigns')</h5>
                                        <h5>=> @lang('app.all_of_your_payments')</h5>
                                        <h5>=> @lang('app.all_of_your_comments')</h5>
                                        <h5>=> @lang('app.withdrawal_data_if_any')</h5>
                                        <h5>=> @lang('app.others_data_deletion_info')</h5>
                                    </div>

                                    @if(request()->user()->is_admin())
                                        <div class="mt-3 mb-3">
                                            <form action="{{route('requested_deletion_action', $user->id)}}" method="post" > @csrf
                                                <input type="hidden" name="redirect_page" value="users">
                                                <button class="btn btn-lg btn-danger mb-2" name="action_type" value="approve_deletion_request"><i class="fa fa-trash-o"></i> @lang('app.confirm_delete') [{!! $user->email !!}] </button>
                                            </form>
                                        </div>
                                    @else
                                        <form action="" method="post"> @csrf

                                            @if( $user->request_deletion !== 1)
                                                <button type="submit" name="delete_account_btn" class="btn btn-lg btn-danger">
                                                    <i class="fa fa-trash-o"></i> @lang('app.still_want_delete')</button>
                                            @else
                                                <button type="submit" name="delete_account_btn" class="btn btn-lg btn-success">
                                                    <i class="fa fa-check-circle-o"></i> @lang('app.changed_mind_info')</button>
                                            @endif
                                        </form>
                                    @endif
                                @else
                                    <div class="alert alert-secondary">
                                        <h4>@lang('app.as_admin_cant_request')</h4>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>   <!-- /#page-wrapper -->

            </div>   <!-- /#wrapper -->


        </div> <!-- /#container -->
    </div> <!-- /#dashboard wrap -->
@endsection

@section('page-js')

@endsection