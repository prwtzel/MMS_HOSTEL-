document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(open));
    });
  }
  document.querySelectorAll('form').forEach(form => {
    const start = form.querySelector('input[name="check_in"]');
    const end = form.querySelector('input[name="check_out"]');
    if (!start || !end) return;
    const sync = () => {
      if (start.value) {
        const d = new Date(start.value + 'T00:00:00');
        d.setDate(d.getDate() + 1);
        const min = d.toISOString().slice(0, 10);
        end.min = min;
        if (end.value && end.value <= start.value) end.value = min;
      }
    };
    start.addEventListener('change', sync);
    sync();
  });
});
