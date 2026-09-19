@if ($avatarUrl)
  <img class="fi-user-entry-avatar fi-size-{{ $size }}" src="{{ $avatarUrl }}" alt="{{ $alt ?? 'User Avatar' }}">
@else
  <div class="fi-user-entry-default-avatar fi-size-{{ $size }}" aria-label="{{ $alt ?? 'User' }}">
    <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::User" class="fi-size-{{ $size }}"/>
  </div>
@endif
