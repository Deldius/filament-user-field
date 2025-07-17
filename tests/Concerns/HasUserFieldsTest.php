<?php

use Deldius\UserField\Concerns\HasUserFields;
use Illuminate\Contracts\Support\Htmlable;

class DummyUserFieldWithUserFields
{
    use HasUserFields;

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

class DummyHtmlable implements Htmlable
{
    public function __construct(private string $html) {}

    public function toHtml(): string
    {
        return $this->html;
    }

    public function __toString()
    {
        return $this->html;
    }
}

it('can set and get heading directly', function () {
    $field = new DummyUserFieldWithUserFields;
    $field->heading('Custom Heading');
    expect($field->getHeading())->toBe('Custom Heading');

    $field->heading(fn () => 'Closure Heading');
    expect($field->getHeading())->toBe('Closure Heading');

    $htmlable = new DummyHtmlable('Htmlable Heading');
    $field->heading($htmlable);
    expect($field->getHeading())->toBe('Htmlable Heading');
});

it('can set and get description directly', function () {
    $field = new DummyUserFieldWithUserFields;
    $field->description('Custom Description');
    expect($field->getDescription())->toBe('Custom Description');

    $field->description(fn () => 'Closure Description');
    expect($field->getDescription())->toBe('Closure Description');

    $htmlable = new DummyHtmlable('Htmlable Description');
    $field->description($htmlable);
    expect($field->getDescription())->toBe('Htmlable Description');
});

it('falls back to user model heading field from config', function () {
    config(['user-field.user_model_heading' => 'name']);
    $user = (object) ['name' => 'User Name'];
    $field = new DummyUserFieldWithUserFields($user);
    $field->heading(null);
    expect($field->getHeading())->toBe('User Name');
});

it('falls back to user model description field from config', function () {
    config(['user-field.user_model_description' => 'email']);
    $user = (object) ['email' => 'user@email.com'];
    $field = new DummyUserFieldWithUserFields($user);
    $field->description(null);
    expect($field->getDescription())->toBe('user@email.com');
});

it('returns empty string if user model field is missing', function () {
    $user = (object) [];
    $field = new DummyUserFieldWithUserFields($user);
    $field->heading(null);
    expect($field->getHeading())->toBe('');

    $field->description(null);
    expect($field->getDescription())->toBe('');
});
