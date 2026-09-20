@php
$state = $getState();
$size = $getSize();
$stackedModalAction = $getStackedModalAction();
$isStackedModalAction = $stackedModalAction && ($getAction() === $stackedModalAction);
@endphp

<div class="fi-user-entry fi-size-{{ $size }}">
  @if ($isStackedState() && $state->isNotEmpty())
    @php
      $stackedModalEnabled = $hasStackedModal();
    @endphp
    <div class="fi-user-stack fi-size-{{ $size }}" aria-label="Users">
      @if ($isStackedModalAction)
        <span class="fi-sr-only">Open user list for {{ $getLabel() ?: 'Users' }}</span>
      @endif

      @foreach ($getVisibleStackedUsers() as $user)
        @php
          $userTooltip = $stackedModalEnabled ? null : view('filament-user-field::components.user-stack-tooltip', [
              'heading' => $getHeadingFor($user),
              'description' => $getDescriptionFor($user),
          ])->render();
        @endphp

        <div
          class="fi-user-stack-item"
          @if ($userTooltip)
            x-tooltip.html="{ content: @js($userTooltip), theme: $store.theme }"
          @endif
        >
          @include('filament-user-field::components.user-avatar', [
              'avatarUrl' => $getAvatarUrlFor($user),
              'size' => $size,
              'alt' => $user->{config('user-field.user_model.fields.heading', 'name')} ?? 'User',
          ])
        </div>
      @endforeach

      @if ($remaining = $getStackedRemainingCount())
        @php
          $remainingTooltip = $stackedModalEnabled ? null : view('filament-user-field::components.user-stack-tooltip', [
              'users' => $getHiddenStackedUsers()->map(fn ($user) => [
                  'heading' => $getHeadingFor($user),
                  'description' => $getDescriptionFor($user),
              ]),
          ])->render();
        @endphp

        <span
          class="fi-user-stack-remaining fi-size-{{ $size }}"
          @if ($remainingTooltip)
            tabindex="0"
            x-tooltip.html="{ content: @js($remainingTooltip), theme: $store.theme, interactive: true, appendTo: () =&gt; document.body }"
          @endif
        >+{{ $remaining }}</span>
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
