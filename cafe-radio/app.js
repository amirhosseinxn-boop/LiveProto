const state = {
  items: [],
  filtered: [],
  categories: [],
  activeCategory: "all"
};

function formatPriceRialToToman(rial){
  if (rial == null || isNaN(rial)) return "-";
  const toman = Math.round(Number(rial) / 10);
  return new Intl.NumberFormat("fa-IR").format(toman) + " تومان";
}

async function loadMenu(){
  try{
    const res = await fetch("menu.json", { cache: "no-store" });
    const data = await res.json();
    state.items = data.items || [];
    state.categories = ["all", ...new Set(state.items.map(i => i.category))];
    renderCategories();
    applyFilters();
  }catch(err){
    console.error("Failed to load menu.json", err);
  }
}

function renderCategories(){
  const list = document.getElementById("categoryList");
  list.innerHTML = "";
  state.categories.forEach(cat => {
    const b = document.createElement("button");
    b.className = "cat-btn" + (state.activeCategory === cat ? " active" : "");
    b.textContent = cat === "all" ? "همه" : cat;
    b.addEventListener("click", () => {
      state.activeCategory = cat;
      document.querySelectorAll(".cat-btn").forEach(x => x.classList.remove("active"));
      b.classList.add("active");
      applyFilters();
    });
    list.appendChild(b);
  });
}

function applyFilters(){
  const q = document.getElementById("searchInput").value.trim();
  const cat = state.activeCategory;
  state.filtered = state.items.filter(item => {
    const matchesCat = cat === "all" || item.category === cat;
    const matchesQuery = !q || [item.title, item.en, item.category]
      .filter(Boolean)
      .some(v => String(v).toLowerCase().includes(q.toLowerCase()));
    return matchesCat && matchesQuery;
  });
  renderGrid();
}

function renderGrid(){
  const grid = document.getElementById("menuGrid");
  const tpl = document.getElementById("itemCardTemplate");
  grid.innerHTML = "";
  state.filtered.forEach(item => {
    const node = tpl.content.cloneNode(true);
    const img = node.querySelector(".card-img");
    img.src = item.image || "assets/img/placeholder.jpg";
    img.alt = item.title || "";
    node.querySelector(".card-title").textContent = item.title;
    node.querySelector(".price").textContent = formatPriceRialToToman(item.price_rial);
    grid.appendChild(node);
  });
}

function wireUp(){
  document.getElementById("year").textContent = new Date().getFullYear();
  const search = document.getElementById("searchInput");
  search.addEventListener("input", applyFilters);
}

window.addEventListener("DOMContentLoaded", () => { wireUp(); loadMenu(); });
