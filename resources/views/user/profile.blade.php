@php
  $phoneCodes = [
    '+971' => 'UAE (+971)',
    '+966' => 'KSA (+966)',
    '+973' => 'Bahrain (+973)',
    '+974' => 'Qatar (+974)',
    '+968' => 'Oman (+968)',
    '+965' => 'Kuwait (+965)',
    '+20' => 'Egypt (+20)',
  ];
@endphp

<x-layouts.app title="حسابي — سوالف">
<section class="account-page">
  <div class="container">
    <x-back-button :href="route('home')" />
    <div class="account-card">
      <div class="account-card__head">
        <h1>حسابي</h1>
        <div class="account-tabs" role="tablist">
          <button type="button" class="account-tab is-active" data-tab="profile" role="tab">حساب تعريفي</button>
          <button type="button" class="account-tab" data-tab="password" role="tab">تغيير كلمة المرور</button>
        </div>
      </div>

      <div id="tab-profile">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="account-card__body" id="profileForm">
          @csrf
          @method('PUT')

          <aside class="account-sidebar">
            <div class="account-avatar">
              @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" id="avatarPreview">
                <div class="account-avatar__placeholder" id="avatarPlaceholder" hidden>
                  <svg viewBox="0 0 24 24" width="64" height="64" fill="currentColor" aria-hidden="true">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5Z"/>
                  </svg>
                </div>
              @else
                <div class="account-avatar__placeholder" id="avatarPlaceholder">
                  <svg viewBox="0 0 24 24" width="64" height="64" fill="currentColor" aria-hidden="true">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5Z"/>
                  </svg>
                </div>
                <img src="" alt="" id="avatarPreview" hidden>
              @endif
              <label class="account-avatar__cam" title="تغيير الصورة">
                <input type="file" name="avatar" accept="image/*" id="avatarInput" hidden>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 8h3l2-3h6l2 3h3v11H4V8Z"/><circle cx="12" cy="13" r="3.5"/>
                </svg>
              </label>
            </div>

            <div class="account-sidebar__info">
              <b>{{ $user->name }}</b>
              <span>{{ $user->email }}</span>
            </div>

            <button type="button" class="account-logout" onclick="document.getElementById('logoutForm').submit()">
              تسجيل خروج
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"/><path d="M14 12H4"/><path d="m16 8 4 4-4 4"/>
              </svg>
            </button>
          </aside>

          <div class="account-form">
            <div class="account-form__row">
              <label class="account-field">
                <input type="text" name="first_name" value="{{ old('first_name', $user->firstName()) }}" placeholder="الاسم الأول" required>
              </label>
              <label class="account-field">
                <input type="text" name="last_name" value="{{ old('last_name', $user->lastName()) }}" placeholder="اسم العائلة" required>
              </label>
            </div>

            <div class="account-form__row account-form__row--phone">
              <label class="account-field account-field--code">
                <select name="phone_code">
                  @foreach($phoneCodes as $code => $label)
                    <option value="{{ $code }}" @selected(old('phone_code', $user->phone_code ?: '+971') === $code)>{{ $label }}</option>
                  @endforeach
                </select>
              </label>
              <label class="account-field">
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="رقم الهاتف" dir="ltr">
              </label>
            </div>

            <label class="account-field">
              <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="البريد الإلكتروني" required dir="ltr">
            </label>

            <label class="account-field account-field--date">
              <input type="date" name="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
            </label>

            @if($errors->any() && ! $errors->has('current_password') && ! $errors->has('password'))
              <p class="account-error">{{ $errors->first() }}</p>
            @endif

            <button type="submit" class="account-save">حفظ التغييرات</button>
          </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
          @csrf
        </form>
      </div>

      <div class="account-card__body account-card__body--password" id="tab-password" hidden>
        <form method="POST" action="{{ route('profile.password') }}" class="account-password-form">
          @csrf
          @method('PUT')
          <label class="account-field">
            <span>كلمة المرور الحالية</span>
            <input type="password" name="current_password" required>
          </label>
          <label class="account-field">
            <span>كلمة المرور الجديدة</span>
            <input type="password" name="password" required>
          </label>
          <label class="account-field">
            <span>تأكيد كلمة المرور</span>
            <input type="password" name="password_confirmation" required>
          </label>
          @if($errors->has('current_password') || $errors->has('password'))
            <p class="account-error">{{ $errors->first() }}</p>
          @endif
          <button type="submit" class="account-save">تحديث كلمة المرور</button>
        </form>
      </div>
    </div>
  </div>
</section>
</x-layouts.app>
