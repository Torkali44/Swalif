@if ($paginator->hasPages())
  <nav class="swalif-pagination" role="navigation" aria-label="التنقل بين الصفحات">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <span class="swalif-page is-disabled" aria-disabled="true">‹ السابق</span>
    @else
      <a class="swalif-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ السابق</a>
    @endif

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <a class="swalif-page" href="{{ $paginator->nextPageUrl() }}" rel="next">التالي ›</a>
    @else
      <span class="swalif-page is-disabled" aria-disabled="true">التالي ›</span>
    @endif
  </nav>
@endif
