@if(count($story->tags) > 0)
    @php
        $tags = $story->tags()->pluck('title')->toArray();
    @endphp
    <div class="tags" style="padding: 5px 10px;font-size: 12px">
        Теги: {{implode(', ',$tags)}}
    </div>
@endif