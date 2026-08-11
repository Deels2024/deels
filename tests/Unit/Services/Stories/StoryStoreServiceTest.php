<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Challenge;
use App\Models\Battle;
use App\Models\Story;
use App\Services\Stories\StoryLegacyMediaUploader;
use App\Services\Stories\StoryStoreService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryStoreServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_store_returns_missing_content_without_file(): void
    {
        $service = new StoryStoreService($this->mockUploader([]));
        $request = Request::create('/api/stories/store', 'POST', [
            'user_id' => 10,
            'amount' => 0,
        ]);

        self::assertSame([
            'success' => false,
            'error' => 'Отсутствует контент',
        ], $service->store($request));
    }

    public function test_store_creates_story_with_uploaded_media_contract(): void
    {
        $challenge = Challenge::create([
            'user_id' => 20,
            'title' => 'Challenge',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        $oldStory = Story::create([
            'user_id' => 10,
            'challenge_id' => $challenge->id,
            'is_main_story' => false,
        ]);
        $media = (object) ['id' => 555];
        $service = new StoryStoreService($this->mockUploader([
            'success' => true,
            'images' => [$media],
        ]));
        $file = UploadedFile::fake()->create('story.mp4', 128, 'video/mp4');
        $request = Request::create('/api/stories/store', 'POST', [
            'user_id' => 10,
            'amount' => 25,
            'description' => 'Stored story',
            'data' => json_encode(['kind' => 'contract']),
            'challenge_id' => $challenge->id,
        ], [], [
            'file' => $file,
        ]);

        $result = $service->store($request);

        self::assertTrue($result['success']);
        self::assertIsInt($result['story_id']);
        self::assertNull(Story::withoutGlobalScopes()->find($oldStory->id));

        $created = Story::withoutGlobalScopes()->find($result['story_id']);
        self::assertSame(10, $created->user_id);
        self::assertSame(555, $created->media_id);
        self::assertSame('Stored story', $created->description);
        self::assertSame(['kind' => 'contract'], $created->data);
        self::assertSame(25, $created->amount);
        self::assertTrue((bool) $created->paid);
        self::assertSame($challenge->id, $created->challenge_id);
    }

    public function test_store_creates_battle_story_and_replaces_existing_story(): void
    {
        $battle = Battle::create([
            'user_id' => 20,
            'title' => 'Battle',
            'active' => true,
            'declined' => false,
            'finished' => false,
        ]);
        $oldStory = Story::create([
            'user_id' => 10,
            'battle_id' => $battle->id,
            'is_main_story' => false,
        ]);
        $service = new StoryStoreService($this->mockUploader([
            'success' => true,
            'images' => [(object) ['id' => 777]],
        ]));
        $request = Request::create('/api/stories/store', 'POST', [
            'user_id' => 10,
            'amount' => 0,
            'battle_id' => $battle->id,
            'data' => '{}',
        ], [], [
            'file' => UploadedFile::fake()->create('battle.mp4', 128, 'video/mp4'),
        ]);

        $result = $service->store($request);

        self::assertTrue($result['success']);
        self::assertNull(Story::withoutGlobalScopes()->find($oldStory->id));
        self::assertSame($battle->id, Story::withoutGlobalScopes()->find($result['story_id'])->battle_id);
    }

    public function test_store_web_returns_back_for_missing_targets_without_throwing(): void
    {
        $service = new StoryStoreService($this->mockUploader([
            'success' => true,
            'images' => [(object) ['id' => 888]],
        ]));
        $request = Request::create('/stories/store_web', 'POST', [
            'user_id' => 10,
            'challenge_id' => 999,
        ], [], [
            'mainImg' => UploadedFile::fake()->create('story.mp4', 128, 'video/mp4'),
        ]);

        self::assertSame([
            'type' => 'back',
            'message' => 'Челлендж не найден',
        ], $service->storeWeb($request));

        $request = Request::create('/stories/store_web', 'POST', [
            'user_id' => 10,
            'battle_id' => 999,
        ], [], [
            'mainImg' => UploadedFile::fake()->create('story.mp4', 128, 'video/mp4'),
        ]);

        self::assertSame([
            'type' => 'back',
            'message' => 'Батл не найден',
        ], $service->storeWeb($request));
    }

    private function mockUploader(array $response): StoryLegacyMediaUploader
    {
        $uploader = $this->createMock(StoryLegacyMediaUploader::class);
        $uploader->method('store')->willReturn($response);

        return $uploader;
    }
}
