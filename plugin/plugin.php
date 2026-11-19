<?php
defined('KRIS_ROOT') || exit;

use Kris\Plugin\Context;

/**
 * Misurazione: GA4 e Pixel di Meta solo dopo il consenso (statistics e
 * marketing), con Consent Mode v2 di Google impostato prima di tutto, e i clic
 * di contatto come eventi.
 */
return function (Context $kris): void {
    $ids = function () use ($kris): array {
        $s = $kris->settings();
        $ga4 = strtoupper(trim((string) ($s['ga4_id'] ?? '')));
        $pixel = trim((string) ($s['meta_pixel_id'] ?? ''));
        return [
            preg_match('/^G-[A-Z0-9]{4,20}$/', $ga4) === 1 ? $ga4 : '',
            preg_match('/^\d{5,20}$/', $pixel) === 1 ? $pixel : '',
            ($s['contact_events'] ?? false) === true,
        ];
    };

    $kris->on('head', function (array $page) use ($kris, $ids): string {
        [$ga4, $pixel] = $ids();
        if ($ga4 === '' && $pixel === '') return '';
        // Consent Mode v2: tutto negato finché il visitatore non sceglie; la scelta già fatta vale subito
        $html = $kris->tag(<<<'HTML'
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
(function () {
  function read() {
    var m = document.cookie.match(/(?:^|;\s*)kris_consent=([^;]*)/);
    var v = m ? decodeURIComponent(m[1]) : '';
    if (v === 'accepted') return {statistics: true, marketing: true};
    var out = {};
    if (v.indexOf('c:') === 0) v.slice(2).split(',').forEach(function (c) { out[c] = true; });
    return out;
  }
  function state(c) {
    var s = c.statistics ? 'granted' : 'denied', m = c.marketing ? 'granted' : 'denied';
    return {analytics_storage: s, ad_storage: m, ad_user_data: m, ad_personalization: m};
  }
  var d = state(read()); d.wait_for_update = 500;
  gtag('consent', 'default', d);
  document.addEventListener('kris:consent', function (e) { gtag('consent', 'update', state((e.detail && e.detail.categories) || {})); });
})();
</script>
HTML, 'technical');
        if ($ga4 !== '') {
            $html .= "\n" . $kris->script('https://www.googletagmanager.com/gtag/js?id=' . rawurlencode($ga4), 'statistics', ['async' => true]);
            $html .= "\n" . $kris->tag('<script>gtag(\'js\', new Date()); gtag(\'config\', ' . json_encode($ga4) . ');</script>', 'statistics');
        }
        if ($pixel !== '') {
            $html .= "\n" . $kris->tag('<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};'
                . 'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];'
                . 's.parentNode.insertBefore(t,s)}(window,document,\'script\',\'https://connect.facebook.net/en_US/fbevents.js\');'
                . 'fbq(\'init\', ' . json_encode($pixel) . '); fbq(\'track\', \'PageView\');</script>', 'marketing');
        }
        return $html;
    });

    $kris->on('body_end', function (array $page) use ($kris, $ids): string {
        [$ga4, $pixel, $events] = $ids();
        if (!$events || ($ga4 === '' && $pixel === '')) return '';
        // Lo script degli eventi non imposta cookie: passa i clic a gtag e fbq, che girano solo con il consenso
        return $kris->script('eventi.js', 'technical', ['defer' => true]);
    });
};
