@php
$state = $getState();
$size = $getSize();
$stackedModalAction = $getStackedModalAction();
$isStackedModalAction = $stackedModalAction && ($getAction() === $stackedModalAction);
@endphp

<div class="fi-user-entry fi-size-{{ $size }}">
  @if ($isStackedState() && $state->isNotEmpty())
    <div class="fi-user-stack fi-size-{{ $size }}" aria-label="Users">
      @if ($isStackedModalAction)
        <span class="fi-sr-only">Open user list for {{ $getLabel() ?: 'Users' }}</span>
      @endif

      @foreach ($getVisibleStackedUsers() as $user)
        <div class="fi-user-stack-item">
          @include('filament-user-field::components.user-avatar', [
              'avatarUrl' => $getAvatarUrlFor($user),
              'size' => $size,
              'alt' => $user->{config('user-field.user_model.fields.heading', 'name')} ?? 'User',
          ])
        </div>
      @endforeach

      @if ($remaining = $getStackedRemainingCount())
        <span class="fi-user-stack-remaining fi-size-{{ $size }}">+{{ $remaining }}</span>
      @endif
    </div>
  @elseif ($state && ! $isStackedState())
    @include('filament-user-field::components.user-card', [
        'state' => $state,
        'size' => $size,
    ])
  @else
    <div>
      @if ($emptyState = $getEmptyState())
        {{ $emptyState }}
      @else
        <div class="fi-user-entry-content-heading">{{ $getEmptyStateHeading() }}</div>
        <div class="fi-user-entry-content-description">{{ $getEmptyStateDescription() }}</div>
      @endif
    </div>
  @endif
</div>
