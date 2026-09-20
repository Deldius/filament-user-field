@isset($headings)
  <ul class="fi-user-stack-tooltip-list">
    @foreach ($headings as $heading)
      <li>{{ $heading }}</li>
    @endforeach
  </ul>
@else
  <div class="fi-user-stack-tooltip-heading">{{ $heading }}</div>
  @if (filled($description))
    <div class="fi-user-stack-tooltip-description">{{ $description }}</div>
  @endif
@endisset
