@foreach($stories as $story)
    @include('stories.story_item', ['story' => $story, 'list' => true])
@endforeach
