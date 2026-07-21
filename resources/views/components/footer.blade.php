<footer class="foot">
  <div class="container foot__inner">
    <div class="foot__brand">
      <a href="{{ route('home') }}" class="nav__logo">
        <img src="{{ asset('images/logo.png') }}" width="500" height="100" alt="سوالف" class="logo-img">
        <span class="logo-text">سوالف</span>
      </a>
      <p class="foot__tag">منصة ألعاب معلوماتية ثقافية تجمع بين المتعة والتعلم بهوية عصرية.</p>
      <div class="foot__social">
        <a href="#" aria-label="تويتر / إكس" title="X">𝕏</a>
        <a href="#" aria-label="إنستغرام" title="Instagram">📸</a>
        <a href="#" aria-label="فيسبوك" title="Facebook">f</a>
        <a href="#" aria-label="يوتيوب" title="YouTube">▶</a>
      </div>
    </div>

    <div class="foot__col">
      <h4>روابط سريعة</h4>
      <a href="{{ route('home') }}">الرئيسية</a>
      <a href="{{ route('categories.index') }}">الألعاب</a>
      <a href="{{ route('home') }}#leaderboard">الترتيب</a>
      <a href="{{ route('home') }}#plans">الاشتراكات</a>
    </div>

    <div class="foot__col">
      <h4>الدعم والمساعدة</h4>
      <a href="{{ route('home') }}#faq">مركز المساعدة</a>
      <a href="{{ route('home') }}#faq">الأسئلة الشائعة</a>
      <a href="mailto:info@swalif.ae">اتصل بنا</a>
      <!-- <a href="#">سياسة الخصوصية</a>
      <a href="#">شروط الاستخدام</a> -->
    </div>

    <div class="foot__col foot__download">
      <h4>الروابط القانونية</h4>
      <a href="#">سياسة الخصوصية</a>
      <a href="#">شروط الاستخدام</a>
    </div>
  </div>

  <div class="foot__bottom" style="text-align:center;">© {{ now()->year }} سوالف — جميع الحقوق محفوظة</div>
</footer>
