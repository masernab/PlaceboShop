# Fase 8 — Pulido

**Objetivo:** cerrar la experiencia: i18n completo, emails en ES, estados vacíos, responsive y detalles finales.

## Checklist

### i18n
- [x] Barrido de strings hardcodeados en todas las páginas/componentes → diccionarios `en.json`/`es.json` completos.
- [x] `php artisan lang:publish` + traducir `lang/es/validation.php`, `lang/es/auth.php`.
- [x] `lang/en/shop.php` + `lang/es/shop.php` para strings de servidor (email de confirmación).
- [x] Email de confirmación renderiza en el locale de la compra.

### UX
- [x] Estados vacíos con ilustración/CTA: carrito, wishlist, pedidos, resultados de búsqueda.
- [x] Página 404 amigable con link a la tienda.
- [x] Pase responsive completo (móvil: nav drawer, grids, checkout, tablas admin con scroll).
- [x] Disclaimer de transparencia visible en footer, checkout y email.
- [x] Accesibilidad básica: labels, focus states, alt en imágenes.

### Técnica
- [x] `.env.example` — notas de MAIL_MAILER=log y seeds.
- [x] README breve: qué es PlaceboShop, setup (`migrate:fresh --seed`, `php artisan dev`), credenciales demo, tarjetas de prueba.
- [x] `composer test` + `npm run lint && npm run types:check && npm run build` finales en verde.
- [x] Revisión final del flujo completo en EN y ES (checklist de verificación de 00-overview).
