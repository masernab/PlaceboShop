# Fase 8 — Pulido

**Objetivo:** cerrar la experiencia: i18n completo, emails en ES, estados vacíos, responsive y detalles finales.

## Checklist

### i18n
- [ ] Barrido de strings hardcodeados en todas las páginas/componentes → diccionarios `en.json`/`es.json` completos.
- [ ] `php artisan lang:publish` + traducir `lang/es/validation.php`, `lang/es/auth.php`.
- [ ] `lang/en/shop.php` + `lang/es/shop.php` para strings de servidor (email de confirmación).
- [ ] Email de confirmación renderiza en el locale de la compra.

### UX
- [ ] Estados vacíos con ilustración/CTA: carrito, wishlist, pedidos, resultados de búsqueda.
- [ ] Página 404 amigable con link a la tienda.
- [ ] Pase responsive completo (móvil: nav drawer, grids, checkout, tablas admin con scroll).
- [ ] Disclaimer de transparencia visible en footer, checkout y email.
- [ ] Accesibilidad básica: labels, focus states, alt en imágenes.

### Técnica
- [ ] `.env.example` — notas de MAIL_MAILER=log y seeds.
- [ ] README breve: qué es PlaceboShop, setup (`migrate:fresh --seed`, `php artisan dev`), credenciales demo, tarjetas de prueba.
- [ ] `composer test` + `npm run lint && npm run types:check && npm run build` finales en verde.
- [ ] Revisión final del flujo completo en EN y ES (checklist de verificación de 00-overview).
