@extends('layouts.admin.app')

@section('title') @if(! empty($title)) {!!$title!!} @endif  @parent @endsection

@section('content')

    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">
                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {!! $title !!}  </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    <div class="row">
                        <div class="col-12">

                            <div class="admin-campaign-lists-sub-head">
                                <div class="row">
                                    <div class="col-md-5">
                                        @lang('app.total') : {!!number_format($users_count)!!}
                                    </div>
                                    <div class="col-md-7">
                                        <form method="get" action="">
                                            <div class="form-row align-items-center float-right">
                                                <div class="col-auto">
                                                    <input type="text" name="q" value="{{request('q')}}" class="form-control mb-2" placeholder="Поиск пользователей">
                                                </div>

                                                <div class="col-auto">
                                                    <button type="submit" class="btn btn-primary mb-2">@lang('app.search')</button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>


                            @if($users->count() > 0)
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <td>@lang('app.name')</td>
                                        <td>@lang('app.email')</td>
                                        <td>@lang('app.contributed')</td>
                                        <td>Дата создания</td>
                                        <td>@lang('app.actions')</td>
                                    </tr>

                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <img src="{!! $user->avatar() !!}" class="img-thumbnail img-circle magnific_image circle-img" width="30"/>
                                                {!! $user->fullname !!}
                                            </td>
                                            <td>{!!$user->email!!}</td>
                                            <td>
                                                @php $total_contributed = $user->contributed_amount(); @endphp
                                                @if($total_contributed > 0)
                                                    {!!get_amount($total_contributed)!!}
                                                @endif
                                            </td>
                                            <td>{!!$user->created_at!!}</td>
                                            <td>
                                                <a href="{!!route('user.profile', $user->id)!!}" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i>
                                                </a>

                                                <a href="{!!route('users_edit', $user->id)!!}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i>
                                                </a>

                                                <a href="{!!route('campaign_admin_search', ['user'=>$user->id])!!}" class="btn btn-info btn-sm"><i class="fa fa-dollar"></i>
                                                </a>

                                                @if($user->active_status == 0)
                                                    <a href="{!!route('user_status', [$user->id, 'approve'])!!}" class="btn btn-default btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-ban"></i>
                                                    </a>

                                                    <a href="{!!route('user_status', [$user->id, 'block'])!!}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i>
                                                    </a>

                                                @elseif($user->active_status == '1')
                                                    <a href="{!!route('user_status', [$user->id, 'block'])!!}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i>
                                                    </a>

                                                @elseif($user->active_status == 2)
                                                    <a href="{!!route('user_status', [$user->id, 'approve'])!!}" class="btn btn-success btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-check-circle-o"></i>
                                                    </a>
                                                @endif

                                                <a href="{!!route('user_delete', $user->id)!!}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.delete')"><i class="fa fa-trash-o"></i>
                                                </a>


                                            </td>
                                        </tr>
                                    @endforeach

                                </table>

                                {!! $users->links() !!}

                            @else
                                <h3>@lang('app.there_is_no_user')</h3>
                            @endif

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
