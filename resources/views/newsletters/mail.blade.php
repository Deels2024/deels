<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
{!! $body !!}
<br>
<p><small style="color: #888888">Вы можете отписаться от рассылки <a href="{{ $unsubscribeUrl }}" style="color: #888888">здесь</a></small></p>
@if(isset($mailing))
<img src="{{url('/mail_track?mail_id')}}={{$mailing->id}}" alt="">
@endif
</body>
</html>


