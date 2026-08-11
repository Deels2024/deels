@if($contest->type == 'video' && $contest->video_preview)
    <video src="{{$contest->video_preview}}" poster="{{$contest->thumbnail}}" muted loop autoplay playsinline></video>
@else
    <img src="{{$contest->thumbnail ?: $contest->path}}" alt="{{$contest->title}}">
@endif
