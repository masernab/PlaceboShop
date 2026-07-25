# Fase 1 — Fundaciones (i18n, admin flag, shop layout)

**Objetivo:** infraestructura transversal que todas las demás fases necesitan: flag de admin, sistema de idiomas EN/ES, y el layout de la tienda.

## Checklist

### Backend
- [x] Migración `add_is_admin_to_users_table` (`is_admin` boolean default false) + cast `'is_admin' => 'boolean'` en `app/Models/User.php`.
- [x] Middleware `app/Http/Middleware/EnsureUserIsAdmin.php` — abort 403 salvo `$request->user()?->is_admin`. Alias `admin` en `bootstrap/app.php`.
- [x] Middleware `app/Http/Middleware/SetLocale.php` — `app()->setLocale(session('locale', config('app.locale')))`; append al grupo web en `bootstrap/app.php` (antes de `HandleInertiaRequests`).
- [x] Config de locales disponibles: `['en','es']` (en `config/app.php` o `config/locales.php`).
- [x] `app/Http/Controllers/LocaleController.php` + ruta `PUT /locale` (name `locale.update`): valida `in:en,es`, guarda en sesión, `back()`.
- [x] Props compartidas en `app/Http/Middleware/HandleInertiaRequests.php`: añadir `'locale' => app()->getLocale()`. Verificar que `auth.user` exponga `is_admin`.

### Frontend
- [x] `resources/js/lang/en.ts` y `resources/js/lang/es.ts` — diccionarios esqueleto (nav, footer, común). Se usó `.ts` en vez de `.json`: TypeScript verifica que ES tenga todas las claves de EN (`Record<TranslationKey, string>`).
- [x] `resources/js/hooks/use-translation.ts` — lee `locale` de `usePage().props`, exporta `t(key, params?)` con interpolación `:param` y fallback a EN.
- [x] Extender tipos: `locale` en `resources/js/types/global.d.ts` (sharedPageProps) y `is_admin` en `resources/js/types/auth.ts` (User).
- [x] `resources/js/components/shop/language-switcher.tsx` — `router.put` a `locale.update` con `preserveScroll`.
- [x] `resources/js/layouts/shop-layout.tsx` — header (logo, nav, búsqueda placeholder, switcher de idioma, menú usuario/login), footer con disclaimer de transparencia ("Nothing ships. Nothing is charged. Pure retail joy." / ES). Drawer móvil con `sheet`. El badge del carrito llega en Fase 3.

### Tests
- [x] `tests/Feature/Shop/LocaleTest.php` — `PUT /locale` persiste en sesión; locale inválido rechazado; prop compartida `locale` correcta.
- [x] `tests/Feature/Admin/AdminAccessTest.php` — base del test de acceso admin (guest → login, no-admin → 403, admin → ok) usando una ruta admin dummy o preparado para Fase 7.

## Verificación
- `composer test` y `npm run lint && npm run types:check && npm run build` en verde.
- En el navegador: cambiar idioma y ver la UI del layout cambiar EN↔ES; persiste al recargar.
