<?php

beforeEach(function () {
    view()->replaceNamespace('filament-user-field', dirname(__DIR__, 2) . '/resources/views');
});

it('renders visible users and a remaining count for stacked state', function () {
    $first = (object) ['name' => 'First User', 'avatar_url' => 'first.png'];
    $second = (object) ['name' => 'Second User', 'avatar_url' => 'second.png'];
    $third = (object) ['name' => 'Third User', 'avatar_url' => 'third.png'];

    $html = view('filament-user-field::user-column', [
        'getState' => fn () => collect([$first, $second, $third]),
        'getSize' => fn () => 'sm',
        'getStackedModalAction' => fn () => null,
        'getAction' => fn () => null,
        'getLabel' => fn () => 'Users',
        'isStackedState' => fn () => true,
        'getVisibleStackedUsers' => fn () => collect([$first, $second]),
        'getStackedRemainingCount' => fn () => 1,
        'getAvatarUrlFor' => fn ($user) => $user->avatar_url,
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => null,
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    expect($html)->toContain('first.png', 'second.png', '+1')
        ->not->toContain('third.png');
});

it('renders the empty state for an empty stacked collection', function () {
    $html = view('filament-user-field::user-column', [
        'getState' => fn () => collect(),
        'getSize' => fn () => 'sm',
        'getStackedModalAction' => fn () => null,
        'getAction' => fn () => null,
        'getLabel' => fn () => 'Users',
        'isStackedState' => fn () => true,
        'getVisibleStackedUsers' => fn () => collect(),
        'getStackedRemainingCount' => fn () => 0,
        'getAvatarUrlFor' => fn ($user) => $user->avatar_url,
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => 'No users selected',
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    expect($html)->toContain('No users selected');
});

it('keeps scalar user card rendering unchanged', function () {
    $user = (object) ['name' => 'Scalar User', 'email' => 'scalar@example.com'];

    $html = view('filament-user-field::user-column', [
        'getState' => fn () => $user,
        'getSize' => fn () => 'sm',
        'getStackedModalAction' => fn () => null,
        'getAction' => fn () => null,
        'getLabel' => fn () => 'User',
        'isStackedState' => fn () => false,
        'getShowAvatar' => fn () => true,
        'getAvatarUrl' => fn () => 'scalar.png',
        'getShowActiveState' => fn () => false,
        'getIsActiveState' => fn () => false,
        'getHeading' => fn () => $user->name,
        'getDescription' => fn () => $user->email,
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => null,
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    expect($html)->toContain('scalar.png', 'Scalar User', 'scalar@example.com')
        ->not->toContain('fi-user-stack');
});
