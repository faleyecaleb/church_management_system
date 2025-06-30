# Order of Service Route Reference

## Understanding Shallow Routes

When using `Route::resource('services.order-of-services', OrderOfServiceController::class)->shallow()`, Laravel creates the following routes:

### Non-Shallow Routes (require service parameter)
- **Index**: `services.order-of-services.index` → `/services/{service}/order-of-services`
- **Create**: `services.order-of-services.create` → `/services/{service}/order-of-services/create`
- **Store**: `services.order-of-services.store` → `POST /services/{service}/order-of-services`

### Shallow Routes (don't require service parameter)
- **Show**: `order-of-services.show` → `/order-of-services/{order_of_service}`
- **Edit**: `order-of-services.edit` → `/order-of-services/{order_of_service}/edit`
- **Update**: `order-of-services.update` → `PUT/PATCH /order-of-services/{order_of_service}`
- **Destroy**: `order-of-services.destroy` → `DELETE /order-of-services/{order_of_service}`

### Additional Custom Routes
- **Overview**: `order-of-services.overview` → `/order-of-services`
- **Reorder**: `services.order-of-services.reorder` → `POST /services/{service}/order-of-services/reorder`
- **Duplicate**: `services.order-of-services.duplicate` → `POST /services/{service}/order-of-services/duplicate`
- **Print**: `services.order-of-services.print` → `/services/{service}/order-of-services/print`

## Correct Usage in Views

### ✅ Correct Route Usage

```php
<!-- Index (needs service) -->
{{ route('services.order-of-services.index', $service->id) }}

<!-- Create (needs service) -->
{{ route('services.order-of-services.create', $service->id) }}

<!-- Edit (shallow - only needs item) -->
{{ route('order-of-services.edit', $item->id) }}

<!-- Update (shallow - only needs item) -->
{{ route('order-of-services.update', $item->id) }}

<!-- Delete (shallow - only needs item) -->
{{ route('order-of-services.destroy', $item->id) }}

<!-- Overview (no parameters) -->
{{ route('order-of-services.overview') }}
```

### ❌ Incorrect Route Usage

```php
<!-- These will cause "Route not defined" errors -->
{{ route('services.order-of-services.edit', [$service->id, $item->id]) }}
{{ route('services.order-of-services.destroy', [$service->id, $item->id]) }}
{{ route('services.order-of-services.update', [$service->id, $item->id]) }}
```

## Navigation Flow

1. **Overview** → `order-of-services.overview`
2. **Manage Order** → `services.order-of-services.index` (with service)
3. **Add Item** → `services.order-of-services.create` (with service)
4. **Edit Item** → `order-of-services.edit` (with item only)
5. **Delete Item** → `order-of-services.destroy` (with item only)