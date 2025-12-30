document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.querySelector('[data-agent-search]');
  const cards = document.querySelectorAll('[data-agent-card]');

  if (!searchInput || !cards.length) {
    return;
  }

  searchInput.addEventListener('input', function () {
    const query = searchInput.value.trim().toLowerCase();

    cards.forEach((card) => {
      const name = (card.dataset.name || '').toLowerCase();
      const city = (card.dataset.city || '').toLowerCase();

      if (!query || name.includes(query) || city.includes(query)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
