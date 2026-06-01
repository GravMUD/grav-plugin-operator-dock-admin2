# Operator Dock for Admin2

**Repo:** [GravMUD/grav-plugin-grav-operator-dock-admin2](https://github.com/GravMUD/grav-plugin-grav-operator-dock-admin2)

**Free Admin2 operator utilities for Grav 2.0** — header shortcuts, dashboard Launch Pad widget, and a one-click Clear Cache toolbar action.

> *JavaBean paints the cockpit. Operator Dock wires the controls.*

Pairs with [JavaBean for Admin2](https://github.com/GravMUD/grav-plugin-grav-javabean-admin2) (MIT theming). Commercial GravMUD plugins (EvvyTink, Commentz, Forumz, etc.) are separate products.

**License:** MIT — free forever.

## Requirements

| Package | Version |
|---------|---------|
| [Grav](https://github.com/getgrav/grav) | `>=2.0.0` |
| [Admin2](https://github.com/getgrav/grav-plugin-admin2) | `>=1.0.0` |
| [API](https://github.com/getgrav/grav-plugin-api) | `>=1.0.0` |

## Installation

### GPM (once listed)

```bash
bin/gpm install grav-operator-dock-admin2
```

### Manual

1. Download the latest release zip.
2. Extract to `user/plugins/grav-operator-dock-admin2`.
3. Clear cache: `bin/grav cache` or Admin2 → Clear Cache.
4. Enable **Operator Dock for Admin2** in Admin2 → Plugins.

## Features

| Feature | Description |
|---------|-------------|
| **Header shortcuts** | Merges configured links into Admin2 `menubarLinks` (top bar icons) |
| **Launch Pad widget** | Dashboard widget with quick tiles + optional site vitals |
| **Clear Cache button** | Toolbar action (requires `api.system.write`) |
| **Settings** | Admin2 → Settings → Operator Dock, or sidebar **Operator Dock** page |

### Default shortcuts

- View Site (front-end)
- Grav Learn
- Optional Team DC pack: GetGRAV!, Mud Bazaar
- Custom links (label, URL, FA icon, external toggle)
- Launch Pad also links to Pages, Plugins, Themes, Tools, and Settings inside Admin2

### Site / docs

- **GitHub Pages:** https://operator-dock.gravmud.site
- **Pairs with:** [JavaBean for Admin2](https://javabean.gravmud.site)

### Andy compatibility

Uses official Admin2 extension points only:

- `onApiMenubarItems` / `onApiMenubarAction` (header shortcuts + clear cache)
- `onApiDashboardWidgets` + `admin-next/widgets/{slug}.js`
- `onApiAdminSettingsPanels` + blueprint settings

Header shortcuts are registered at runtime via the menubar API. The plugin does **not** write `user/config/admin-next.yaml`. Disable the plugin and the shortcuts disappear automatically.

If an older build injected links into `admin-next.yaml`, remove stale entries manually under **Settings → Appearance → Menubar links**.

## Development

```powershell
# From GRAV-MUD monorepo
.\scripts\build-operator-dock-gpm.ps1
```

Plugin path: `user/plugins/grav-operator-dock-admin2/`

## GPM submission

This plugin is intended for the [Grav Package Manager](https://learn.getgrav.org/advanced/grav-development#theme-plugin-release-process):

1. MIT `LICENSE`
2. This `README.md`
3. `blueprints.yaml` with metadata and dependencies
4. `CHANGELOG.md` in Grav changelog format
5. GitHub release tag (semver, e.g. `1.0.0`)
6. Issue on [getgrav/grav](https://github.com/getgrav/grav/issues) — see `GPM-SUBMISSION.md` for copy-paste body

## Author

**FutureVision Labs · Team DC**  
Damian Caynes — [gravmud.site](https://gravmud.site)
