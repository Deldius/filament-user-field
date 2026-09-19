<?php

namespace Deldius\UserField\Concerns;

use App\Models\User;
use DateInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasState
{
    const CACHE_KEY_PREFIX = 'user_field_state_';

    public function getState(): mixed
    {
        $state = parent::getState();

        if (is_array($state) || $state instanceof Collection) {
            return collect($state)
                ->map(fn (mixed $item): mixed => $this->resolveUserState($item))
                ->filter(fn (mixed $item): bool => $item !== null)
                ->values();
        }

        return $this->resolveUserState($state);
    }

    protected function resolveUserState(mixed $state): mixed
    {
        /** @disregard P1009 */
        // @phpstan-ignore class.notFound
        $userModel = config('user-field.user_model.class', User::class);
        $userModelId = config('user-field.user_model.fields.id', 'id');

        if (($state instanceof Model) || ($state instanceof $userModel)) {
            return $state;
        }

        if ((! is_scalar($state)) || (! $state)) {
            return null;
        }

        $data = Cache::remember(
            key: self::CACHE_KEY_PREFIX . $userModel . '_' . $state,
            ttl: new DateInterval('PT5S'),
            callback: fn () => $userModel::where($userModelId, $state)->first()?->toArray(),
        );

        return ($data === null) ? null : new $userModel($data);
    }
}
