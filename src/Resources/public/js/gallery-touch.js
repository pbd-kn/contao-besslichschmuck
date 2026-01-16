// --- gallery-touch.js ---
// Touch-Erweiterung für Contao GalleryCreator-Template
// Kurzer Tap -> zeigt Detail in .logo
// Longpress  -> Klick (onclick oder Event)
// Verhindert Doppelanzeige unter dem Bild
// 2025-11-11 final

document.addEventListener("DOMContentLoaded", function () {
  const isTouch = ("ontouchstart" in window) || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
  if (!isTouch) return;

  // ---------- CSS (automatisch eingefügt) ----------
  const css = `
    .album_preview_item { position: relative; }

    .album_preview_item img {
      touch-action: manipulation;
      -webkit-user-select: none;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
    }

    .album_preview_item .detail-img-zoom.iconborder {
      outline: 2px solid rgba(0,0,0,0.25);
      outline-offset: 2px;
      box-shadow: 0 0 0 3px rgba(0,0,0,0.08);
      border-radius: 8px;
      transition: outline-color .2s ease, box-shadow .2s ease;
    }

    /* Wichtig: Artikel-Detail niemals im Album anzeigen */
    .album_preview_item > .article-detail {
      display: none !important;
      visibility: hidden !important;
      opacity: 0 !important;
      height: 0 !important;
      overflow: hidden !important;
    }

    .logo { position: relative; min-height: 1px; }

    .logo .schmuck-detail-inside {
      background: transparent;
      padding: 1rem 1.25rem;
      margin: .5rem 0;
      max-height: 65vh;
      overflow-y: auto;
      animation: fadeIn 0.25s ease;
    }
    /* Abstand unter dem großen Bild in der Detailansicht (.logo-Bereich) */
    .logo .detail_image,
    .logo .detail_image .image_container,
    .logo .detail_image img {
      display: block;
      margin-bottom: 1.5rem; /* etwas mehr Luft */
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .logo .schmuck-detail-inside::-webkit-scrollbar { width: 10px; }
    .logo .schmuck-detail-inside::-webkit-scrollbar-thumb {
      background: rgba(0,0,0,0.25);
      border-radius: 6px;
    }
  `;
  const styleEl = document.createElement("style");
  styleEl.textContent = css;
  document.head.appendChild(styleEl);

  // ---------- LOGIK ----------
  const itemSel   = ".album_preview_item";
  const imgSel    = ".album_preview_item img";
  const detailSel = ".article-detail";
  const mainSel   = "#main";
  const logoSel   = ".logo";

  // Alle Detailboxen *sofort* verstecken (sicherer als CSS allein)
  document.querySelectorAll(detailSel).forEach(el => {
    el.style.display = "none";
    el.style.visibility = "hidden";
    el.style.opacity = "0";
    el.style.height = "0";
    el.style.overflow = "hidden";
  });

  // Klicks blocken, außer nach Longpress
  const allowClick = new WeakMap();
  document.addEventListener("click", function (e) {
    const img = e.target.closest(imgSel);
    if (!img) return;
    if (allowClick.get(img) === true) { allowClick.set(img, false); return; }
    e.stopImmediatePropagation();
    e.preventDefault();
  }, true);

  // Detail in .logo anzeigen
  function showDetailBeside($img) {
    if (typeof window.oldicon !== "undefined") jQuery(window.oldicon).removeClass("iconborder");
    $img.addClass("iconborder");
    window.oldicon = $img[0];

    const $container = $img.closest(mainSel);
    const containerRect = $container[0].getBoundingClientRect();
    const $acl = $img.closest(itemSel);
    const elemRect = $acl[0].getBoundingClientRect();
    const offset = elemRect.top - containerRect.top;

    const at = $acl.find(detailSel).html();

    // In .logo schreiben
    const $logo = $container.find(logoSel).html(at);
    $logo.css({ position: "relative", top: offset + "px" }).focus();

    // Sicherheit: eingebettetes Detail sofort unsichtbar machen
    $acl.find(detailSel).css({
      display: "none",
      visibility: "hidden",
      opacity: "0",
      height: "0",
      overflow: "hidden"
    });
  }

  // Touch-Handling
  jQuery(imgSel).each(function () {
    const img = this;
    let timer, isLong = false, fingerDown = false;
    allowClick.set(img, false);

    img.addEventListener("touchstart", function (e) {
      if (e.touches.length > 1) return;
      fingerDown = true; isLong = false;
      e.preventDefault();

      timer = setTimeout(function () {
        if (!fingerDown) return;
        isLong = true;
        allowClick.set(img, true);

        const onclick = img.getAttribute("onclick");
        if (onclick) {
          try { setTimeout(() => { eval(onclick); }, 0); } catch {}
        }
        const evt = new MouseEvent("click", { bubbles: true, cancelable: true });
        img.dispatchEvent(evt);
        setTimeout(() => allowClick.set(img, false), 0);
      }, 800);
    }, { passive: false });

    img.addEventListener("touchend", function () {
      fingerDown = false;
      clearTimeout(timer);
      if (!isLong) showDetailBeside(jQuery(img));
    });

    img.addEventListener("touchmove", function () {
      fingerDown = false;
      clearTimeout(timer);
    });
  });
});
