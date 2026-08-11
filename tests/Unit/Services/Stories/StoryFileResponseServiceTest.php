<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stories;

use App\Models\Media;
use App\Models\Story;
use App\Services\Stories\StoryFileResponseService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class StoryFileResponseServiceTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_get_file_returns_missing_story_contract(): void
    {
        $response = (new StoryFileResponseService())->getFile(404);
        $payload = $response->getData(true);

        self::assertSame([
            'success' => false,
            'error' => 'Сторис не найдена',
        ], $payload);
    }

    public function test_get_file_returns_missing_file_contract(): void
    {
        $story = $this->createImageStory('missing-file.jpg');

        $response = (new StoryFileResponseService())->getFile($story->id);
        $payload = $response->getData(true);

        self::assertSame([
            'success' => false,
            'error' => 'Файл не найден',
        ], $payload);
    }

    public function test_get_file_returns_binary_image_response_with_no_cache_headers(): void
    {
        $directory = public_path('uploads/stories');
        File::ensureDirectoryExists($directory);
        $path = $directory . '/story-file-response-test.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='));

        try {
            $story = $this->createImageStory('story-file-response-test.png');

            $response = (new StoryFileResponseService())->getFile($story->id);

            self::assertInstanceOf(
                BinaryFileResponse::class,
                $response,
                method_exists($response, 'getData') ? json_encode($response->getData(true), JSON_UNESCAPED_UNICODE) : ''
            );
            $cacheControl = $response->headers->get('Cache-Control');
            self::assertStringContainsString('no-store', $cacheControl);
            self::assertStringContainsString('no-cache', $cacheControl);
            self::assertStringContainsString('must-revalidate', $cacheControl);
            self::assertStringContainsString('max-age=0', $cacheControl);
            self::assertSame('no-cache', $response->headers->get('Pragma'));
        } finally {
            File::delete($path);
        }
    }

    private function createImageStory(string $fileName): Story
    {
        $user = $this->createCharacterizationUserWithWallets(['id' => random_int(100, 999)]);
        $slug = pathinfo($fileName, PATHINFO_FILENAME);
        $media = Media::create([
            'user_id' => $user->id,
            'mime_type' => 'image/jpeg',
            'slug' => $slug,
            'slug_ext' => $fileName,
            'folder' => 'uploads/stories',
        ]);

        return Story::create([
            'user_id' => $user->id,
            'media_id' => $media->id,
            'active' => true,
            'declined' => false,
            'broken' => false,
        ]);
    }
}
