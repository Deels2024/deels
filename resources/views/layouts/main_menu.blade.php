@if(config('app.is_demo'))
    <div class="demoLinkWrap" style="background: #333333; padding: 10px 0">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <center>
                        <a href="https://codecanyon.net/item/helpus-ultimate-crowdfunding-solution/22047466/?ref=themeqx"
                           class="btn btn-success" target="_blank">
                            <i class="fa fa-eye"></i> Back to CodeCanyon Page </a>
                        <a href="https://codecanyon.net/item/helpus-ultimate-crowdfunding-solution/22047466/?ref=themeqx"
                           class="btn btn-primary" target="_blank"><i class="fa fa-shopping-cart"></i>
                            Buy Now </a>
                        <a href="https://www.themeqx.com/product/helpus-ultimate-crowdfunding-solution/"
                           class="btn btn-info" target="_blank"><i class="fa fa-eye"></i>
                            Back to HelpUs</a>
                        <a href="https://www.themeqx.com/docs/helpus-ultimate-crowdfunding-solution/"
                           class="btn btn-info" target="_blank">
                            <i class="fa fa-sticky-note-o"></i> Docs </a>
                    </center>
                </div>
            </div>
        </div>
    </div>
@endif

@if( ! request()->cookie('accept_cookie'))
    <div class="alert alert-info text-center cookie-notice">
        <div class="container ">
            <div class="row">
                <div class="col-md-12">
                    <p>{!! get_option('cookie_message') !!}</p>
                    <a href="#" class="cookie-ok-btn btn btn-danger">Ok</a>
                    <a href="{!! get_post_url(get_option('cookie_learn_page')) !!}">Learn More</a>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <nav class="navbar navbar-expand-lg navbar-light main-nav">
                <a class="navbar-brand" href="{{route('home')}}">
                    @if(get_option('logo_settings') == 'show_site_name')
                        {{ get_option('site_name') }}
                    @else
                        @if(logo_url())
                            <img class="main-logo" src="{{ logo_url() }}"/>
                        @else
                            {{ get_option('site_name') }}
                        @endif
                    @endif
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#MainTopNav"
                        aria-controls="MainTopNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="MainTopNav">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="{{route('home')}}"><i class="fa fa-home"></i> @lang('app.home')
                            </a>
                        </li>

                        @php
                            $header_menu_pages = \App\Models\Post::whereStatus(1)->where('show_in_header_menu', 1)->get();
                        @endphp
                        @if($header_menu_pages->count() > 0)
                            @foreach($header_menu_pages as $page)
                                <li class="nav-item">
                                    <a class="nav-link"
                                       href="{{ route('single_page', $page->slug) }}">{{ $page->title }} </a>
                                </li>
                            @endforeach
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" href="{{route('start_campaign')}}">@lang('app.start_campaign')</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{route('contact_us')}}"> @lang('app.contact_us')</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ml-auto">

                        <li class="nav-item ml-1">

                            <form action="{{route('search')}}" method="get" class="form-inline top-search-form">
                                <input class="form-control" type="search" placeholder="@lang('app.search') "
                                       aria-label="@lang('app.search')" name="q">
                                <button class="btn nav-link hu-btn-outline" type="submit">
                                    <i class="fa fa-search"></i> @lang('app.search') </button>
                            </form>

                        </li>

                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li class="nav-item ml-1">
                                <a class="nav-link btn hu-btn-outline" href="{{ route('login') }}">
                                    <i class="fa fa-sign-in"></i> @lang('app.login')
                                </a>
                            </li>
                            <li class="nav-item ml-1">
                                <a class="nav-link btn hu-btn-outline" href="{{ route('register') }}">
                                    <i class="fa fa-user-plus"></i> @lang('app.register')
                                </a>
                            </li>
                        @else

                            <li class="nav-item dropdown">
                                @php
                                    $loggedUser = Auth::user();
                                @endphp
                                @if($loggedUser->is_campaign_admin())
                                    <span style="
                                    display: block;
                                    position: absolute;
                                    top: -5px;
                                    right: 0px;
                                    background: #e41616;
                                    border-radius: 50px;
                                    width: 25px;
                                    height: 25px;
                                    color: #fff;
                                    text-align: center;
                                    ">{{\App\Models\Campaign::where('moderated', false)->count()}}</span>
                                @endif
                                <a class="nav-link dropdown-toggle" href="#" id="dropdown09" data-toggle="dropdown"
                                   aria-haspopup="true" aria-expanded="false">

                                    <span class="top-nav-user-name">

                                        <img src="{{ $loggedUser->avatar() }}" class="magnific_image circle-img" width="30"/>
                                        {{ $loggedUser->fullname }}
                                    </span>

                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdown09">
                                    <a class="dropdown-item" href="{{route('dashboard')}}">
                                        <i class="fa fa-dashboard"></i> @lang('app.dashboard') </a>
                                    <a class="dropdown-item" href="{{route('profile')}}">
                                        <i class="fa fa-user"></i> @lang('app.profile') </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fa fa-sign-out"></i> @lang('app.logout')
                                    </a>

                                    <a class="dropdown-item" href="{{route('change_password')}}"><i
                                                class="fa fa-lock"></i> @lang('app.change_password')
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                          style="display: none;">
                                        {{ csrf_field() }}
                                    </form>

                                </div>
                            </li>

                        @endif

                    </ul>
                </div>
            </nav>

        </div>
    </div>

</div>
