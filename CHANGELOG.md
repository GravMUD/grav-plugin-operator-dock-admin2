# v1.1.3
## 06/25/2026

1. [](#removed)
    * **Clear Cache:** Removed duplicate menubar button — Admin2 core now ships selective cache clearing

# v1.1.2
## 06/05/2026

1. [](#improved)
    * **Menubar:** `TeamDcMenubarCoordinator` dedupes JavaBean + Operator Dock + Mambo links into `effective.menubarLinks` at preferences resolve time
    * **Menubar:** URL shortcuts no longer register as broken action buttons
    * **Menubar:** No writes to `user/config/admin-next.yaml`

# v1.1.1
## 06/04/2026

1. [](#bugfix)
    * **Fix:** Grav 2 plugin bootstrap — explicit `return new GravOperatorDockAdmin2Plugin($name, $grav)` for Andy slug `operator-dock-admin2`
    * **Fix:** Load `OperatorDockLegacy` before `isEnabled()` in early init (prevents fatal on cache clear)

# v1.1.0
## 06/01/2026

1. [](#breaking)
    * Plugin slug **`operator-dock-admin2`** (was `grav-operator-dock-admin2`) — repo `grav-plugin-operator-dock-admin2`
    * Admin route `/plugin/operator-dock-admin2`; API routes stay `/operator-dock/*`
    * Auto-migrates plugin config yaml and `system.yaml` on first load

# v1.0.1
## 06/01/2026

1. [](#improved)
    * Header shortcuts register at runtime via `onApiMenubarItems` — no longer writes `user/config/admin-next.yaml`
    * `inject_header_links` defaults to off (opt-in)
    * Removed `config->reload()` on every admin request from early plugin init

# v1.0.0
## 05/31/2026

1. [](#new)
    * Initial release — header shortcuts, Launch Pad widget, Clear Cache menubar action
