(() => {
  "use strict";

  const menuButton = document.querySelector("#hamburger");
  const menu = document.querySelector("#navLinks");
  if (menuButton && menu) {
    menuButton.addEventListener("click", () => {
      const open = menu.classList.toggle("show");
      menuButton.setAttribute("aria-expanded", String(open));
      menuButton.setAttribute("aria-label", open ? "Close menu" : "Open menu");
      menuButton.textContent = open ? "✕" : "☰";
    });
    menu.addEventListener("click", event => {
      if (event.target.closest("a")) {
        menu.classList.remove("show");
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-label", "Open menu");
        menuButton.textContent = "☰";
      }
    });
  }

  const themeButton = document.querySelector("#themeToggle");
  if (themeButton) {
    try {
      document.body.classList.toggle("dark-mode", localStorage.getItem("studentHubTheme") === "dark");
    } catch { /* Storage may be unavailable; the theme toggle still works in this page. */ }
    const updateThemeLabel = () => {
      const dark = document.body.classList.contains("dark-mode");
      themeButton.textContent = dark ? "☀️" : "🌙";
      themeButton.setAttribute("aria-label", `Switch to ${dark ? "light" : "dark"} theme`);
      themeButton.setAttribute("aria-pressed", String(dark));
    };
    updateThemeLabel();
    themeButton.addEventListener("click", () => {
      const dark = document.body.classList.toggle("dark-mode");
      try { localStorage.setItem("studentHubTheme", dark ? "dark" : "light"); } catch { /* Continue without persistence. */ }
      updateThemeLabel();
    });
  }

  const banner = document.querySelector("#notification");
  document.querySelector("#closeNotification")?.addEventListener("click", () => banner?.remove());

  const image = document.querySelector("#sliderImage");
  if (image) {
    const slides = [
      { src: "../../assets/images/charusat.png", alt: "CHARUSAT campus" },
      { src: "../../assets/images/event1.jpeg", alt: "A StudentHub campus event" },
      { src: "../../assets/images/event2.jpeg", alt: "Students taking part in a campus event" },
      { src: "../../assets/images/event3.jpeg", alt: "A university cultural event" }
    ];
    const dots = document.querySelector("#sliderDots");
    let active = 0;
    const show = index => {
      active = (index + slides.length) % slides.length;
      image.src = slides[active].src;
      image.alt = slides[active].alt;
      dots?.querySelectorAll("button").forEach((dot, number) => {
        dot.classList.toggle("active", number === active);
        dot.setAttribute("aria-current", String(number === active));
      });
    };
    slides.forEach((slide, index) => {
      const dot = document.createElement("button");
      dot.type = "button";
      dot.className = "slider-dot";
      dot.setAttribute("aria-label", `Show slide ${index + 1}`);
      dot.addEventListener("click", () => show(index));
      dots?.append(dot);
    });
    show(0);
    document.querySelector("#prevSlide")?.addEventListener("click", () => show(active - 1));
    document.querySelector("#nextSlide")?.addEventListener("click", () => show(active + 1));
    if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      let timer = window.setInterval(() => show(active + 1), 5000);
      image.closest(".slider")?.addEventListener("mouseenter", () => window.clearInterval(timer), { once: true });
    }
  }

  const dialog = document.querySelector("#infoModal");
  const opener = document.querySelector("#learnMoreBtn");
  const closeDialog = () => {
    if (!dialog) return;
    dialog.classList.remove("show");
    dialog.setAttribute("aria-hidden", "true");
    opener?.focus();
  };
  if (dialog) {
    dialog.setAttribute("aria-hidden", String(!dialog.classList.contains("show")));
    opener?.addEventListener("click", () => {
      dialog.classList.add("show");
      dialog.setAttribute("aria-hidden", "false");
      dialog.querySelector("#modalClose")?.focus();
    });
    dialog.querySelector("#modalClose")?.addEventListener("click", closeDialog);
    dialog.querySelector("#modalOk")?.addEventListener("click", closeDialog);
    dialog.addEventListener("click", event => { if (event.target === dialog) closeDialog(); });
  }
  document.addEventListener("keydown", event => {
    if (event.key === "Escape" && dialog?.classList.contains("show")) closeDialog();
    if (event.key === "Escape" && menu?.classList.contains("show")) {
      menu.classList.remove("show");
      menuButton.setAttribute("aria-expanded", "false");
      menuButton.setAttribute("aria-label", "Open menu");
      menuButton.textContent = "☰";
      menuButton.focus();
    }
    if (event.key === "Tab" && dialog?.classList.contains("show")) {
      const focusable = [...dialog.querySelectorAll('button:not([disabled]), a[href], input:not([disabled])')];
      const first = focusable[0], last = focusable.at(-1);
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
  });

  document.querySelectorAll(".faq-question").forEach(question => {
    question.setAttribute("aria-expanded", "false");
    question.addEventListener("click", () => {
      const expanded = question.parentElement.classList.toggle("active");
      question.setAttribute("aria-expanded", String(expanded));
    });
  });
})();
