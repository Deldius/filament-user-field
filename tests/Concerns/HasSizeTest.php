<?php

use Deldius\UserField\Concerns\HasSize;
use Filament\Support\Enums\Size;

class DummyUserFieldWithSize
{
    use HasSize;

    // Simulate Filament's evaluate method
    protected function evaluate($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

it('returns default size as Small when not set', function () {
    $field = new DummyUserFieldWithSize;
    expect($field->getSize())->toBe(Size::Small);
});

it('returns size as enum when set directly', function () {
    $field = new DummyUserFieldWithSize;
    $field->size(Size::Large);
    expect($field->getSize())->toBe(Size::Large);
});

it('returns size as enum when set as string', function () {
    $field = new DummyUserFieldWithSize;
    $field->size('sm');
    expect($field->getSize())->toBe(Size::Small);

    $field->size('md');
    expect($field->getSize())->toBe(Size::Medium);

    $field->size('lg');
    expect($field->getSize())->toBe(Size::Large);
});

it('returns string if not a valid enum', function () {
    $field = new DummyUserFieldWithSize;
    $field->size('custom-size');
    expect($field->getSize())->toBe('custom-size');
});

it('returns Medium if size is "base"', function () {
    $field = new DummyUserFieldWithSize;
    $field->size('base');
    expect($field->getSize())->toBe(Size::Medium);
});

it('evaluates closure for size', function () {
    $field = new DummyUserFieldWithSize;
    $field->size(fn () => 'lg');
    expect($field->getSize())->toBe(Size::Large);
});
