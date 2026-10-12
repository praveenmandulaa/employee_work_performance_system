document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (event) => {
      if (!window.confirm(el.getAttribute('data-confirm') || 'Are you sure?')) event.preventDefault();
    });
  });
  document.querySelectorAll('textarea').forEach((field) => {
    field.addEventListener('input', () => {
      field.style.height = 'auto';
      field.style.height = Math.min(field.scrollHeight, 260) + 'px';
    });
  });
});
