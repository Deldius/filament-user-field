# Multi-User Stacks Design

## Summary

Extend the existing `UserEntry` and `UserColumn` components to support a single
user or multiple users without adding new public component classes. A resolved
multi-user state renders as a compact avatar stack. Consumers can opt into a
Filament modal that lists every resolved user using the existing full
`UserEntry` presentation.

The change must remain backward compatible for existing single-model and scalar
ID states.

## Public API

Collection mode is detected automatically. No `multiple()` or `stacked()` mode
switch is required.

```php
UserEntry::make('assignees')
    ->stackedLimit(8)
    ->stackedModal();

UserColumn::make('reviewers')
    ->stackedLimit(5);
```

The new methods are available on both components:

- `stackedLimit(int | Closure | null $limit): static` overrides the global
  visible-avatar limit. Passing `null` falls back to configuration.
- `stackedModal(bool | Closure $condition = true): static` enables or disables
  the user-list modal. It overrides the global setting.

The package configuration gains:

```php
'stacked' => [
    'limit' => 5,
    'modal' => false,
],
```

The default limit is five. The modal is disabled by default, making modal
interaction opt-in either globally or on an individual component.

## State Resolution

`HasState` will preserve its current single-state behavior and add a normalized
multi-state path:

1. An Eloquent model is returned unchanged.
2. A scalar ID is resolved through `user-field.user_model.class` and
   `user-field.user_model.fields.id`, using the existing five-second cache.
3. An array or Laravel/Eloquent collection is iterated in source order.
4. Each collection item may be an Eloquent model or scalar ID. Scalar IDs use
   the same configured-model resolver and cache as a single scalar state.
5. Nulls, unsupported values, and IDs that do not resolve are omitted.
6. The resolved collection preserves source order and duplicate values.
7. If no items resolve, the state is treated as empty and uses the existing
   empty-state presentation.

Model collections do not issue lookup queries. Scalar collections retain the
existing successful-lookup cache behavior. Documentation will continue to
recommend eager-loading relationships because each unique uncached scalar ID
may require a query.

Internal state helpers will distinguish a normalized multi-user state from a
single model so views and actions do not duplicate type-detection logic.

## Inline Rendering

Single-user states continue rendering the current avatar, heading, description,
and optional active-state indicator without visual or API changes.

Multi-user states render only an avatar stack:

- Show the first `stackedLimit()` resolved users.
- Render each configured avatar URL, or the existing default user icon when no
  avatar is available.
- Render a `+N` item when additional users are hidden by the limit.
- Preserve source order.
- Apply overlap and ring styling consistently to real and default avatars.
- Render as a non-interactive group unless the stacked modal is enabled.
- When modal interaction is enabled, render a keyboard-accessible button with
  an accessible label describing that it opens the user list.

Entry and column wrappers remain separate, but they share state and stacked-user
concerns plus reusable Blade partials to avoid duplicating avatar rendering.

## Modal Interaction

`stackedModal()` configures a Filament action on the existing component. The
action is only interactive when the resolved state is multi-user and non-empty.
For a single-user state, stacked options have no effect.

The modal is read-only and has no submit action. Its schema contains one
`UserEntry` per resolved user. Each generated entry receives that model as its
constant state and inherits the parent component's relevant display settings:

- avatar visibility and avatar callback;
- heading and description callbacks;
- size;
- active-state visibility and callback.

This preserves the normal full user-card presentation in the modal instead of
introducing a second card implementation. Empty-state settings are not copied to
individual generated rows because unresolved users have already been removed.

The existing Filament action precedence is retained. If a consumer configures
both a custom `action()` and `stackedModal()`, the option configured last owns
the component click behavior.

## Components and Responsibilities

### `HasState`

Resolves one model or a normalized collection of models. The scalar resolver is
shared by both paths so configured model fields and cache behavior remain
consistent.

### New stacked-user concern

Owns global/per-component stacked settings, collection detection, visible and
remaining user calculations, modal action creation, and creation of modal
`UserEntry` instances.

### Existing display concerns

Continue to own avatar, heading, description, size, and active-state settings.
They expose the raw configured values needed to transfer those settings to
generated modal entries without prematurely evaluating callbacks against the
parent collection.

### Blade and CSS

The current single-user markup remains intact. Shared avatar markup supports one
model at a time, while new collection branches in the entry and column views
render the avatar stack and `+N` indicator. CSS adds stack overlap, ring,
interactive focus, and count-indicator styles for every supported component
size.

## Error Handling and Compatibility

- Existing single-model and scalar-ID output is unchanged.
- Arrays and collections containing mixed valid models, scalar IDs, and invalid
  values render all successfully resolved users and skip the rest.
- Missing IDs do not fail the entire field.
- Empty arrays, empty collections, and fully unresolved collections use the
  existing empty state.
- An invalid non-collection object follows the existing unresolved-state path.
- `stackedLimit()` values below one show no user avatars and represent all users
  with the `+N` indicator; they do not discard state.
- Existing custom actions remain supported under Filament's last-configured
  action behavior.

## Testing

Automated tests will cover both `UserEntry` and `UserColumn` where behavior
differs, and shared concerns directly where appropriate:

- automatic array and Laravel/Eloquent collection detection;
- model-only, scalar-ID-only, and mixed collection resolution;
- source order and duplicate preservation;
- skipping null, unsupported, and missing values;
- empty-state behavior when no values resolve;
- successful scalar cache reuse;
- default, global, and per-component stacked limits;
- visible users and `+N` calculations;
- modal disabled by default and enabled globally/per component;
- modal generation of one configured `UserEntry` per resolved user;
- single-user behavior remaining unchanged;
- rendering of real avatars, fallback avatars, and accessible interaction;
- CSS build and the existing full test, analysis, and formatting checks.

## Documentation

Update the published configuration example and README with:

- relationship/model collection usage for `UserEntry` and `UserColumn`;
- scalar-ID collection behavior;
- `stackedLimit()` and `stackedModal()` examples;
- global stacked defaults;
- eager-loading guidance and the limits of per-ID caching.
