(() => {
  document.querySelectorAll("[data-confirm]").forEach((el) => {
    el.addEventListener("click", (e) => {
      const msg = el.getAttribute("data-confirm") || "Удалить?";
      if (!confirm(msg)) e.preventDefault();
    });
  });
})();
