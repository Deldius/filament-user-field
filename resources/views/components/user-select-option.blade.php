<div class="fi-user-select-option">
  @if ($avatarUrl)
    <img class="fi-user-select-option-avatar" src="{{ $avatarUrl }}" alt="">
  @else
    <span class="fi-user-select-option-fallback" aria-hidden="true">
      <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::User" />
    </span>
  @endif

  <span class="fi-user-select-option-content">
    <span class="fi-user-select-option-heading">{{ $heading }}</span>
    @if (filled($description instanceof \Illuminate\Contracts\Support\Htmlable ? $description->toHtml() : $description))
      <span class="fi-user-select-option-description">{{ $description }}</span>
    @endif
  </span>
</div>
