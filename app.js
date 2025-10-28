/* Cafe Radio - dynamic menu rendering */
const STATE = {
  data: null,
  activeCategory: 'all',
  query: ''
};

async function loadData() {
  try {
    const response = await fetch('menu.json', { cache: 'no-store' });
    if (!response.ok) throw new Error('Failed to load menu.json');
    const data = await response.json();
    STATE.data = data;
    renderCategories(data.categories);
    renderMenu();
  } catch (error) {
    console.error(error);
    document.getElementById('menu').innerHTML = '<p>خطا در بارگذاری منو. لطفاً صفحه را رفرش کنید.</p>';
  }
}

function renderCategories(categories) {
  const container = document.getElementById('category-chips');
  container.innerHTML = '';

  const allChip = createChip('همه', 'all');
  container.appendChild(allChip);

  categories.forEach(cat => {
    const chip = createChip(cat.title, cat.id);
    container.appendChild(chip);
  });

  updateActiveChip();
}

function createChip(label, id) {
  const chip = document.createElement('button');
  chip.className = 'chip';
  chip.type = 'button';
  chip.textContent = label;
  chip.dataset.id = id;
  chip.addEventListener('click', () => {
    STATE.activeCategory = id;
    updateActiveChip();
    renderMenu();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  return chip;
}

function updateActiveChip() {
  document.querySelectorAll('.chip').forEach(el => {
    el.classList.toggle('active', el.dataset.id === STATE.activeCategory);
  });
}

function renderMenu() {
  if (!STATE.data) return;
  const { categories, items } = STATE.data;
  const menu = document.getElementById('menu');
  menu.innerHTML = '';

  const normalizedQuery = STATE.query.trim();
  const queryActive = normalizedQuery.length > 0;

  const itemsByCategory = {};
  categories.forEach(c => { itemsByCategory[c.id] = []; });

  items.forEach(item => {
    const inCategory = STATE.activeCategory === 'all' || item.categoryId === STATE.activeCategory;
    const matchQuery = !queryActive || item.title.includes(normalizedQuery) || (item.description || '').includes(normalizedQuery);
    if (inCategory && matchQuery) {
      itemsByCategory[item.categoryId]?.push(item);
    }
  });

  const renderCategory = (category) => {
    const list = itemsByCategory[category.id] || [];
    if (list.length === 0) return;
    const section = document.createElement('section');
    section.className = 'category';
    section.id = `cat-${category.id}`;
    section.innerHTML = `<h2>${category.title}</h2>`;

    const grid = document.createElement('div');
    grid.className = 'grid';
    list.forEach((item) => grid.appendChild(createCard(item)));
    section.appendChild(grid);
    menu.appendChild(section);
  };

  if (STATE.activeCategory === 'all') {
    categories.forEach(renderCategory);
  } else {
    const cat = categories.find(c => c.id === STATE.activeCategory);
    if (cat) renderCategory(cat);
  }

  if (!menu.children.length) {
    menu.innerHTML = '<p>موردی یافت نشد.</p>';
  }
}

function createCard(item) {
  const card = document.createElement('article');
  card.className = 'card';

  const imageWrap = document.createElement('div');
  imageWrap.className = 'image';
  const img = document.createElement('img');
  img.src = item.image || 'images/placeholder.png';
  img.alt = item.title;
  imageWrap.appendChild(img);

  const body = document.createElement('div');
  body.className = 'body';
  const title = document.createElement('h3');
  title.className = 'title';
  title.textContent = item.title;

  const desc = document.createElement('p');
  desc.className = 'desc';
  desc.textContent = item.description || '';

  const price = document.createElement('div');
  price.className = 'price';
  price.textContent = formatTomans(item.priceTomans);

  body.appendChild(title);
  if (item.description) body.appendChild(desc);
  body.appendChild(price);

  card.appendChild(imageWrap);
  card.appendChild(body);
  return card;
}

function formatTomans(value) {
  const number = Number(value || 0);
  return new Intl.NumberFormat('fa-IR').format(number) + ' تومان';
}

function setupSearch() {
  const input = document.getElementById('search-input');
  if (!input) return;
  input.addEventListener('input', () => {
    STATE.query = input.value;
    renderMenu();
  });
}

function setupYear() {
  const y = document.getElementById('year');
  if (y) y.textContent = String(new Date().getFullYear());
}

window.addEventListener('DOMContentLoaded', () => {
  setupSearch();
  setupYear();
  loadData();
});

