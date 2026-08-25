@props([
    'user',
    'size' => 'md',
    'class' => '',
])

@php
    try {
        $user->loadMissing('character');
    } catch (\Throwable) {
        // characters table/model may be missing on a partial deploy
    }
    $url = null;
    $emoji = null;
    $initial = mb_substr((string) ($user->name ?? '؟'), 0, 1);
    $gradient = 'linear-gradient(135deg,#1E3A5F,#0F2440)';
    try {
        $url = $user->displayAvatarUrl();
        $emoji = $user->displayAvatarEmoji();
        $initial = $user->displayAvatarInitial();
        $gradient = $user->displayAvatarGradient();
    } catch (\Throwable) {
        try {
            $url = $user->avatarUrl();
        } catch (\Throwable) {
            $url = null;
        }
    }
    $sizeClass = match ($size) {
        'sm' => 'user-avatar--sm',
        'lg' => 'user-avatar--lg',
        default => '',
    };
@endphp

@if($url)
  <img
    src="{{ $url }}"
    alt="{{ $user->name }}"
    class="user-avatar {{ $sizeClass }} {{ $class }}"
    loading="lazy"
    decoding="async"
    {{ $attributes }}
  >
@else
  <span
    class="user-avatar user-avatar--fallback {{ $sizeClass }} {{ $class }}"
    style="background:{{ $gradient }}"
    aria-hidden="true"
    {{ $attributes }}
  >{{ $emoji ?: $initial }}</span>
@endif
