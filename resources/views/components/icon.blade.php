@props(['name','class' => 'h-icon', 'w' => 16, 'h' => 16])

<svg class="{{ $class }}" width="{{ $w }}" height="{{ $h }}" viewBox="0 0 24 24" aria-hidden="true">
  <use xlink:href="{{ asset('/icons/icons.svg') }}#{{ $name }}"></use>
</svg>
