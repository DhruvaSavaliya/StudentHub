(() => {
  "use strict";
  const config = {
    events: { url: "../data/events.json", cache: "studentHubEvents", searchable: ["title", "location", "category"], filterKey: "category", card: item => [["Date", formatDate(item.date)], ["Location", item.location], ["Category", item.category]] },
    students: { url: "../data/students.json", cache: "studentHubStudents", searchable: ["name", "email", "course"], filterKey: "course", card: item => [["Email", item.email], ["Course", item.course], ["Year", `Year ${item.year}`]] },
    faq: { url: "../data/faq.json", cache: "studentHubFaq", searchable: ["question", "answer", "category"], filterKey: "category", card: item => [["Topic", item.category], ["Answer", item.answer]] }
  };
  const kind = document.body.dataset.dataset;
  const settings = config[kind];
  if (!settings) return;
  const state = { all: [], page: 1, perPage: 6 };
  const search = document.querySelector("#search");
  const filter = document.querySelector("#filter");
  const sort = document.querySelector("#sort");
  const results = document.querySelector("#results");
  const status = document.querySelector("#status");
  const pagination = document.querySelector("#pagination");
  const summary = document.querySelector("#resultsSummary");

  function formatDate(value) {
    const date = new Date(`${value}T12:00:00`);
    return Number.isNaN(date.valueOf()) ? value : new Intl.DateTimeFormat(undefined, { dateStyle: "medium" }).format(date);
  }
  function setStatus(message, stateName = "") {
    status.textContent = message;
    status.dataset.state = stateName;
  }
  function render() {
    const query = search.value.trim().toLocaleLowerCase();
    let rows = state.all.filter(item => settings.searchable.some(key => String(item[key] ?? "").toLocaleLowerCase().includes(query)));
    if (filter.value) rows = rows.filter(item => item[settings.filterKey] === filter.value);
    const [key, direction] = sort.value.endsWith("-desc") ? [sort.value.slice(0, -5), -1] : [sort.value, 1];
    rows.sort((a, b) => {
      const left = a[key], right = b[key];
      const comparison = key === "year" ? Number(left) - Number(right) : key === "date" ? String(left).localeCompare(String(right)) : String(left ?? "").localeCompare(String(right ?? ""), undefined, { sensitivity: "base", numeric: true });
      return comparison * direction;
    });
    const pageCount = Math.max(1, Math.ceil(rows.length / state.perPage));
    state.page = Math.min(state.page, pageCount);
    const visible = rows.slice((state.page - 1) * state.perPage, state.page * state.perPage);
    results.replaceChildren();
    for (const item of visible) {
      const card = document.createElement("article"); card.className = "data-card";
      const heading = document.createElement("h2"); heading.textContent = kind === "events" ? item.title : kind === "students" ? item.name : item.question; card.append(heading);
      for (const [label, value] of settings.card(item)) {
        const p = document.createElement("p"); const strong = document.createElement("strong"); strong.textContent = `${label}: `; p.append(strong, document.createTextNode(String(value ?? ""))); card.append(p);
      }
      results.append(card);
    }
    results.setAttribute("aria-busy", "false");
    const start = rows.length ? (state.page - 1) * state.perPage + 1 : 0;
    const end = Math.min(state.page * state.perPage, rows.length);
    summary.textContent = `${rows.length} ${kind === "faq" ? "questions" : kind} found · showing ${start}–${end}`;
    setStatus(rows.length ? "Data loaded. Use the controls to refine the results." : "No matching records. Try changing your search or filter.", rows.length ? "success" : "empty");
    pagination.replaceChildren();
    const button = (label, page, disabled, text = label) => { const b = document.createElement("button"); b.type = "button"; b.className = "page-button"; b.textContent = text; b.setAttribute("aria-label", label); b.disabled = disabled; if (page === state.page) b.setAttribute("aria-current", "page"); b.addEventListener("click", () => { state.page = page; render(); }); pagination.append(b); };
    if (pageCount > 1) {
      button("Previous page", state.page - 1, state.page === 1, "‹ Previous");
      for (let page = 1; page <= pageCount; page++) button(`Page ${page}`, page, false, String(page));
      button("Next page", state.page + 1, state.page === pageCount, "Next ›");
    }
  }
  function loadCached() {
    try { const cached = JSON.parse(localStorage.getItem(settings.cache)); return Array.isArray(cached) ? cached : null; } catch { return null; }
  }
  async function loadData() {
    results.setAttribute("aria-busy", "true"); setStatus("Loading data…", "loading");
    try {
      const response = await fetch(settings.url, { headers: { Accept: "application/json" }, cache: "no-cache" });
      if (!response.ok) throw new Error(`Request failed (${response.status})`);
      const data = await response.json();
      if (!Array.isArray(data)) throw new Error("The JSON response must contain an array.");
      state.all = data;
      try { localStorage.setItem(settings.cache, JSON.stringify(data)); } catch { /* Storage can be unavailable; fetched data still works. */ }
      if (kind === "events" || kind === "students" || kind === "faq") {
        const values = [...new Set(data.map(item => item[settings.filterKey]).filter(Boolean))].sort((a, b) => a.localeCompare(b));
        for (const value of values) if (![...filter.options].some(option => option.value === value)) filter.add(new Option(value, value));
      }
      render();
    } catch (error) {
      const cached = loadCached();
      if (cached?.length) { state.all = cached; render(); setStatus("Could not refresh the JSON file; showing the last saved copy.", "warning"); return; }
      results.setAttribute("aria-busy", "false"); setStatus(`Could not load data. ${error.message} Check that the site is running on a local web server.`, "error");
      results.replaceChildren();
      const retry = document.createElement("button"); retry.type = "button"; retry.className = "btn btn-blue"; retry.textContent = "Try again"; retry.addEventListener("click", loadData); results.append(retry);
    }
  }
  for (const control of [search, filter, sort]) control.addEventListener(control === search ? "input" : "change", () => { state.page = 1; render(); });
  loadData();
})();
