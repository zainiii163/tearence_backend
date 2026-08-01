const AUTO_SAVE_DELAY = 700;
const el = (s, r = document) => r.querySelector(s);
const els = (s, r = document) => Array.from(r.querySelectorAll(s));
const STORAGE_KEY = document.body.dataset.storageKey || 'wwa_template_v1';

const showToast = (msg, time = 2800) => {
  const toast = el('#toast');
  if (!toast) return;
  toast.textContent = msg;
  toast.style.display = 'block';
  requestAnimationFrame(() => { toast.style.opacity = '1'; });
  clearTimeout(showToast._t);
  showToast._t = setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => { toast.style.display = 'none'; }, 280);
  }, time);
};

const debounce = (fn, wait) => {
  let t;
  return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), wait); };
};

let isDirty = false;
const markDirty = () => { isDirty = true; };
const scheduleSave = debounce(() => saveNow(false), AUTO_SAVE_DELAY);

function wireInputs(root = document) {
  els('input, textarea, select', root).forEach((node) => {
    node.addEventListener('input', () => { markDirty(); scheduleSave(); });
    node.addEventListener('change', () => { markDirty(); scheduleSave(); });
  });
}

function collect() {
  const data = { fields: {}, checks: {}, rows: {} };
  els('#tpl-form input, #tpl-form textarea, #tpl-form select').forEach((node) => {
    if (!node.name) return;
    if (node.type === 'checkbox') data.checks[node.name] = node.checked;
    else data.fields[node.name] = node.value;
  });
  els('[data-row-table]').forEach((table) => {
    const key = table.dataset.rowTable;
    data.rows[key] = [];
    els('tbody tr', table).forEach((tr) => {
      const row = {};
      els('input, textarea, select', tr).forEach((n) => {
        if (!n.name) return;
        row[n.name] = n.type === 'checkbox' ? n.checked : n.value;
      });
      data.rows[key].push(row);
    });
  });
  return data;
}

function apply(data) {
  if (!data) return;
  Object.entries(data.fields || {}).forEach(([k, v]) => {
    const node = el(`#tpl-form [name="${k}"]`);
    if (node && node.type !== 'checkbox') node.value = v ?? '';
  });
  Object.entries(data.checks || {}).forEach(([k, v]) => {
    const node = el(`#tpl-form [name="${k}"]`);
    if (node && node.type === 'checkbox') node.checked = !!v;
  });
  Object.entries(data.rows || {}).forEach(([key, rows]) => {
    const table = el(`[data-row-table="${key}"]`);
    if (!table || !Array.isArray(rows)) return;
    const tbody = table.querySelector('tbody');
    const addBtn = el(`[data-add-row="${key}"]`);
    tbody.innerHTML = '';
    rows.forEach((row) => {
      if (typeof window.__addRow === 'function') window.__addRow(key, row);
      else if (addBtn) {
        addBtn.click();
        const tr = tbody.lastElementChild;
        if (!tr) return;
        Object.entries(row).forEach(([n, v]) => {
          const input = tr.querySelector(`[name="${n}"]`);
          if (!input) return;
          if (input.type === 'checkbox') input.checked = !!v;
          else input.value = v ?? '';
        });
      }
    });
  });
}

function saveNow(announce = true) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(collect()));
    isDirty = false;
    if (announce) showToast('Saved on this device');
  } catch (e) {
    showToast('Could not save');
  }
}

function load() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    apply(JSON.parse(raw));
    showToast('Restored saved draft', 1800);
  } catch (_) { /* ignore */ }
}

function resetAll() {
  if (!confirm('Clear all fields and saved draft?')) return;
  localStorage.removeItem(STORAGE_KEY);
  el('#tpl-form')?.reset();
  els('[data-row-table] tbody').forEach((tb) => { tb.innerHTML = ''; });
  if (typeof window.__seedRows === 'function') window.__seedRows();
  isDirty = false;
  showToast('Reset');
}

function validateForm() {
  const required = els('#tpl-form [required]');
  let ok = true;
  required.forEach((node) => {
    if (!String(node.value || '').trim()) {
      ok = false;
      node.style.outline = '2px solid #ef4444';
    } else node.style.outline = '';
  });
  showToast(ok ? 'Looks good — ready to print' : 'Fill highlighted required fields');
  return ok;
}

document.addEventListener('DOMContentLoaded', () => {
  wireInputs();
  if (typeof window.__seedRows === 'function') window.__seedRows();
  load();
  el('#btn-save')?.addEventListener('click', () => saveNow(true));
  el('#btn-validate')?.addEventListener('click', validateForm);
  el('#btn-reset')?.addEventListener('click', resetAll);
  el('#btn-print')?.addEventListener('click', () => window.print());
  window.addEventListener('beforeunload', (e) => {
    if (isDirty) { e.preventDefault(); e.returnValue = ''; }
  });
});
