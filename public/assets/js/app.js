// Confirmación sencilla usando data-confirm
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  const msg = btn.getAttribute('data-confirm') || '¿Seguro?';
  if (!confirm(msg)) e.preventDefault();
});

// Auto-focus al primer input visible
window.addEventListener('DOMContentLoaded', () => {
  const firstInput = document.querySelector('input, select, textarea');
  if (firstInput) firstInput.focus();
});

// Formateo "bonito" de <input type=number> al perder foco (opcional)
document.addEventListener('blur', e=>{
  const el = e.target;
  if (el.matches('input[data-money]')) {
    const v = parseFloat(el.value.replace(',', '.')) || 0;
    el.value = v.toFixed(2);
  }
}, true);
