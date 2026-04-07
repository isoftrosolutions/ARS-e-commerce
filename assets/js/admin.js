/* ============================================================
   ARS ADMIN — JavaScript Utilities
   ============================================================ */
'use strict';

/* ----------------------------------------------------------
   1. SIDEBAR
   ---------------------------------------------------------- */
class AdminSidebar {
  constructor() {
    this.sidebar   = document.querySelector('.admin-sidebar');
    this.toggleBtn = document.getElementById('sidebar-toggle');
    this.hamburger = document.getElementById('topbar-hamburger');
    this.overlay   = document.getElementById('sidebar-overlay');
    this.isCollapsed = localStorage.getItem('ars_sidebar') === '1';
    this.isMobile  = window.innerWidth <= 768;
    this.init();
  }

  init() {
    if (!this.sidebar) return;
    if (!this.isMobile && this.isCollapsed) this.sidebar.classList.add('collapsed');
    this.toggleBtn?.addEventListener('click', () => this.toggle());
    this.hamburger?.addEventListener('click', () => this.mobileOpen());
    this.overlay?.addEventListener('click',  () => this.mobileClose());
    window.addEventListener('resize', () => {
      this.isMobile = window.innerWidth <= 768;
      if (!this.isMobile) { this.sidebar.classList.remove('mobile-open'); this.overlay?.classList.remove('show'); document.body.style.overflow = ''; }
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.mobileClose(); });
  }

  toggle() {
    this.isCollapsed = !this.isCollapsed;
    this.sidebar.classList.toggle('collapsed', this.isCollapsed);
    localStorage.setItem('ars_sidebar', this.isCollapsed ? '1' : '0');
  }

  mobileOpen()  { this.sidebar.classList.add('mobile-open'); this.overlay?.classList.add('show'); document.body.style.overflow = 'hidden'; }
  mobileClose() { this.sidebar.classList.remove('mobile-open'); this.overlay?.classList.remove('show'); document.body.style.overflow = ''; }
}

/* ----------------------------------------------------------
   2. TOAST NOTIFICATIONS
   ---------------------------------------------------------- */
const Toast = (() => {
  let container = null;

  const icons = {
    success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
    error:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
    warning: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
  };

  function getContainer() {
    if (!container) { container = document.createElement('div'); container.className = 'toast-container'; document.body.appendChild(container); }
    return container;
  }

  function remove(el) {
    if (!el.parentNode) return;
    el.classList.add('removing');
    setTimeout(() => el.remove(), 320);
  }

  function show(message, type, title, duration) {
    type = type || 'info'; duration = duration !== undefined ? duration : 5000;
    const c = getContainer();
    const t = document.createElement('div');
    t.className = 'toast toast-' + type;
    const labels = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };
    t.innerHTML =
      '<div class="toast-icon">' + icons[type] + '</div>' +
      '<div class="toast-body">' +
        '<div class="toast-title">' + (title || labels[type]) + '</div>' +
        '<div class="toast-msg">' + message + '</div>' +
      '</div>' +
      '<button class="toast-dismiss" aria-label="Dismiss">' +
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
      '</button>';
    c.appendChild(t);
    t.querySelector('.toast-dismiss').addEventListener('click', () => remove(t));
    if (duration > 0) setTimeout(() => remove(t), duration);
  }

  return {
    show: show,
    success: function(msg, title) { show(msg, 'success', title); },
    error:   function(msg, title) { show(msg, 'error',   title); },
    warning: function(msg, title) { show(msg, 'warning', title); },
    info:    function(msg, title) { show(msg, 'info',    title); },
  };
})();

window.Toast = Toast;

/* ----------------------------------------------------------
   3. MODAL
   ---------------------------------------------------------- */
function openModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.add('open');
  document.body.style.overflow = 'hidden';
  setTimeout(function() { var f = el.querySelector('input:not([type=hidden]),select,textarea,button'); if (f) f.focus(); }, 60);
}

function closeModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('open');
  document.body.style.overflow = '';
}

window.openModal  = openModal;
window.closeModal = closeModal;

document.addEventListener('click', function(e) {
  var opener = e.target.closest('[data-open-modal]');
  if (opener) { e.preventDefault(); openModal(opener.dataset.openModal); return; }

  var closer = e.target.closest('[data-modal-close]');
  if (closer) {
    e.preventDefault();
    var bd = closer.closest('.modal-backdrop');
    if (bd) { bd.classList.remove('open'); document.body.style.overflow = ''; }
    return;
  }

  if (e.target.classList.contains('modal-backdrop')) { e.target.classList.remove('open'); document.body.style.overflow = ''; }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop.open').forEach(function(m) { m.classList.remove('open'); document.body.style.overflow = ''; });
  }
});

