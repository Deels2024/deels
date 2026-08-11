@extends('layouts.neon.app')

@section('title')
    @if(! empty($title))
        {{$title}}
    @endif  @parent
@endsection

@section('content')

    <?php
        $participant = $battle->participant(Auth::user()->id ?? null);
    ?>
    <div class="catalog">
        <div class="container">
            <div class="account-info">
                @include('battles.battle_data', ['participant' => $participant, 'stories' => $stories, 'route' => route('challenges.catalog')])
            </div>
        </div>
    </div>
    @include('challenges.modal')
    @include('stories.modal')
@endsection

@section('page-js')
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
