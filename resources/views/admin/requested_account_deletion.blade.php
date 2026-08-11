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
                            <div class="col-md-12">
                                <h1 class="page-header"> {{ $title }}  </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $date_time_format = get_option('date_format') .' '.get_option('time_format');
                            @endphp
                            @if($users->count())
                                <table class="table table-bordered ">
                                    <thead>
                                    <tr>
                                        <th>@lang('app.id')</th>
                                        <th>@lang('app.name')</th>
                                        <th>@lang('app.email')</th>
                                        <th>@lang('app.requested_time')</th>
                                        <th>#</th>
                                    </tr>
                                    </thead>


                                    <tbody>

                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <img src="{!! $user->avatar() !!}" class="img-thumbnail" /> <br />
                                                #{!! $user->id !!}
                                            </td>
                                            <td>{!! $user->fullname !!}</td>
                                            <td>{!! $user->email !!}</td>
                                            <td>
                                                {!! date($date_time_format , strtotime($user->request_deletion_time)) !!}
                                            </td>
                                            <td>
                                                <a href="{{route('users_view', $user->id)}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-eye"></i> </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>

                                </table>

                            @else
                                <div class="no-data-wrap text-center p-5 mt-5">
                                    <i class="fa fa-frown-o"></i>
                                    <h1>@lang('app.no_available_data')</h1>
                                </div>
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