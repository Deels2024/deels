@extends('layouts.admin.app_neon')
@section('title')
    @if( ! empty($title))
        {{ $title }} |
    @endif @parent
@endsection

@section('content')

    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-title">
                <h1 class="account-main__title">{{$title}}
                </h1>
            </div>
        </div>

        <div class="comments bg-dark">

            @if($tags)
                <table class="wallet-table">
                    <thead>
                    <tr>
                        <th>Название</th>
                        <th>Сторис</th>
                        <th>Создан</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tags as $tag)
                        <tr>
                            <td>{{$tag->title}}</td>
                            <td>{{$tag->stories()->count()}}</td>
                            <td>{{\Carbon\Carbon::parse($tag->accepted_at)->format('d.m.Y H:i')}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

        </div>
        {{$tags->links()}}
    </main>
@endsection
