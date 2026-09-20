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
        'getHiddenStackedUsers' => fn () => collect([$third]),
        'hasStackedModal' => fn () => false,
        'getAvatarUrlFor' => fn ($user) => $user->avatar_url,
        'getHeadingFor' => fn ($user) => $user->name,
        'getDescriptionFor' => fn ($user) => "Description for {$user->name}",
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => null,
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    expect($html)->toContain('first.png', 'second.png', '+1')
        ->not->toContain('third.png');
});

it('shows details for visible and hidden users in stack tooltips without a modal', function () {
    $first = (object) ['name' => 'First User', 'description' => 'First Description', 'avatar_url' => 'first.png'];
    $second = (object) ['name' => 'Second User', 'description' => 'Second Description', 'avatar_url' => 'second.png'];
    $third = (object) ['name' => 'Hidden User', 'description' => 'Hidden Description', 'avatar_url' => 'hidden.png'];
    $fourth = (object) ['name' => 'Hidden Without Description', 'description' => '', 'avatar_url' => 'hidden-empty.png'];

    $html = view('filament-user-field::user-column', [
        'getState' => fn () => collect([$first, $second, $third, $fourth]),
        'getSize' => fn () => 'sm',
        'getStackedModalAction' => fn () => null,
        'getAction' => fn () => null,
        'getLabel' => fn () => 'Users',
        'isStackedState' => fn () => true,
        'getVisibleStackedUsers' => fn () => collect([$first, $second]),
        'getHiddenStackedUsers' => fn () => collect([$third, $fourth]),
        'getStackedRemainingCount' => fn () => 2,
        'hasStackedModal' => fn () => false,
        'getAvatarUrlFor' => fn ($user) => $user->avatar_url,
        'getHeadingFor' => fn ($user) => $user->name,
        'getDescriptionFor' => fn ($user) => $user->description,
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => null,
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    preg_match('/<span\s+class="fi-user-stack-remaining[^>]+>/', $html, $remainingTag);

    expect($html)->toContain('x-tooltip.html', 'First User', 'First Description', 'Second User', 'Second Description')
        ->and($remainingTag[0])->toContain('Hidden User', 'Hidden Description', 'Hidden Without Description', 'interactive: true', 'appendTo: () =&gt; document.body', 'tabindex="0"')
        ->not->toContain('First User', 'Second User')
        ->and(substr_count($remainingTag[0], 'fi-user-stack-tooltip-description'))->toBe(1);
});

it('does not render stack tooltips when the modal is enabled', function () {
    $first = (object) ['name' => 'First User', 'description' => 'First Description', 'avatar_url' => 'first.png'];
    $hidden = (object) ['name' => 'Hidden User', 'description' => 'Hidden Description', 'avatar_url' => 'hidden.png'];

    $html = view('filament-user-field::user-column', [
        'getState' => fn () => collect([$first, $hidden]),
        'getSize' => fn () => 'sm',
        'getStackedModalAction' => fn () => new stdClass,
        'getAction' => fn () => new stdClass,
        'getLabel' => fn () => 'Users',
        'isStackedState' => fn () => true,
        'getVisibleStackedUsers' => fn () => collect([$first]),
        'getHiddenStackedUsers' => fn () => collect([$hidden]),
        'getStackedRemainingCount' => fn () => 1,
        'hasStackedModal' => fn () => true,
        'getAvatarUrlFor' => fn ($user) => $user->avatar_url,
        'getHeadingFor' => fn ($user) => $user->name,
        'getDescriptionFor' => fn ($user) => $user->description,
        'getEmptyState' => fn () => null,
        'getEmptyStateHeading' => fn () => null,
        'getEmptyStateDescription' => fn () => null,
    ])->render();

    expect($html)->not->toContain('x-tooltip');
});

it('escapes user content in stack tooltips', function () {
    $userTooltip = view('filament-user-field::components.user-stack-tooltip', [
        'heading' => '<script>alert("heading")</script>',
        'description' => '<img src=x onerror=alert("description")>',
    ])->render();
    $remainingTooltip = view('filament-user-field::components.user-stack-tooltip', [
        'users' => collect([[
            'heading' => '<script>alert("hidden heading")</script>',
            'description' => '<img src=x onerror=alert("hidden description")>',
        ]]),
    ])->render();

    expect($userTooltip)->toContain('&lt;script&gt;', '&lt;img src=x onerror=alert(&quot;description&quot;)&gt;')
        ->not->toContain('<script>', '<img')
        ->and($remainingTooltip)->toContain('&lt;script&gt;', '&lt;img src=x onerror=alert(&quot;hidden description&quot;)&gt;')
        ->not->toContain('<script>', '<img');
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