/* ----------------------------------------------------------
   4. TABS
   ---------------------------------------------------------- */
function initTabs(container) {
  var el = typeof container === 'string' ? document.querySelector(container) : container;
  if (!el) return;
  el.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var root = btn.closest('.tab-container') || el;
      root.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
      root.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
      btn.classList.add('active');
      var panel = root.querySelector('#' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });
}

/* ----------------------------------------------------------
   5. DROPDOWN
   ---------------------------------------------------------- */
document.addEventListener('click', function(e) {
  var trigger = e.target.closest('[data-dropdown]');
  if (trigger) {
    e.stopPropagation();
    var menu = document.getElementById(trigger.dataset.dropdown);
    if (!menu) return;
    var wasOpen = menu.classList.contains('open');
    document.querySelectorAll('.dropdown-menu.open').forEach(function(m) { m.classList.remove('open'); });
    if (!wasOpen) menu.classList.add('open');
    return;
  }
  document.querySelectorAll('.dropdown-menu.open').forEach(function(m) { m.classList.remove('open'); });
});

/* ----------------------------------------------------------
   6. DATA TABLE (search / sort / paginate client-side)
   ---------------------------------------------------------- */
function DataTable(tableId, opts) {
  this.table = document.getElementById(tableId);
  if (!this.table) return;
  this.opts  = Object.assign({ perPage: 25 }, opts || {});
  this.page  = 1;
  this.sortCol = null;
  this.sortDir = 'asc';
  this.query   = '';
  this.allRows = Array.from(this.table.querySelectorAll('tbody tr'));
  this._init();
}

DataTable.prototype._init = function() {
  var self = this;

  var searchInput = document.getElementById(this.table.id + '-search');
  if (searchInput) {
    searchInput.addEventListener('input', function(e) { self.query = e.target.value.toLowerCase(); self.page = 1; self._render(); });
  }

  document.querySelectorAll('[data-filter-table="' + this.table.id + '"]').forEach(function(sel) {
    sel.addEventListener('change', function() { self.page = 1; self._render(); });
  });

  this.table.querySelectorAll('thead th.sortable').forEach(function(th, i) {
    th.style.cursor = 'pointer';
    th.addEventListener('click', function() {
      if (self.sortCol === i) self.sortDir = self.sortDir === 'asc' ? 'desc' : 'asc';
      else { self.sortCol = i; self.sortDir = 'asc'; }
      self.table.querySelectorAll('thead th.sortable').forEach(function(t) { delete t.dataset.sort; });
      th.dataset.sort = self.sortDir;
      self._render();
    });
  });

  var checkAll = this.table.querySelector('.check-all');
  if (checkAll) {
    checkAll.addEventListener('change', function() {
      self.table.querySelectorAll('tbody .check-row').forEach(function(cb) {
        if (cb.closest('tr').style.display !== 'none') cb.checked = checkAll.checked;
      });
      self._updateBulk();
    });
    this.table.addEventListener('change', function(e) {
      if (e.target.classList.contains('check-row')) {
        var all = Array.from(self.table.querySelectorAll('tbody .check-row'));
        var chk = all.filter(function(c) { return c.checked; });
        checkAll.indeterminate = chk.length > 0 && chk.length < all.length;
        checkAll.checked = chk.length > 0 && chk.length === all.length;
        self._updateBulk();
      }
    });
  }

  this._render();
};

DataTable.prototype._updateBulk = function() {
  var bar = document.getElementById(this.table.id + '-bulk');
  if (!bar) return;
  var count = this.table.querySelectorAll('tbody .check-row:checked').length;
  var counter = bar.querySelector('.bulk-count');
  if (counter) counter.textContent = count;
  bar.classList.toggle('show', count > 0);
};

DataTable.prototype._getFilters = function() {
  var filters = [];
  document.querySelectorAll('[data-filter-table="' + this.table.id + '"]').forEach(function(sel) {
    if (sel.value) filters.push({ col: parseInt(sel.dataset.filterCol), val: sel.value.toLowerCase() });
  });
  return filters;
};

