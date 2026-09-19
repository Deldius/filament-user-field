@if ($getShowAvatar())
  <div style="position: relative">
    @include('filament-user-field::components.user-avatar', [
        'avatarUrl' => $getAvatarUrl(),
        'size' => $size,
    ])

    @if ($getShowActiveState())
      @if ($getIsActiveState())
        <div class="fi-user-entry-active-state fi-size-{{ $size }}" style="color: green">
          <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedCheckCircle"/>
        </div>
      @else
        <div class="fi-user-entry-active-state fi-size-{{ $size }}" style="color: red">
          <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedXCircle"/>
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
