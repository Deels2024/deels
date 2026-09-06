<?php

declare(strict_types=1);

namespace App\Http\Resources\Home;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class HomeCardResource extends JsonResource
{
    protected function author(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        $attributes = $user->getAttributes();
        $username = $attributes['username'] ?? null;
        $name = $username ?: ($attributes['name'] ?? 'Пользователь');

        return [
            'id' => (int) $user->id,
            'name' => $name,
            'username' => $username,
            'avatar' => $user->avatar_url,
        ];
    }

    protected function count(Model $model, string $attribute, string $relation): int
    {
        $attributes = $model->getAttributes();
        if (array_key_exists($attribute, $attributes)) {
            return (int) $attributes[$attribute];
        }

        if ($model->relationLoaded($relation)) {
            return $model->getRelation($relation)->count();
        }

        return 0;
    }

    protected function isoDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->toIso8601String();
    }
}
