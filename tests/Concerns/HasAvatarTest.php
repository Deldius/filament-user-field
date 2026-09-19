<?php

use Deldius\UserField\Concerns\HasAvatar;
use Filament\Models\Contracts\HasAvatar as ContractsHasAvatar;
use Illuminate\Support\Facades\Config;

class DummyUserFieldWithAvatar
{
    use HasAvatar;

    protected $state;

    public function __construct($state = null)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    // Simulate Filament's evaluate method
    protected function evaluate($value, array $namedInjections = [])
    {
        if (! $value instanceof Closure) {
            return $value;
        }

        $parameter = (new ReflectionFunction($value))->getParameters()[0] ?? null;

        return $parameter ? $value($namedInjections[$parameter->getName()] ?? null) : $value();
    }

    // Override getImageUrl for test
    public function getImageUrl(?string $state = null): ?string
    {
        return $state ? "url_for_{$state}" : null;
    }
}

it('can set and get showAvatar', function () {
    $field = new DummyUserFieldWithAvatar;
    $field->showAvatar(false);
    expect($field->getShowAvatar())->toBeFalse();

    $field->showAvatar(true);
    expect($field->getShowAvatar())->toBeTrue();
});

it('can set and get avatarUrl directly', function () {
    $field = new DummyUserFieldWithAvatar;
    $field->avatarUrl('custom_avatar');
    expect($field->getAvatarUrl())->toBe('custom_avatar');

    $field->avatarUrl(fn () => 'closure_avatar');
    expect($field->getAvatarUrl())->toBe('closure_avatar');
});

it('resolves an avatar callback for a specific stacked user', function () {
    $first = (object) ['avatar' => 'first.png'];
    $second = (object) ['avatar' => 'second.png'];
    $field = new DummyUserFieldWithAvatar(collect([$first, $second]));

    $field->avatarUrl(fn ($user) => $user->avatar);

    expect($field->getAvatarUrlFor($second))->toContain('second.png');
});

it('returns null from the single-user avatar accessor for collection state', function () {
    $field = new DummyUserFieldWithAvatar(collect([(object) ['avatar' => 'first.png']]));
    $field->avatarUrl(fn ($state) => $state->avatar);

    expect($field->getAvatarUrl())->toBeNull();
});

it('injects the stacked user into avatar callbacks by parameter name', function (string $parameter) {
    $user = (object) ['avatar' => 'named.png'];
    $field = new DummyUserFieldWithAvatar(collect([$user]));

    $field->avatarUrl(match ($parameter) {
        'state' => fn ($state) => $state->avatar,
        'user' => fn ($user) => $user->avatar,
    });

    expect($field->getAvatarUrlFor($user))->toBe('url_for_named.png');
})->with(['state', 'user']);

it('falls back to ContractsHasAvatar', function () {
    $user = new class implements ContractsHasAvatar
    {
        public function getFilamentAvatarUrl(): ?string
        {
            return 'contract_avatar';
        }
    };
    $field = new DummyUserFieldWithAvatar($user);
    $field->avatarUrl(null);
    expect($field->getAvatarUrl())->toBe('url_for_contract_avatar');
});

it('falls back to user model avatar field from config', function () {
    Config::set('user-field.user_model_avatar', 'avatar_url');
    $user = (object) ['avatar_url' => 'model_avatar'];
    $field = new DummyUserFieldWithAvatar($user);
    $field->avatarUrl(null);
    expect($field->getAvatarUrl())->toBe('url_for_model_avatar');
});

it('returns null if no avatar is set', function () {
    $user = (object) [];
    $field = new DummyUserFieldWithAvatar($user);
    $field->avatarUrl(null);
    expect($field->getAvatarUrl())->toBeNull();
});
