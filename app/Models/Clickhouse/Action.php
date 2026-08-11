<?php

namespace App\Models\Clickhouse;

use PhpClickHouseLaravel\BaseModel;
use Tinderbox\ClickhouseBuilder\Query\Expression;

class Action extends BaseModel
{
    protected $table = 'actions';

    /**
     * Get popular stories
     *
     * @param int $limit Maximum number of records
     * @return array
     */
    public static function getPopularStories(int $limit = 20): array
    {
        return self::select([
            'model_id as story_id',
            new Expression('count() as action_count'),
            new Expression('any(created_at) as sample_created_at')
        ])
            ->where('model', 'Story')
            ->groupBy('model_id')
            ->orderBy('action_count', 'DESC')
            ->limit($limit)
            ->getRows();
    }

    /**
     * Get user actions
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of records
     * @return array
     */
    public static function getUserActions(int $userId, int $limit = 50): array
    {
        $results = self::select()
            ->where('model', 'Story')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->getRows();

        return array_map([self::class, 'processTags'], $results);
    }

    /**
     * Check user's view
     *
     * @param int $userId User ID
     * @param int $limit Maximum number of records
     * @return array
     */
    public static function checkUserView(int $userId, int $storyId): bool
    {
        $results = self::select()
            ->where('model', 'Story')
            ->where('type', 'view')
            ->where('user_id', $userId)
            ->where('model_id', $storyId)
            ->orderBy('created_at', 'DESC')
            ->getRows();

        return count($results);
    }

    /**
     * Get stories by tags
     *
     * @param array $tags Tags to search for
     * @param int $limit Maximum number of records
     * @return array
     */
    public static function getStoriesByTags(array $tags, $userId, int $limit = 20): array
    {
        if (empty($tags)) {
            return [];
        }

        // Получаем ID просмотренных пользователем историй
        $viewedStoryIds = self::select(['model_id'])
            ->where('model', 'Story')
            ->where('type', 'view')
            ->where('user_id', $userId)
            ->getRows();

        $viewedStoryIds = array_column($viewedStoryIds, 'model_id');

        // Получаем все истории, исключая просмотренные
        $results = self::select(['model_id', 'tags'])
            ->where('model', 'Story');

        if (!empty($viewedStoryIds)) {
            $results->whereNotIn('model_id', $viewedStoryIds);
        }

        $results = $results->limit(1000)
            ->getRows();

        $grouped = [];
        foreach ($results as $row) {
            $modelId = $row['model_id'];
            $rowTags = self::extractTags($row['tags']);

            if (!isset($grouped[$modelId])) {
                $grouped[$modelId] = [
                    'story_id' => $modelId,
                    'story_tags' => $rowTags,
                    'match_count' => 0
                ];
            }

            $grouped[$modelId]['match_count'] += count(array_intersect($rowTags, $tags));
        }

        usort($grouped, fn($a, $b) => $b['match_count'] <=> $a['match_count']);

        return array_slice($grouped, 0, $limit);
    }

    /**
     * Process tags string into array
     *
     * @param array $item
     * @return array
     */
    private static function processTags(array $item): array
    {
        if (isset($item['tags']) && $item['tags'] !== '[]') {
            $item['tags'] = self::extractTags($item['tags']);
        }
        return $item;
    }

    /**
     * Extract tags from string
     *
     * @param string $tagsString
     * @return array
     */
    private static function extractTags(string $tagsString): array
    {
        if ($tagsString === '[]') {
            return [];
        }

        preg_match_all("/'([^']+)'/", $tagsString, $matches);
        return $matches[1] ?? [];
    }
}