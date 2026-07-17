@props(['href' => null, 'label' => 'رجوع'])
@php
  $backHref = $href ?: url()->previous();
  if (! $backHref || $backHref === url()->current()) {
      $backHref = route('home');
  }
@endphp
<a href="{{ $backHref }}" class="page-back">
  <span aria-hidden="true">→</span>
  <span>{{ $label }}</span>
</a>
