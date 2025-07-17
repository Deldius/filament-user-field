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
    protected function evaluate($value)
    {
        return $value instanceof Closure ? $value() : $value;
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
