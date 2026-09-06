@extends('layouts.neon.app')

@section('body-class', config('homepage.use_v2') ? 'deels-studio-enabled' : '')

@section('page-css')
    @if(config('homepage.use_v2'))
        <link rel="stylesheet" href="{{ ext_asset('/dist/css/deels-studio.css') }}">
        <link rel="stylesheet" href="{{ ext_asset('/dist/css/challenge-studio.css') }}">
    @endif
@endsection

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')

    <?php
        $participant = $challenge->participant(Auth::user()->id ?? null);
    ?>
    <main class="catalog {{ config('homepage.use_v2') ? 'studio-challenge' : '' }}">
        <div class="container">
            @if(config('homepage.use_v2'))
                <nav class="studio-breadcrumb" aria-label="Навигация по разделу"><a href="{{ route('home') }}">Главная</a><span aria-hidden="true">/</span><a href="{{ route('challenges.catalog') }}">Челленджи</a><span aria-hidden="true">/</span><span aria-current="page">Задание</span></nav>
            @endif
            <div class="account-info">
                @include('challenges.challenge_data', ['participant' => $participant, 'stories' => $stories, 'route' => route('challenges.catalog'), 'deelsStudio' => (bool) config('homepage.use_v2')])
            </div>
        </div>
    </main>
    @include('challenges.modal')
    @include('stories.modal')
@endsection

@push('after_scripts')
    <script>
        $('.challenge-media--video:not(.show_story)').on('click', function (e) {
            e.preventDefault();
            var route = $(this).attr('data-route');

            $.ajax({
                type: 'GET',
                url: route,
                success: function (data) {
                    if(data.success) {
                        showChallenge(data.data);
                    } else {
                        $('.alert-container').html('<div class="alert danger"> <span class="closebtn">&times;</span> '+data.error+'</div>')
                    }
                }
            });
        });
    </script>
@endpush
