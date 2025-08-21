@php
use Filament\Support\Icons\Heroicon;

$state = $getState();
$size = $getSize();
@endphp

<div  class="fi-user-entry fi-size-{{ $size }}"
      {{-- x-data
      x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-user-field', package: 'deldius/filament-user-field'))]" --}}
      >
  @if ($state)
    @if ($getShowAvatar())
      <div style="position: relative">
        @if ($avatarUrl = $getAvatarUrl())
          <img class="fi-user-entry-avatar fi-size-{{ $size }}" src="{{ $avatarUrl }}" alt="User Avatar">
        @else
          <div class="fi-user-entry-default-avatar fi-size-{{ $size }}">
            <x-filament::icon :icon="Heroicon::User" class="fi-size-{{ $size }}"/>
          </div>
        @endif

        @if ($getShowActiveState())
          @if ($getIsActiveState())
            <div class="fi-user-entry-active-state fi-size-{{ $size }}" style="color: green">
              <x-filament::icon :icon="Heroicon::OutlinedCheckCircle"/>
            </div>
          @else
            <div class="fi-user-entry-active-state fi-size-{{ $size }}" style="color: red">
              <x-filament::icon :icon="Heroicon::OutlinedXCircle"/>
            </div>
          @endif
        @endif
      </div>
    @endif

    <div class="fi-user-entry-content">
      <div class="fi-user-entry-content-heading fi-size-{{ $size }}">
        <span class="">{{ $getHeading() }}</span>
      </div>
      <div class="fi-user-entry-content-description fi-size-{{ $size }}">{{ $getDescription() }}</div>
    </div>
  @else
  {{-- Empty State --}}
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