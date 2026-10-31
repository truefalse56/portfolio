(() => {
  document.querySelectorAll("[data-confirm]").forEach((el) => {
    el.addEventListener("click", (e) => {
      const msg = el.getAttribute("data-confirm") || "Удалить?";
      if (!confirm(msg)) e.preventDefault();
    });
  });

  const article = document.querySelector("[data-article-content]");
  document.querySelectorAll("[data-insert-lang]").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!article) return;
      const lang = btn.getAttribute("data-insert-lang") || "text";
      const snippet = "\n```" + lang + "\n\n```\n";
      const start = article.selectionStart ?? article.value.length;
      const end = article.selectionEnd ?? start;
      article.value = article.value.slice(0, start) + snippet + article.value.slice(end);
      const cursor = start + ("\n```" + lang + "\n").length;
      article.focus();
      article.selectionStart = article.selectionEnd = cursor;
    });
  });
})();
