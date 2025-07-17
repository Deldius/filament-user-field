<?php

use Deldius\UserField\Concerns\HasActiveState;
use Illuminate\Support\Facades\Config;

class DummyUserField
{
    use HasActiveState;

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
}

it('can set and get showActiveState directly', function () {
    $field = new DummyUserField;
    $field->showActiveState(false);
    expect($field->getShowActiveState())->toBeFalse();

    $field->showActiveState(fn () => true);
    expect($field->getShowActiveState())->toBeTrue();
});

it('falls back to config for showActiveState', function () {
    $field = new DummyUserField;
    $field->showActiveState(null);

    Config::set('user-field.active_state.show', true);
    expect($field->getShowActiveState())->toBeTrue();

    Config::set('user-field.active_state.show', false);
    expect($field->getShowActiveState())->toBeFalse();
});

it('can set and get isActiveState directly', function () {
    $field = new DummyUserField;
    $field->isActiveState(true);
    expect($field->getIsActiveState())->toBeTrue();

    $field->isActiveState(fn () => false);
    expect($field->getIsActiveState())->toBeFalse();
});

it('falls back to user model field for isActiveState', function () {
    Config::set('user-field.user_model_is_active', 'is_active');
    $user = (object) ['is_active' => true];
    $field = new DummyUserField($user);
    $field->isActiveState(null);
    expect($field->getIsActiveState())->toBeTrue();

    $user = (object) [];
    $field = new DummyUserField($user);
    expect($field->getIsActiveState())->toBeNull();
});
