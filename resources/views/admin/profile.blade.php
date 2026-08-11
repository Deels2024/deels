@extends('layouts.admin.app')

@section('title') @if(! empty($title)) {{$title}} @endif  @parent @endsection

@section('content')

    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

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
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>@lang('app.profile_photo')</th>
                                    <td>
                                        <img src="{{ $user->avatar() }}" class="img-thumbnail img-circle" width="100" />
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('app.name')</th>
                                    <td>{{ $user->fullname }}</td>
                                </tr>

                                <tr>
                                    <th>@lang('app.email')</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.gender')</th>
                                    <td>{{ ucfirst($user->gender) }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.phone')</th>
                                    <td>{{ $user->phone }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.address')</th>
                                    <td>{{ $user->address }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.country')</th>
                                    <td>
                                        @if($user->country)
                                            {{ $user->country->name }}
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('app.created_at')</th>
                                    <td>{{ $user->signed_up_datetime() }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.status')</th>
                                    <td>{{ $user->status_context() }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('app.contributed')</th>
                                    <td>
                                        @php $total_contributed = $user->contributed_amount(); @endphp
                                        @if($total_contributed > 0)
                                            {!! get_amount($total_contributed) !!}
                                        @endif
                                    </td>
                                </tr>

                                @if( ! $user->is_admin())
                                <tr>
                                    <th>@lang('app.account_deletion')</th>
                                    <td> <a href="{{route('request_account_deletion')}}">@lang('app.delete_my_account')</a> </td>
                                </tr>
                                @endif

                            </table>

                            @if( ! empty($is_user_id_view))
                                <a href="{{route('users_edit', $user->id)}}"><i class="fa fa-pencil-square-o"></i> @lang('app.edit') </a>
                            @else
                                <a href="{{ route('profile_edit') }}"><i class="fa fa-pencil-square-o"></i> @lang('app.edit') </a>
                            @endif



                            @if(request()->user()->is_admin())

                                @if($user->request_deletion === 1)
                                    <div class="mt-3 mb-3">
                                        <form action="{{route('requested_deletion_action', $user->id)}}" method="post" > @csrf

                                        <button class="btn btn-lg btn-danger mb-2" name="action_type" value="approve_deletion_request">@lang('app.approve_deletion_request')</button>
                                        </form>

                                        <form action="{{route('requested_deletion_action', $user->id)}}" method="post" > @csrf
                                            <button class="btn btn-lg btn-success" name="action_type" value="cancel_deletion_request">@lang('app.cancel_deletion_request')</button>
                                        </form>
                                    </div>

                                @endif

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