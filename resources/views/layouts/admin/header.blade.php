<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{asset('assets/images/favicon.ico')}}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> @yield('title') @show </title>

@yield('meta-data')

<!-- Bootstrap core CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font awesome 4.4.0 -->
    <link rel="stylesheet" href="{{ asset('assets/font-awesome-4.4.0/css/font-awesome.min.css') }}">
    <!-- load page specific css -->

    <!-- main select2.css -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <!-- Conditional page load script -->
    @if(request()->segment(1) === 'dashboard')
        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/metisMenu/dist/metisMenu.min.css') }}">
    @endif
<!-- main style.css -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    @php
        $deelsCampaignManage = request()->is('dashboard/my_campaigns/edit_campaign/*/rewards')
            || request()->is('dashboard/my_campaigns/edit_campaign/*/rewards/update/*')
            || request()->is('dashboard/my_campaigns/edit_campaign/*/updates')
            || request()->is('dashboard/my_campaigns/edit_campaign/*/updates/update/*')
            || request()->is('dashboard/my_campaigns/edit_campaign/*/faqs')
            || request()->is('dashboard/my_campaigns/edit_campaign/*/faqs/update/*');
    @endphp
    @if($deelsCampaignManage)
        <link rel="stylesheet" href="{{ asset('dist/css/deelsweb-campaign-manage-source.css') }}">
    @endif
    @yield('page-css')

    @if(get_option('additional_css'))
        <style type="text/css">
            {{ get_option('additional_css') }}
        </style>
@endif
<!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body @if($deelsCampaignManage) class="deels-campaign-manage" @endif>


@include('layouts.main_menu')