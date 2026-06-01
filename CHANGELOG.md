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
## 05/28/2026

1. [](#new)
    * Initial public release — Operator Dock for Admin2
    * Configurable Admin2 header shortcuts (runtime menubar API)
    * Operator Launch Pad dashboard widget with site vitals
    * Launch Pad tiles: Pages, Plugins, Themes, Tools, Settings (+ header shortcuts)
    * One-click Clear Cache menubar action
    * Settings panel + sidebar plugin page
    * Optional Team DC shortcut pack (GetGRAV!, Mud Bazaar)
