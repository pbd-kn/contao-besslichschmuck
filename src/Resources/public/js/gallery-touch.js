// JavaScript Document
// script für smartphone, damit der scroll un der touch sich nicht stören
document.addEventListener('DOMContentLoaded', function() {
  let touchTimer = null;
  let isTouchDevice = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;

  if (isTouchDevice) {
    document.querySelectorAll('.detail-img-zoom').forEach(img => {

      img.addEventListener('touchstart', function(e) {
        if (e.touches.length > 1) return;
        touchTimer = setTimeout(() => {
          img.classList.add('active');

          const onclick = img.getAttribute('onclick');
          if (onclick) {
            setTimeout(() => { eval(onclick); }, 10);
          }

          const evt = new MouseEvent('click', { bubbles: true, cancelable: true });
          img.dispatchEvent(evt);

        }, 400);
      });

      img.addEventListener('touchend', function() {
        clearTimeout(touchTimer);
        img.classList.remove('active');
      });

      img.addEventListener('touchmove', function() {
        clearTimeout(touchTimer);
      });
    });
  }
});
