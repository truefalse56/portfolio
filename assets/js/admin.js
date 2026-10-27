(() => {
  const sidebar = document.querySelector("[data-sidebar]");
  const toggle = document.querySelector("[data-sidebar-toggle]");
  if (sidebar && toggle) {
    toggle.addEventListener("click", () => sidebar.classList.toggle("is-open"));
  }

  document.querySelectorAll("[data-confirm]").forEach((el) => {
    el.addEventListener("click", (e) => {
      const msg = el.getAttribute("data-confirm") || "Удалить?";
      if (!confirm(msg)) e.preventDefault();
    });
  });
})();
