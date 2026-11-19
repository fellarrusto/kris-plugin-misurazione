# Misurazione — plugin di Kris

Google Analytics 4 e Pixel di Meta con Consent Mode v2, e i clic su telefono, WhatsApp, email e mappe come eventi.

- **Consenso.** Prima di ogni altra cosa imposta Consent Mode v2 con tutto negato (o con la scelta già salvata nel cookie `kris_consent`) e lo aggiorna all'evento `kris:consent`. `gtag.js` parte solo con il consenso alle statistiche, il Pixel solo con quello al marketing (`consent.js` di Kris): il tema deve avere il banner dei cookie.
- **Impostazioni** (Plugin › Misurazione): ID di misurazione GA4 (`G-…`), ID del Pixel, eventi di contatto.
- **Eventi.** `contact` di GA4 con `method` (`telefono`, `whatsapp`, `email`, `mappa`) e `Contact` del Pixel; `public/eventi.js` non imposta cookie.
- **Cookie dichiarati:** `_ga`, `_ga_<ID>` (statistiche), `_fbp` (marketing). Il plugin Cookie policy automatica li mette nella tabella dell'informativa.

Contratto dei plugin: `docs/2.0/plugin.md` in [kris-cms](https://github.com/fellarrusto/kris-cms). Release: `php <kris-cms>/tools/plugin-release.php --key … --changelog "…" --publish` dalla radice di questa repository.