DataTable.prototype._filtered = function() {
  var self = this;
  var filters = this._getFilters();
  return this.allRows.filter(function(row) {
    var textOk   = !self.query || row.textContent.toLowerCase().indexOf(self.query) !== -1;
    var filterOk = filters.every(function(f) {
      var cell = row.cells[f.col];
      return cell && cell.textContent.toLowerCase().indexOf(f.val) !== -1;
    });
    return textOk && filterOk;
  });
};

DataTable.prototype._sorted = function(rows) {
  if (this.sortCol === null) return rows;
  var self = this;
  return rows.slice().sort(function(a, b) {
    var av = (a.cells[self.sortCol] ? a.cells[self.sortCol].textContent.trim() : '');
    var bv = (b.cells[self.sortCol] ? b.cells[self.sortCol].textContent.trim() : '');
    var isNum = !isNaN(parseFloat(av)) && !isNaN(parseFloat(bv));
    var cmp   = isNum ? parseFloat(av) - parseFloat(bv) : av.localeCompare(bv);
    return self.sortDir === 'asc' ? cmp : -cmp;
  });
};

DataTable.prototype._render = function() {
  var filtered = this._filtered();
  var sorted   = this._sorted(filtered);
  var total    = sorted.length;
  var start    = (this.page - 1) * this.opts.perPage;
  var paged    = sorted.slice(start, start + this.opts.perPage);

  this.allRows.forEach(function(r) { r.style.display = 'none'; });
  paged.forEach(function(r) { r.style.display = ''; });

  var info = document.getElementById(this.table.id + '-info');
  if (info) {
    var from = total ? start + 1 : 0;
    var to   = Math.min(start + this.opts.perPage, total);
    info.textContent = 'Showing ' + from + '\u2013' + to + ' of ' + total + ' entries';
  }

  this._pagination(Math.ceil(total / this.opts.perPage));
};

DataTable.prototype._pagination = function(totalPages) {
  var self = this;
  var pg = document.getElementById(this.table.id + '-pagination');
  if (!pg) return;
  pg.innerHTML = '';
  if (totalPages <= 1) return;

  function mkBtn(label, page, disabled, active) {
    var b = document.createElement('button');
    b.className = 'page-btn' + (active ? ' active' : '');
    b.innerHTML = label;
    b.disabled = !!disabled;
    if (!disabled && !active) b.addEventListener('click', function() { self.page = page; self._render(); });
    return b;
  }

  pg.appendChild(mkBtn('&#8249;', this.page - 1, this.page === 1, false));

  var range = this._range(this.page, totalPages);
  range.forEach(function(p) {
    if (p === '…') { var s = document.createElement('span'); s.className = 'page-btn'; s.textContent = '…'; pg.appendChild(s); }
    else pg.appendChild(mkBtn(p, p, false, p === self.page));
  });

  pg.appendChild(mkBtn('&#8250;', this.page + 1, this.page === totalPages, false));
};

DataTable.prototype._range = function(cur, total) {
  if (total <= 7) { var r = []; for (var i = 1; i <= total; i++) r.push(i); return r; }
  if (cur <= 4)       return [1,2,3,4,5,'…',total];
  if (cur >= total-3) return [1,'…',total-4,total-3,total-2,total-1,total];
  return [1,'…',cur-1,cur,cur+1,'…',total];
};

/* ----------------------------------------------------------
   7. FORM VALIDATION
   ---------------------------------------------------------- */
function FormValidator(formId) {
  this.form = document.getElementById(formId);
  if (!this.form) return;
  var self = this;
  this.form.addEventListener('submit', function(e) { self._submit(e); });
  this.form.querySelectorAll('[required],[data-validate]').forEach(function(f) {
    f.addEventListener('blur',  function() { self._validate(f); });
    f.addEventListener('input', function() { if (f.classList.contains('is-error')) self._validate(f); });
  });
}

FormValidator.prototype._validate = function(field) {
  var val = field.value.trim();
  var err = '';
  if (field.hasAttribute('required') && !val) err = 'This field is required.';
  else if (field.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) err = 'Enter a valid email.';
  else if (field.type === 'number' && val) {
    var n = parseFloat(val);
    if (isNaN(n)) err = 'Enter a valid number.';
    else if (field.min !== '' && n < +field.min) err = 'Min value is ' + field.min + '.';
    else if (field.max !== '' && n > +field.max) err = 'Max value is ' + field.max + '.';
  } else if (field.dataset.validate === 'phone' && val && !/^\d{10}$/.test(val)) err = 'Enter a 10-digit phone number.';

  var group = field.closest('.form-group');
  var errEl = group ? group.querySelector('.form-error') : null;
  if (err) {
    field.classList.add('is-error');
    if (!errEl) { errEl = document.createElement('div'); errEl.className = 'form-error'; if (group) group.appendChild(errEl); }
    errEl.textContent = err;
  } else {
    field.classList.remove('is-error');
    if (errEl) errEl.remove();
  }
  return !err;
};

