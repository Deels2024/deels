@extends('layouts.new.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection

@section('content')

    <section class="page-top">
        <div class="wrapper">
            <div class="breadcrumbs">
                <ul>
                    <li>
                        <a href="/">Главная</a>
                    </li>
                    <li>{!! $title !!}</li>
                </ul>
            </div>
        </div>
    </section>







    <section class="contacts">
        <div class="wrapper contacts__wrap flex">
            <p>Корзина пуста</p>
        </div>
    </section>

@endsection
