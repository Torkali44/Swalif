<x-layouts.admin>
  <x-slot:heading>{{ $subscription->exists ? 'إدارة الاشتراك' : 'منح اشتراك جديد' }}</x-slot:heading>
  <x-slot:subheading>اختيار المستخدم والباقة وتحديد حالة الاشتراك ومدة الصلاحية</x-slot:subheading>

  <x-back-button :href="route('admin.subscribers.index')" label="رجوع للمشتركين" />

  @php
    $startsAt = old('starts_at', optional($subscription->starts_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $endsAt = old('ends_at', optional($subscription->ends_at)->format('Y-m-d\TH:i') ?? now()->addDays(30)->format('Y-m-d\TH:i'));
  @endphp

  <form class="admin-form" method="POST" action="{{ $subscription->exists ? route('admin.subscribers.update', $subscription) : route('admin.subscribers.store') }}">
    @csrf
    @if($subscription->exists)
      @method('PUT')
    @endif

    <label>
      المستخدم
      <select name="user_id" required>
        <option value="">اختر المستخدم</option>
        @foreach($users as $user)
          <option value="{{ $user->id }}" @selected((string) old('user_id', $subscription->user_id) === (string) $user->id)>
            {{ $user->name }} - {{ $user->email }}
          </option>
        @endforeach
      </select>
    </label>

    <label>
      الباقة
      <select name="plan_id" required>
        <option value="">اختر الباقة</option>
        @foreach($plans as $plan)
          <option value="{{ $plan->id }}" @selected((string) old('plan_id', $subscription->plan_id) === (string) $plan->id)>
            {{ $plan->name }} - {{ number_format($plan->price) }} {{ $plan->currency === 'AED' ? 'درهم' : $plan->currency }}
            @unless($plan->is_active) (موقوفة) @endunless
          </option>
        @endforeach
      </select>
    </label>

    <label>
      يبدأ في
      <input type="datetime-local" name="starts_at" value="{{ $startsAt }}" required>
    </label>

    <label>
      ينتهي في
      <input type="datetime-local" name="ends_at" value="{{ $endsAt }}" required>
    </label>

    <label class="wide">
      الحالة
      <select name="status" required>
        <option value="active" @selected(old('status', $subscription->status) === 'active')>نشط</option>
        <option value="cancelled" @selected(old('status', $subscription->status) === 'cancelled')>ملغي</option>
        <option value="expired" @selected(old('status', $subscription->status) === 'expired')>منتهي</option>
      </select>
    </label>

    @if($errors->any())
      <p class="error wide">{{ $errors->first() }}</p>
    @endif

    <button class="btn btn-primary" type="submit">حفظ الاشتراك</button>
  </form>
</x-layouts.admin>
