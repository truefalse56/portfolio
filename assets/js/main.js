(() => {
  const header = document.querySelector("[data-header]");
  const themeToggle = document.querySelector("[data-theme-toggle]");
  const root = document.documentElement;

  const getTheme = () =>
    root.getAttribute("data-bs-theme") === "dark" ? "dark" : "light";

  const setTheme = (theme) => {
    root.setAttribute("data-bs-theme", theme);
    try {
      localStorage.setItem("theme", theme);
    } catch {
      /* ignore */
    }
    if (themeToggle) {
      themeToggle.setAttribute(
        "aria-label",
        theme === "dark" ? "Включить светлую тему" : "Включить тёмную тему"
      );
      themeToggle.setAttribute(
        "title",
        theme === "dark" ? "Светлая тема" : "Тёмная тема"
      );
    }
  };

  if (!root.getAttribute("data-bs-theme")) {
    setTheme("light");
  } else {
    setTheme(getTheme());
  }

  themeToggle?.addEventListener("click", () => {
    setTheme(getTheme() === "dark" ? "light" : "dark");
  });

  const onScroll = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  };
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const revealEls = document.querySelectorAll(".reveal, [data-animate]");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const el = entry.target;
            const delay = el.getAttribute("data-delay");
            if (delay) el.style.transitionDelay = `${delay}ms`;
            el.classList.add("is-in");
            io.unobserve(el);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("is-in"));
  }

  const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return;
    }
    const area = document.createElement("textarea");
    area.value = text;
    area.setAttribute("readonly", "");
    area.style.position = "fixed";
    area.style.top = "0";
    area.style.left = "-9999px";
    document.body.appendChild(area);
    area.select();
    const ok = document.execCommand("copy");
    area.remove();
    if (!ok) {
      throw new Error("copy failed");
    }
  };

  document.querySelectorAll("[data-copy]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const block = btn.closest(".code-block");
      const code = block?.querySelector("code");
      if (!code) return;
      const prev = btn.textContent;
      try {
        await copyText(code.textContent || "");
        btn.textContent = "Скопировано";
        setTimeout(() => {
          btn.textContent = prev;
        }, 1600);
      } catch {
        btn.textContent = "Ошибка";
        setTimeout(() => {
          btn.textContent = prev;
        }, 1600);
      }
    });
  });

  document.querySelectorAll(".hobby-carousel").forEach((el) => {
    const current = el.querySelector("[data-carousel-current]");
    if (!current) return;
    el.addEventListener("slid.bs.carousel", (e) => {
      current.textContent = String((e.to || 0) + 1);
    });
  });

  const siteNav = document.getElementById("siteNav");
  if (siteNav && typeof bootstrap !== "undefined") {
    siteNav.querySelectorAll(".nav-link").forEach((link) => {
      link.addEventListener("click", () => {
        if (window.matchMedia("(max-width: 991.98px)").matches) {
          bootstrap.Collapse.getOrCreateInstance(siteNav).hide();
        }
      });
    });
  }
})();
