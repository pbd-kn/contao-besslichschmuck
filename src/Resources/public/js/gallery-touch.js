// --- Debug-Anzeige: Skript geladen ---
const DEBUG = false;
if (DEBUG) {
document.body.insertAdjacentHTML(
  "beforeend",
  '<div style="position:fixed;bottom:0;left:0;background:red;color:#fff;padding:6px;z-index:9999;">JS geladen!</div>'
);

alert("✅ gallery-touch.js wird richtig ausgeführt!");
}
document.addEventListener("DOMContentLoaded", function () {
  const isTouchDevice =
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0 ||
    navigator.msMaxTouchPoints > 0;
  if (!isTouchDevice) return;

  // --- CSS: Layout stabil + transparenter Hover ---
  const style = document.createElement("style");
  style.innerHTML = `
    .album_preview_item {
      position: relative;
    }

    .album_preview_item img {
      touch-action: manipulation;
      -webkit-user-select: none;
      user-select: none;
    }

    /* article-detail verstecken */
    .album_preview_item > .article-detail {
      display: none;
    }

    /* sichtbar, wenn aktiv */
    .album_preview_item.hover > .article-detail {
      display: block !important;
      max-height: 65vh;
      overflow-y: auto;
      padding: 1em;
      margin-top: 0.5em;

      /* ✨ statt weiß jetzt voll transparent – übernimmt Seitenhintergrund */
      background: transparent;
      backdrop-filter: none;

      color: #111;
      font-size: 0.95rem;
      line-height: 1.5;
      border: 1px solid rgba(200, 200, 200, 0.4);
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* Scrollbar-Styling optional */
    .album_preview_item.hover > .article-detail::-webkit-scrollbar {
      width: 8px;
    }
    .album_preview_item.hover > .article-detail::-webkit-scrollbar-thumb {
      background: #999;
      border-radius: 4px;
    }
  `;
  document.head.appendChild(style);

  // --- Debug ---
  function debug(msg) {
if (!DEBUG) return; // → kein Output
    let box = document.getElementById("debugBox");
    if (!box) {
      box = document.createElement("div");
      Object.assign(box.style, {
        position: "fixed",
        bottom: "10px",
        left: "10px",
        width: "90%",
        maxWidth: "800px",
        background: "rgba(0,0,0,0.8)",
        color: "#0f0",
        fontSize: "14px",
        lineHeight: "1.3",
        fontFamily: "monospace",
        zIndex: "9999",
        borderRadius: "6px",
        padding: "6px 10px",
      });
      document.body.appendChild(box);
    }
    box.innerHTML = msg;
  }

  debug("✅ Script gestartet – warte auf Touch...");

  const hoverItems = document.querySelectorAll(".album_preview_item img");
  const longPressDuration = 800;
  const allowClick = new WeakMap();

  // --- Safari-Klick blockieren ---
  document.addEventListener(
    "click",
    (e) => {
      const img = e.target.closest(".album_preview_item img");
      if (!img) return;
      if (allowClick.get(img) !== true) {
        e.stopImmediatePropagation();
        e.preventDefault();
        debug("🚫 Nativer Klick blockiert (kurzer Tap)");
      } else {
        allowClick.set(img, false);
        debug("✅ Klick nach Longpress erlaubt");
      }
    },
    true
  );

  // --- Haupt-Event-Logik ---
  hoverItems.forEach((img) => {
    let touchTimer;
    let hasLongPressed = false;
    let fingerDown = false;
    allowClick.set(img, false);

    img.addEventListener(
      "touchstart",
      (e) => {
        if (e.touches.length > 1) return;
        fingerDown = true;
        hasLongPressed = false;
        e.preventDefault();

        touchTimer = setTimeout(() => {
          if (!fingerDown) return;
          hasLongPressed = true;
          allowClick.set(img, true);
          debug("⏱️ Longpress → Klick");

          const onclick = img.getAttribute("onclick");
          if (onclick) {
            try { eval(onclick); } catch (err) { debug("⚠️ onclick: " + err.message); }
          } else {
            const evt = new MouseEvent("click", { bubbles: true, cancelable: true });
            img.dispatchEvent(evt);
          }
          setTimeout(() => allowClick.set(img, false), 0);
        }, longPressDuration);
      },
      { passive: false }
    );

    img.addEventListener("touchend", () => {
      fingerDown = false;
      clearTimeout(touchTimer);

      if (!hasLongPressed) {
        const parent = img.closest(".album_preview_item");
        if (parent) {
          document
            .querySelectorAll(".album_preview_item.hover")
            .forEach((el) => el.classList.remove("hover"));
          parent.classList.add("hover");
          debug("👆 Kurzer Tap → Hover geöffnet (bleibt offen)");
        }
      }
    });

    img.addEventListener("touchmove", () => {
      fingerDown = false;
      clearTimeout(touchTimer);
    });
  });

  // --- Nur Tap außerhalb schließt Hover ---
  document.addEventListener(
    "touchstart",
    (e) => {
      const hoveredItem = document.querySelector(".album_preview_item.hover");
      if (!hoveredItem) return;

      const tappedInside = e.target.closest(".album_preview_item") === hoveredItem;
      if (tappedInside) {
        debug("↪️ Tap im geöffneten Detail → bleibt offen");
        return;
      }

      hoveredItem.classList.remove("hover");
      debug("🚫 Tap außerhalb → Hover geschlossen");
    },
    { passive: true }
  );

  // 🚫 kein Scroll-Handler: Scrollen löscht nichts!
});
