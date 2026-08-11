@php
    $preview = $story->getStoryPreview();
    $previewClass = $class ?? '';
    $alt = $alt ?? '';
@endphp

@if($preview['type'] === 'video')
    <video src="{{$preview['url']}}" poster="{{$preview['poster']}}" muted loop autoplay playsinline class="{{$previewClass}}"></video>
@else
    <img src="{{$preview['url']}}" alt="{{$alt}}" class="{{$previewClass}}">
@endif
