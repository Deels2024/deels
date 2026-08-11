<div class="story_number">bt#{{$data['battle_id']}}</div>

<div class="story-media popup-story-content">
    @if($data['type'] == 'video')
        <video src="{{$data['path']}}" loop controls></video>
    @else
        <img src="{{$data['path']}}" alt="">
    @endif
    <script>

    </script>
</div>
