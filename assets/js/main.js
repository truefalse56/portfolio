(() => {
  const header = document.querySelector("[data-header]");
  const toggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");
  const themeToggle = document.querySelector("[data-theme-toggle]");
  const root = document.documentElement;

  const getTheme = () =>
    root.getAttribute("data-theme") === "dark" ? "dark" : "light";

  const setTheme = (theme) => {
    if (theme === "dark") {
      root.setAttribute("data-theme", "dark");
    } else {
      root.removeAttribute("data-theme");
    }
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

  setTheme(getTheme());

  themeToggle?.addEventListener("click", () => {
    setTheme(getTheme() === "dark" ? "light" : "dark");
  });

  const onScroll = () => {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  };
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", nav.classList.contains("is-open") ? "true" : "false");
    });

    nav.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => nav.classList.remove("is-open"));
    });
  }

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

  document.querySelectorAll("[data-copy]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const block = btn.closest(".code-block");
      const code = block?.querySelector("code");
      if (!code) return;
      try {
        await navigator.clipboard.writeText(code.textContent || "");
        const prev = btn.textContent;
        btn.textContent = "Скопировано";
        setTimeout(() => {
          btn.textContent = prev;
        }, 1600);
      } catch {
        btn.textContent = "Ошибка";
      }
    });
  });

  document.querySelectorAll("[data-slider]").forEach((root) => {
    const track = root.querySelector("[data-slider-track]");
    const slides = [...root.querySelectorAll("[data-slider-slide]")];
    const prevBtn = root.querySelector("[data-slider-prev]");
    const nextBtn = root.querySelector("[data-slider-next]");
    const dots = [...root.querySelectorAll("[data-slider-dot]")];
    const currentEl = root.querySelector("[data-slider-current]");
    if (!track || slides.length < 2) return;

    let index = 0;
    let startX = 0;
    let deltaX = 0;
    let dragging = false;

    const goTo = (next) => {
      index = (next + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      slides.forEach((slide, i) => {
        slide.setAttribute("aria-hidden", i === index ? "false" : "true");
      });
      dots.forEach((dot, i) => dot.classList.toggle("is-active", i === index));
      if (currentEl) currentEl.textContent = String(index + 1);
    };

    prevBtn?.addEventListener("click", () => goTo(index - 1));
    nextBtn?.addEventListener("click", () => goTo(index + 1));
    dots.forEach((dot) => {
      dot.addEventListener("click", () => goTo(Number(dot.dataset.sliderDot) || 0));
    });

    root.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") goTo(index - 1);
      if (e.key === "ArrowRight") goTo(index + 1);
    });
    root.setAttribute("tabindex", "0");

    const onStart = (x) => {
      dragging = true;
      startX = x;
      deltaX = 0;
      track.style.transition = "none";
    };
    const onMove = (x) => {
      if (!dragging) return;
      deltaX = x - startX;
      const pct = (deltaX / root.clientWidth) * 100;
      track.style.transform = `translateX(calc(-${index * 100}% + ${pct}%))`;
    };
    const onEnd = () => {
      if (!dragging) return;
      dragging = false;
      track.style.transition = "";
      if (Math.abs(deltaX) > root.clientWidth * 0.18) {
        goTo(deltaX < 0 ? index + 1 : index - 1);
      } else {
        goTo(index);
      }
    };

    track.addEventListener("pointerdown", (e) => {
      if (e.pointerType === "mouse" && e.button !== 0) return;
      track.setPointerCapture(e.pointerId);
      onStart(e.clientX);
    });
    track.addEventListener("pointermove", (e) => onMove(e.clientX));
    track.addEventListener("pointerup", onEnd);
    track.addEventListener("pointercancel", onEnd);

    let timer = setInterval(() => goTo(index + 1), 6000);
    root.addEventListener("pointerenter", () => clearInterval(timer));
    root.addEventListener("pointerleave", () => {
      timer = setInterval(() => goTo(index + 1), 6000);
    });
  });
})();
