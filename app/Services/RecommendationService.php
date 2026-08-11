<?php

namespace App\Services;

use App\Models\Clickhouse\Action;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Получить рекомендации для пользователя
     *
     * @param int $userId ID пользователя
     * @param int $limit Количество рекомендаций
     * @param int $offset Смещение для пагинации
     * @return array
     */
    public function getRecommendationsForUser($userId, $limit = 10, $offset = 0)
    {
        // 1. Получаем предпочтения пользователя (его действия и теги)
        $userActions = collect(Action::getUserActions($userId, 100));

        // 2. Извлекаем все теги, которые интересуют пользователя
        $userTags = $this->extractUserTags($userActions);

        // 3. Находим контент, связанный с этими тегами (увеличиваем лимит для фильтрации)
        $recommendedStories = collect(Action::getStoriesByTags($userTags, $userId, ($limit + $offset) * 2));

        // 4. Фильтруем контент, который пользователь уже видел
        $viewedStoryIds = $userActions->where('type', 'view')->pluck('model_id')->unique()->toArray();
        $recommendedStories = $recommendedStories->filter(function ($story) use ($viewedStoryIds) {
            return !in_array($story['story_id'], $viewedStoryIds);
        });

        // 5. Если недостаточно рекомендаций, добавляем популярные истории
        if ($recommendedStories->count() < ($limit + $offset)) {
            $popularStories = collect(Action::getPopularStories(($limit + $offset) * 2));

            $popularStories = $popularStories->filter(function ($story) use ($viewedStoryIds, $recommendedStories) {
                return !in_array($story['story_id'], $viewedStoryIds) &&
                    !$recommendedStories->pluck('story_id')->contains($story['story_id']);
            });

            $recommendedStories = $recommendedStories->merge($popularStories);
        }

        // 6. Применяем пагинацию и возвращаем результат
        return $recommendedStories->slice($offset, $limit)->values()->toArray();
    }

    /**
     * Извлечь теги из действий пользователя с учетом веса действий
     *
     * @param Collection $userActions
     * @return array
     */
    private function extractUserTags(Collection $userActions)
    {
        $tags = [];
        // Определяем веса для разных типов действий
        $weights = [
            'like' => 3,    // Лайк имеет большой вес
            'comment' => 2, // Комментарий имеет средний вес
            'view' => 1,    // Просмотр имеет наименьший вес
        ];

        foreach ($userActions as $action) {
            $weight = $weights[$action['type']] ?? 1;
            // Если теги представлены как массив
            if (isset($action['tags']) && is_array($action['tags'])) {
                foreach ($action['tags'] as $tag) {
                    if (!isset($tags[$tag])) {
                        $tags[$tag] = 0;
                    }
                    $tags[$tag] += $weight;
                }
            }
        }

        // Сортируем теги по весу (от большего к меньшему)
        arsort($tags);

        // Возвращаем только ключи (теги), а не их веса
        return array_keys($tags);
    }
}