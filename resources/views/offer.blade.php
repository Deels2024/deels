@extends('layouts.neon.app')
@section('title')Правила и оферты@endsection
@section('meta-data')
    <meta name="description" content="Ознакомиться с правилами платформы и официальными документами">
@endsection

@section('content')
    <div class="background__dark"></div>

    <div class="offers">
        <div class="container">
            <h1>Правила и соглашения</h1>
            <p>
                <a class="offers__link" href="/docs/rules.docx" download>Правила пользования сайтом ({{toMb(filesize(public_path('/docs/rules.docx')))}}/{{pathinfo(public_path('/docs/rules.docx'))['extension']}})</a>
                <a class="offers__link" href="/docs/privacy_policy.docx" download>Политика конфиденциальности ({{toMb(filesize(public_path('/docs/privacy_policy.docx')))}}/{{pathinfo(public_path('/docs/privacy_policy.docx'))['extension']}})</a>
                <a class="offers__link" href="/docs/license.docx" download>Лицензионное соглашение ({{toMb(filesize(public_path('/docs/license.docx')))}}/{{pathinfo(public_path('/docs/license.docx'))['extension']}})</a>
            </p>


        </div>
    </div>

@endsection
