/*
 * Misurazione di Kris: i clic su telefono, WhatsApp, email e mappe come eventi
 * "contact" di GA4 e "Contact" del Pixel di Meta. Non imposta cookie: se
 * gtag.js o il Pixel non sono caricati (nessun consenso), non parte nulla.
 */
(function () {
  'use strict';
  function method(href) {
    if (/^tel:/i.test(href)) return 'telefono';
    if (/^mailto:/i.test(href)) return 'email';
    if (/^https?:\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)\//i.test(href)) return 'whatsapp';
    if (/^https?:\/\/((www\.)?google\.[a-z.]+\/maps|maps\.google\.[a-z.]+|maps\.app\.goo\.gl|goo\.gl\/maps|maps\.apple\.com)/i.test(href)) return 'mappa';
    return null;
  }
  document.addEventListener('click', function (event) {
    var link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
    if (!link) return;
    var how = method(link.getAttribute('href') || '');
    if (!how) return;
    if (typeof window.gtag === 'function') window.gtag('event', 'contact', { method: how, link_url: link.href, transport_type: 'beacon' });
    if (typeof window.fbq === 'function' && window.fbq.loaded) window.fbq('track', 'Contact', { content_name: how });
  }, true);
})();
