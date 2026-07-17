@if ($paginator->hasPages())
  <nav class="swalif-pagination" role="navigation" aria-label="التنقل بين الصفحات">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <span class="swalif-page is-disabled" aria-disabled="true">‹</span>
    @else
      <a class="swalif-page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="السابق">‹</a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="swalif-page is-dots">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="swalif-page is-active" aria-current="page">{{ $page }}</span>
          @else
            <a class="swalif-page" href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <a class="swalif-page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="التالي">›</a>
    @else
      <span class="swalif-page is-disabled" aria-disabled="true">›</span>
    @endif
  </nav>
@endif
