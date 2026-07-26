{{-- Critical dark-mode styles for login/register (works even if old build CSS is cached) --}}
<style>
  body.dark .auth-wrap {
    background: linear-gradient(165deg, #0B1020 0%, #151B32 55%, #1A1030 100%) !important;
  }
  body.dark .auth-card {
    background: #151B32 !important;
    color: #F5F7FF !important;
    border: 1px solid rgba(255,255,255,.12) !important;
  }
  body.dark .auth-card h1 { color: #F5F7FF !important; }
  body.dark .auth-card > p { color: #B4BCD0 !important; }
  body.dark .auth-card label,
  body.dark .auth-card .check { color: #F5F7FF !important; }
  body.dark .auth-card input,
  body.dark .auth-card select,
  body.dark .auth-card textarea {
    background: #ffffff !important;
    color: #0B1220 !important;
    border-color: #D1D5DB !important;
    caret-color: #0B1220 !important;
    -webkit-text-fill-color: #0B1220 !important;
  }
  body.dark .auth-card input::placeholder {
    color: #6B7280 !important;
    opacity: 1 !important;
    -webkit-text-fill-color: #6B7280 !important;
  }
  body.dark .auth-card a { color: #FF8A9A !important; }
  body.dark .auth-card .error { color: #FCA5A5 !important; }
</style>
