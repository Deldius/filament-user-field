<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
  @include('filament-user-field::user-column')
</x-dynamic-component>