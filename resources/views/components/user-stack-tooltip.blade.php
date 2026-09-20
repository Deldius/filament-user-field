@isset($users)
  <ul class="fi-user-stack-tooltip-list">
    @foreach ($users as $user)
      <li>
        <div class="fi-user-stack-tooltip-heading">{{ $user['heading'] }}</div>
        @if (filled($user['description']))
          <div class="fi-user-stack-tooltip-description">{{ $user['description'] }}</div>
        @endif
      </li>
    @endforeach
  </ul>
@else
  <div class="fi-user-stack-tooltip-heading">{{ $heading }}</div>
  @if (filled($description))
    <div class="fi-user-stack-tooltip-description">{{ $description }}</div>
  @endif
@endisset
