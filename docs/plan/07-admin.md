# Fase 7 — Panel de administración

**Objetivo:** CRUD de productos (con imágenes), categorías y cupones + vista de pedidos, en páginas Inertia propias protegidas por el middleware `admin`.

## Checklist

### Backend
- [ ] `routes/admin.php` requerido desde web.php: `prefix('admin')->name('admin.')->middleware(['auth','verified','admin'])`.
- [ ] `Admin\DashboardController` — stats (productos, pedidos de hoy, usuarias) + últimos pedidos.
- [ ] `Admin\ProductController` — resource (index/create/store/edit/update/destroy) con requests validando campos bilingües (`name.en`, `name.es`, …), precio decimal → cents.
- [ ] `Admin\ProductImageController` — `store` (upload) y `destroy`. Disco custom `public_uploads` → `public/uploads/products` (sin symlink; añadir a `.gitignore`). `ProductImage::url` soporta ambos orígenes.
- [ ] `Admin\CategoryController` — resource sin show (formularios en dialogs).
- [ ] `Admin\CouponController` — resource sin show.
- [ ] `Admin\OrderController` — `index`, `show` (read-only, status computado).
- [ ] Resources Admin (JSON completo bilingüe para edición).
- [ ] Regenerar Wayfinder.

### Frontend
- [ ] `resources/js/layouts/admin-layout.tsx` — wrapper del `app-sidebar-layout.tsx` existente con nav: Dashboard, Products, Categories, Orders, Coupons + "Back to shop".
- [ ] `pages/admin/dashboard.tsx` — stat cards + tabla de últimos pedidos.
- [ ] `pages/admin/products/index.tsx` — tabla (thumb, nombre EN, categoría, precio, activo, acciones) + búsqueda.
- [ ] `pages/admin/products/form.tsx` — compartida create/edit: pares de campos EN/ES, select de categoría, precio, flags, sección de imágenes (existentes + upload + eliminar + reordenar por position).
- [ ] `pages/admin/categories/index.tsx` — tabla + dialog create/edit.
- [ ] `pages/admin/coupons/index.tsx` — tabla + dialog create/edit.
- [ ] `pages/admin/orders/index.tsx` y `show.tsx` — read-only con status badge.
- [ ] Link "Admin" en el menú de usuario del shop-layout solo si `is_admin`.

### Tests
- [ ] `tests/Feature/Admin/AdminAccessTest.php` — loop sobre todas las rutas admin: guest → login, no-admin → 403, admin → 200.
- [ ] `tests/Feature/Admin/ProductCrudTest.php` — CRUD con payloads bilingües, validación, upload con `Storage::fake`.
- [ ] `tests/Feature/Admin/CategoryCrudTest.php`, `CouponCrudTest.php`.

## Verificación
- Login admin@placeboshop.test → crear producto con imagen → visible en la tienda; no-admin recibe 403 en `/admin`.