FormValidator.prototype._submit = function(e) {
  var self = this;
  var ok = true;
  this.form.querySelectorAll('[required],[data-validate]').forEach(function(f) { if (!self._validate(f)) ok = false; });
  if (!ok) {
    e.preventDefault();
    Toast.error('Please fix the errors before submitting.');
    var firstErr = this.form.querySelector('.is-error');
    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
};

/* ----------------------------------------------------------
   8. IMAGE UPLOAD PREVIEW
   ---------------------------------------------------------- */
function initImageUpload(inputId, previewId, zoneId) {
  var input   = document.getElementById(inputId);
  var preview = document.getElementById(previewId);
  var zone    = document.getElementById(zoneId);
  if (!input || !preview) return;

  function handle(file) {
    if (!file) return;
    var allowed = ['image/jpeg','image/png','image/webp'];
    if (allowed.indexOf(file.type) === -1) { Toast.error('Only JPG, PNG, or WebP allowed.'); return; }
    if (file.size > 2 * 1024 * 1024) { Toast.error('File must be under 2 MB.'); return; }
    var reader = new FileReader();
    reader.onload = function(ev) { preview.src = ev.target.result; preview.classList.add('show'); };
    reader.readAsDataURL(file);
    try { var dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; } catch(e) {}
  }

  input.addEventListener('change', function(e) { handle(e.target.files[0]); });

  if (zone) {
    zone.addEventListener('dragover',  function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function()  { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) { e.preventDefault(); zone.classList.remove('dragover'); handle(e.dataTransfer.files[0]); });
  }
}

/* ----------------------------------------------------------
   9. CONFIRM DELETE
   ---------------------------------------------------------- */
function confirmDelete(message, callback) {
  var modal = document.getElementById('confirm-modal');
  if (!modal) { if (confirm(message)) callback(); return; }
  var msgEl = modal.querySelector('#confirm-message');
  if (msgEl) msgEl.textContent = message;
  openModal('confirm-modal');
  var btn = modal.querySelector('#confirm-ok');
  var newBtn = btn.cloneNode(true);
  btn.parentNode.replaceChild(newBtn, btn);
  newBtn.addEventListener('click', function() { closeModal('confirm-modal'); callback(); });
}

window.confirmDelete = confirmDelete;

/* ----------------------------------------------------------
   10. TOPBAR USER DROPDOWN
   ---------------------------------------------------------- */
function initUserDropdown() {
  var trigger = document.getElementById('user-dropdown-trigger');
  var menu    = document.getElementById('user-dropdown-menu');
  if (!trigger || !menu) return;
  trigger.addEventListener('click', function(e) { e.stopPropagation(); menu.classList.toggle('open'); });
  document.addEventListener('click', function() { menu.classList.remove('open'); });
}

/* ----------------------------------------------------------
   11. SEARCH SHORTCUT Ctrl/Cmd+K
   ---------------------------------------------------------- */
function initSearchShortcut() {
  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      var inp = document.querySelector('.topbar-search input');
      if (inp) inp.focus();
    }
  });
}

/* ----------------------------------------------------------
   12. AUTO-DISMISS FLASH MESSAGES
   ---------------------------------------------------------- */
function initFlashMessages() {
  document.querySelectorAll('.flash-alert').forEach(function(el) {
    setTimeout(function() {
      el.style.transition = 'opacity 0.4s';
      el.style.opacity = '0';
      setTimeout(function() { if (el.parentNode) el.remove(); }, 400);
    }, 4000);
  });
}

/* ----------------------------------------------------------
   INIT on DOMContentLoaded
   ---------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function() {
  new AdminSidebar();
  initUserDropdown();
  initSearchShortcut();
  initFlashMessages();
  document.querySelectorAll('.tab-container').forEach(function(c) { initTabs(c); });
  document.querySelectorAll('.data-table[id]').forEach(function(t) { new DataTable(t.id); });
  initImageUpload('product-image', 'product-image-preview', 'upload-zone');
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
