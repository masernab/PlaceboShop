# Fase 9 — Subcategorías

**Objetivo:** convertir `categories` en un árbol de dos niveles (categoría → subcategoría) gestionable desde el admin y navegable en la tienda.

## Decisiones

- **Exactamente dos niveles.** Una subcategoría no puede tener hijas; se valida en `CategoryRequest`, sin columna `depth`.
- **Los productos viven en cualquier nodo**, padre o hija. Los 42 productos sembrados se quedan en las raíces; no hay migración de datos.
- **Filtrar por un padre incluye sus subcategorías**, y el contador del sidebar acumula lo mismo que devuelve el filtro.
- **Slugs globalmente únicos y sin prefijo del padre**: `?category=slug` no tiene contexto de padre, y prefijar produciría URLs obsoletas al mover una subcategoría.

## Checklist

### Backend
- [x] Migración `2026_07_26_000001_add_parent_id_to_categories_table` — `parent_id` nullable autorreferencial + índice `(parent_id, position)`. `down()` en dos `Schema::table` porque SQLite no borra una columna con FK o índice.
- [x] `Category`: relaciones `parent()`, `children()` (con orden incorporado) y `childProducts()` (HasManyThrough para el rollup de contadores sin N+1); scope `roots()`; `parent_id` en `#[Fillable]`.
- [x] `Product::inCategory()` reescrito para incluir descendientes. **El closure de agrupación es obligatorio**: un `orWhereHas` suelto escapa de la correlación de la relación y devuelve el catálogo entero.
- [x] `CategoryRequest`: `parent_id` valida existencia, que el padre sea raíz, que no sea sí misma (update) y que una categoría con hijas no pueda volverse hija. `categoryAttributes()` emite `parent_id` siempre, `null` incluido, para poder promover a raíz.
- [x] `Admin\CategoryController`: índice en árbol (`roots()` + `with('children')`), guard de hijas en `destroy()`.
- [x] Resources: `parent_id` + `children` en el de admin; `parent` + `children` + contador acumulado en el de tienda. Forma de dos argumentos de `whenLoaded` (el resource de admin también lo usa el formulario de productos, que no carga hijas).
- [x] `Shop\ProductController` (raíces + `withCount(['products','childProducts'])` + `category.parent` en `show`), `Shop\HomeController` (solo raíces).
- [x] `CategorySeeder`: 3 subcategorías bilingües por cada una de las 6 raíces, idempotente. `ProductSeeder` sin cambios.

### Frontend
- [x] Tipos `AdminCategoryData` (`parent_id`, `children`) y `CategoryData` (`parent`, `children`).
- [x] `admin/categories/index.tsx`: select de padre en el diálogo (centinela `NO_PARENT` porque Radix prohíbe `value=""`), tabla en árbol con `CategoryRow` indentado.
- [x] `admin/product-form.tsx`: select agrupado con `SelectGroup`, hijas indentadas.
- [x] `shop/products/index.tsx`: sidebar anidado siempre expandido (sin estado local: el reload parcial usa `only: ['products','filters']`, así que `categories` no se refresca).
- [x] `shop/products/show.tsx`: migas `Padre › Hija`.

### Tests
- [x] `CategoryCrudTest` (12): alta bajo padre, padre debe ser raíz, no autopadre, padre con hijas no puede degradarse, promoción a raíz, borrado bloqueado con hijas, índice en árbol.
- [x] `CatalogTest` (17): filtro por padre incluye descendientes, filtro por hija excluye el resto de la rama, anidamiento del sidebar, contador acumulado, migas con y sin padre.

## Verificación

```
php artisan migrate                 # copiar database.sqlite antes: la FK fuerza rebuild de tabla
php artisan db:seed --class=CategorySeeder
composer test                       # pint + phpstan nivel 7 + 134 tests
npm run types:check && npm run lint:check && npm run build
```
