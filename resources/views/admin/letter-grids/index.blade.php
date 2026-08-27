<x-layouts.admin>
  <x-slot:heading>شبكة الحروف</x-slot:heading>
  <x-slot:subheading>إدارة شبكات الحروف السداسية التنافسية وأسئلة كل حرف</x-slot:subheading>

  {{-- Stats Row --}}
  <div class="admin-stats-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
    <div class="admin-stat-card" style="background: var(--surface, #fff); border: 1.5px solid var(--border, #E8E4DC); border-radius: 16px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);">
      <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 109, 0, 0.12); color: #FF6D00; font-size: 1.5rem; display: grid; place-items: center; flex-shrink: 0;">⬡</div>
      <div>
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--muted, #6C7799);">إجمالي الشبكات</div>
        <div class="admin-stat-val" style="font-family: var(--font-display, 'Cairo'); font-size: 1.4rem; font-weight: 900; color: var(--ink, #0B1220);">{{ $grids->count() }}</div>
      </div>
    </div>

    <div class="admin-stat-card" style="background: var(--surface, #fff); border: 1.5px solid var(--border, #E8E4DC); border-radius: 16px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);">
      <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(0, 200, 83, 0.12); color: #00C853; font-size: 1.4rem; display: grid; place-items: center; flex-shrink: 0;">✓</div>
      <div>
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--muted, #6C7799);">الشبكات المفعّلة</div>
        <div class="admin-stat-val" style="font-family: var(--font-display, 'Cairo'); font-size: 1.4rem; font-weight: 900; color: var(--ink, #0B1220);">{{ $grids->where('is_active', true)->count() }}</div>
      </div>
    </div>

    <div class="admin-stat-card" style="background: var(--surface, #fff); border: 1.5px solid var(--border, #E8E4DC); border-radius: 16px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);">
      <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(124, 58, 237, 0.12); color: #7C3AED; font-size: 1.4rem; display: grid; place-items: center; flex-shrink: 0;">🔤</div>
      <div>
        <div style="font-size: 0.8rem; font-weight: 700; color: var(--muted, #6C7799);">إجمالي الحروف والأسئلة</div>
        <div class="admin-stat-val" style="font-family: var(--font-display, 'Cairo'); font-size: 1.4rem; font-weight: 900; color: var(--ink, #0B1220);">{{ $grids->sum('cells_count') }}</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="toolbar" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
    <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 240px; max-width: 420px;">
      <input type="search" id="adminGridSearch" class="search-inp" placeholder="بحث باسم الشبكة…" style="width: 100%;">
    </div>

    <div style="display: flex; gap: 10px;">
      <a class="btn btn-primary" href="{{ route('admin.letter-grids.create') }}" style="display: inline-flex; align-items: center; gap: 6px;">
        <span>+</span>
        <span>شبكة حروف جديدة</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-info" style="margin-bottom: 18px; border-radius: 12px; padding: 12px 18px;">
      {{ session('success') }}
    </div>
  @endif

  {{-- Table Container with Horizontal Scroll on Mobile --}}
  <div class="table-wrap" style="background: var(--surface, #fff); border: 1.5px solid var(--border, #E8E4DC); border-radius: 20px; overflow-x: auto; -webkit-overflow-scrolling: touch; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);">
    @if($grids->isEmpty())
      <div class="empty-panel" style="padding: 48px 20px; text-align: center;">
        <div style="font-size: 3rem; color: #FF6D00; margin-bottom: 12px;">⬡</div>
        <h3 style="font-size: 1.2rem; font-weight: 900; margin: 0 0 6px;">لا توجد شبكات حروف بعد</h3>
        <p style="color: var(--muted, #6C7799); margin: 0 0 18px;">أنشئ أول شبكة حروف لتتيح للاعبين خوض التحدي السداسي.</p>
        <a class="btn btn-primary" href="{{ route('admin.letter-grids.create') }}">+ إنشاء شبكة حروف جديدة</a>
      </div>
    @else
      <table class="data-table" id="letterGridsTable" style="width: 100%; min-width: 620px; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 1.5px solid var(--border, #E8E4DC); background: rgba(11, 18, 32, 0.02); text-align: right;">
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem;">#</th>
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem;">اسم الشبكة</th>
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem;">عدد الحروف</th>
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem;">الوصف</th>
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem;">الحالة</th>
            <th style="padding: 14px 18px; font-weight: 900; font-size: 0.9rem; text-align: center;">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @foreach($grids as $index => $grid)
            <tr class="grid-table-row" data-name="{{ strtolower($grid->name_ar) }}" style="border-bottom: 1px solid var(--border, #E8E4DC); transition: background 0.15s ease;">
              <td style="padding: 14px 18px; font-weight: 800; color: var(--muted, #6C7799);">
                {{ $index + 1 }}
              </td>
              <td style="padding: 14px 18px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  @if($grid->imageUrl())
                    <img src="{{ $grid->imageUrl() }}" alt="" style="width:34px;height:34px;border-radius:10px;object-fit:cover;flex-shrink:0">
                  @else
                    <span style="width: 34px; height: 34px; border-radius: 10px; background: linear-gradient(135deg, #FFB300, #FF6D00); color: #fff; display: grid; place-items: center; font-size: 1.1rem; flex-shrink: 0;">⬡</span>
                  @endif
                  <div>
                    <strong class="grid-row-title" style="font-family: var(--font-display, 'Cairo'); font-size: 1rem; color: var(--ink, #0B1220); display: block;">{{ $grid->name_ar }}</strong>
                    <small style="color: var(--muted, #6C7799); font-size: 0.78rem;">{{ $grid->slug }}</small>
                  </div>
                </div>
              </td>
              <td style="padding: 14px 18px;">
                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 999px; background: rgba(255, 109, 0, 0.1); color: #FF6D00; font-weight: 800; font-size: 0.85rem;">
                  <b>{{ $grid->cells_count }}</b> حرف
                </span>
              </td>
              <td style="padding: 14px 18px; max-width: 260px;">
                <span style="font-size: 0.85rem; color: var(--muted, #6C7799); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ $grid->description ?: '—' }}
                </span>
              </td>
              <td style="padding: 14px 18px;">
                <span class="status-pill {{ $grid->is_active ? 'on' : 'off' }}" style="padding: 4px 12px; border-radius: 999px; font-size: 0.82rem; font-weight: 800; display: inline-block;">
                  {{ $grid->is_active ? 'مفعّلة' : 'موقوفة' }}
                </span>
              </td>
              <td style="padding: 14px 18px; text-align: center;">
                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                  <a class="btn btn-sm btn-outline" href="{{ route('admin.letter-grids.edit', $grid) }}" style="padding: 6px 14px; border-radius: 8px; font-weight: 800;">تعديل</a>
                  
                  <form method="POST" action="{{ route('admin.letter-grids.toggle', $grid) }}" class="inline" style="display: inline-block; margin: 0;">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-ghost" type="submit" style="padding: 6px 12px; border-radius: 8px; font-weight: 800;">
                      {{ $grid->is_active ? 'إيقاف' : 'تفعيل' }}
                    </button>
                  </form>

                  <form method="POST" action="{{ route('admin.letter-grids.destroy', $grid) }}" class="inline" style="display: inline-block; margin: 0;" data-confirm-delete="هل أنت متأكد من حذف شبكة «{{ $grid->name_ar }}»؟ سيتم حذف جميع أسئلتها.">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" type="submit" style="padding: 6px 12px; border-radius: 8px; font-weight: 800;">حذف</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <style>
  body.dark.admin-body .admin-stat-card,
  body.dark.admin-body .table-wrap {
    background: #151B32 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
  }
  body.dark.admin-body .admin-stat-val,
  body.dark.admin-body .grid-row-title,
  body.dark.admin-body .data-table th,
  body.dark.admin-body .data-table td {
    color: #F8FAFC !important;
  }
  body.dark.admin-body .data-table thead tr {
    background: rgba(255, 255, 255, 0.03) !important;
    border-bottom-color: rgba(255, 255, 255, 0.08) !important;
  }
  body.dark.admin-body .data-table tbody tr {
    border-bottom-color: rgba(255, 255, 255, 0.06) !important;
  }
  body.dark.admin-body .data-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.02) !important;
  }
  body.dark.admin-body .search-inp {
    background: #0B1020 !important;
    color: #F8FAFC !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
  }
  body.dark.admin-body .empty-panel h3 {
    color: #F8FAFC !important;
  }
  @media (max-width: 720px) {
    .admin-stats-row { grid-template-columns: 1fr !important; }
    .toolbar { flex-direction: column; align-items: stretch !important; }
    .toolbar > div { max-width: none !important; width: 100%; }
  }
  </style>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const searchInp = document.getElementById('adminGridSearch');
    const rows = document.querySelectorAll('.grid-table-row');

    searchInp?.addEventListener('input', (e) => {
      const q = e.target.value.trim().toLowerCase();
      rows.forEach((row) => {
        const name = row.dataset.name || '';
        row.hidden = q && !name.includes(q);
      });
    });

    document.querySelectorAll('form[data-confirm-delete]').forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = form.dataset.confirmDelete || 'هل أنت متأكد؟';
        if (typeof window.showConfirm === 'function') {
          const ok = await window.showConfirm(msg);
          if (ok) form.submit();
        } else if (window.confirm(msg)) {
          form.submit();
        }
      });
    });
  });
  </script>
</x-layouts.admin>
