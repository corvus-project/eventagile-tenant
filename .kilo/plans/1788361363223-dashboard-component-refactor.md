# Dashboard Refactor Plan

## Summary
The frontend was refactored to use `x-ui.*` component namespace (e.g., `x-ui.input`, `x-ui.button`), but old non-prefixed component components and their underlying sub-components were deleted. Many dashboard pages still reference the old namespace, broken components, or non-existent Blade components. This plan fixes all broken components.

## Key Decisions
- **Strategy**: Add **aliases** for old component names pointing to `ui/` equivalents, rather than rewriting all views. This is minimal-impact and covers both dashboard pages and component-in-component references (e.g. `ui/event-form.blade.php` uses `x-input`).
- **New missing components**: Create `x-chart`, `x-form`, `x-card`, `x-select`, `x-textarea`, `x-datetime` using `ui/` equivalents or new class-based components.
- **Old `x-modal`**: Keep as alias to `x-ui.modal`.

## Affected Files / Component Map

| Old Reference (broken) | Resolution | Notes |
|---|---|---|
| `<x-chart>` | New component `app/View/Components/Chart.php` + `resources/views/components/chart.blade.php` | **DONE** — creates `<canvas>` + Chart.js init |
| `<x-form>` | New component `resources/views/components/form.blade.php` | Wrapper `<div>` with `@slot('actions')` for buttons |
| `<x-input>` | Alias → `x-ui.input` | Already exists in `ui/input.blade.php` |
| `<x-textarea>` | Create `resources/views/components/textarea.blade.php` | Mirror `ui/input.blade.php` with `<textarea>` |
| `<x-select>` | Create `resources/views/components/select.blade.php` | `<select>` with options support |
| `<x-datetime>` | Create `resources/views/components/datetime.blade.php` | Mirror `ui/input` with `type="datetime-local"` |
| `<x-checkbox>` | Alias → `x-ui.checkbox` | Already exists in `ui/checkbox.blade.php` |
| `<x-modal>` | Alias → `x-ui.modal` | Already exists in `ui/modal.blade.php` |
| `<x-button>` | Alias → `x-ui.button` | Already exists in `ui/button.blade.php` |
| `<x-card>` | Create `resources/views/components/card.blade.php` | Wrapper with `shadow` prop |
| `<x-nav-link>` / `<x-icon>` | Check if used | See "Other Issues" below |

## Component Alias Strategy

Add aliases in `app/Providers/AppServiceProvider.php` `boot()` method:

```php
Blade::component('ui.button', 'button');
Blade::component('ui.input', 'input');
Blade::component('ui.checkbox', 'checkbox');
Blade::component('ui.modal', 'modal');
```

This makes `<x-button>` resolve to `components/ui/button.blade.php`, `<x-input>` → `components/ui/input.blade.php`, etc.

## Pages Affected

### 1. `dashboard/registration.blade.php`
- `<x-modal>` → alias (or `<x-ui.modal>`)
- `<x-form>` → create new component
- `<x-select>` → create new component
- `<x-button>` → alias (or `<x-ui.button>`)

### 2. `dashboard/settings.blade.php`
- `<x-form>` → create new component
- `<x-input>` → alias (or `<x-ui.input>`)
- `<x-textarea>` → create new component
- `<x-button>` → alias (or `<x-ui.button>`)

### 3. `dashboard/events.blade.php`
- `<x-button>` → alias (or `<x-ui.button>`)

### 4. `dashboard/users.blade.php`
- `<x-ui.input>` ✓ fine
- `<x-button>` (line 167) → alias

### 5. `dashboard/users/[User]/registrations.blade.php`
- `<x-card>` → create new component

### 6. `dashboard/events/[Event]/registrations.blade.php`
- `<x-card>` → create new component

### 7. `dashboard/events/[Event]/export.blade.php`
- `<x-card>` → create new component

### 8. `dashboard/events/[Event].blade.php`
- `<x-ui.text-link>` ✓ fine

### 9. `dashboard/profile/edit.blade.php`
- `<x-ui.input>` ✓ fine
- `<x-ui.button>` ✓ fine
- `<x-ui.modal>` ✓ fine

### 10. `dashboard/reports.blade.php`
- No broken components ✓ fine

### 11. `dashboard/events/create.blade.php` + `dashboard/events/[Event]/update.blade.php`
- `<x-ui.event-form>` ✓ fine (but `event-form` itself is broken — see below)

## Sub-component Fix: `ui/event-form.blade.php`
This shared component uses broken references:
- `<x-form>` → new component
- `<x-input>` → alias
- `<x-textarea>` → new component
- `<x-datetime>` → new component
- `<x-select>` → new component
- `<x-checkbox>` → alias
- `<x-button>` → alias

## Implementation Order
1. Add Blade component aliases in `AppServiceProvider`
2. Create `x-form` component (wrapper div + actions slot)
3. Create `x-textarea` component (mirror `ui/input`)
4. Create `x-select` component (select with options prop)
5. Create `x-datetime` component (mirror `ui/input` with datetime type)
6. Create `x-card` component (wrapper div with shadow prop)
7. `x-chart` already created (verify it renders correctly)
8. Run `php artisan test` + lint checks

## Risks / Failure Modes
- `x-form` with `@slot:actions` may conflict with TailwindCSS form plugin. Need to verify slot name doesn't collide.
- The `ui/button.blade.php` component uses `{{ $slot }}` for label, but some usages pass `label="..."` prop. Verify the button handles both `<x-button label="Save">` and `<x-button>Save</x-button>`.
- The `ui/button` uses `icon` prop in some calls — it's not defined in `@props`. Verify if icon is handled elsewhere (e.g., via `x-icon` or slot).
- Chart.js requires unique canvas IDs on a page — must ensure `uniqid()` doesn't collide.

## Open Questions (for user)
1. Are `<x-button>` calls with `icon="o-pencil"` (e.g. `events.blade.php:190`) expected to render icons? The current `ui/button.blade.php` `@props` doesn't declare `icon`. Should we create `<x-icon>` component support, or strip the icon param?
2. Should `x-card` accept additional props (padding, border)?
3. After aliasing, is the plan to later migrate all `x-*` usages to `x-ui.*` directly, or keep aliases permanent?

## Validation
- `php artisan test` — all existing tests must pass
- Manual: render each dashboard page and verify no "Unable to locate a class or view for component" errors
