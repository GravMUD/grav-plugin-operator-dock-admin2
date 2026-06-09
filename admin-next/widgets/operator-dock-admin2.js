/**
 * Operator Dock — Admin2 dashboard launch pad widget.
 */
(function () {
  const TAG = window.__GRAV_WIDGET_TAG || 'grav-widget-operator-dock-launchpad';

  function apiConfig() {
    return {
      serverUrl: window.__GRAV_API_SERVER_URL || window.__GRAV_CONFIG__?.serverUrl || '',
      apiPrefix: window.__GRAV_API_PREFIX || window.__GRAV_CONFIG__?.apiPrefix || '/api/v1',
      token: window.__GRAV_API_TOKEN || null,
    };
  }

  async function apiGet(path) {
    const cfg = apiConfig();
    const base = `${cfg.serverUrl}${cfg.apiPrefix}`.replace(/\/+$/, '');
    const headers = { Accept: 'application/json' };
    if (cfg.token) headers['X-API-Token'] = cfg.token;
    const res = await fetch(`${base}${path.startsWith('/') ? path : `/${path}`}`, { headers, credentials: 'include' });
    const data = await res.json();
    if (!res.ok) throw new Error(data?.error || data?.message || `HTTP ${res.status}`);
    return data?.data ?? data;
  }

  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function iconClass(icon) {
    const raw = String(icon || 'fa-link').trim();
    if (raw.startsWith('fa-')) return `fa-solid ${raw}`;
    return raw;
  }

  class OperatorDockLaunchpad extends HTMLElement {
    static get observedAttributes() {
      return ['size', 'data-endpoint'];
    }

    connectedCallback() {
      this.renderSkeleton();
      this.load();
    }

    attributeChangedCallback() {
      if (this.isConnected) this.load();
    }

    renderSkeleton() {
      this.innerHTML = `
        <div class="flex h-full flex-col gap-3 rounded-lg border border-border bg-card p-4">
          <div class="flex items-center gap-2 text-sm font-medium text-foreground">
            <i class="fa-solid fa-rocket text-primary"></i>
            <span>Operator Launch Pad</span>
          </div>
          <div class="text-xs text-muted-foreground">Loading shortcuts…</div>
        </div>`;
    }

    async load() {
      const endpoint = this.getAttribute('data-endpoint') || '/operator-dock/launchpad';
      try {
        const data = await apiGet(endpoint);
        this.render(data);
      } catch (err) {
        this.innerHTML = `
          <div class="rounded-lg border border-amber-500/30 bg-card p-4 text-sm text-amber-600">
            Launch pad failed: ${esc(err instanceof Error ? err.message : String(err))}
          </div>`;
      }
    }

    render(data) {
      const size = this.getAttribute('size') || 'md';
      const cols = size === 'sm' ? 2 : size === 'lg' || size === 'xl' ? 4 : 3;
      const tiles = Array.isArray(data?.tiles) ? data.tiles : [];
      const vitals = data?.vitals || null;

      const tileHtml = tiles
        .map((tile) => {
          const external = tile.external ? ' target="_blank" rel="noopener"' : '';
          return `
            <a href="${esc(tile.url)}"${external}
              class="group flex items-center gap-2 rounded-md border border-border bg-background/60 px-3 py-2 text-sm transition-colors hover:border-primary/40 hover:bg-accent/40">
              <i class="${esc(iconClass(tile.icon))} text-primary/80"></i>
              <span class="truncate text-foreground group-hover:text-primary">${esc(tile.label)}</span>
            </a>`;
        })
        .join('');

      const vitalsHtml = vitals
        ? `
          <div class="mt-1 grid grid-cols-2 gap-2 rounded-md bg-muted/30 p-2 text-[0.7rem] text-muted-foreground md:grid-cols-4">
            <div><span class="block uppercase tracking-wide opacity-70">Grav</span><strong class="text-foreground">${esc(vitals.grav_version)}</strong></div>
            <div><span class="block uppercase tracking-wide opacity-70">Theme</span><strong class="text-foreground">${esc(vitals.theme)}</strong></div>
            <div><span class="block uppercase tracking-wide opacity-70">Pages</span><strong class="text-foreground">${esc(vitals.page_count)}</strong></div>
            <div class="col-span-2 md:col-span-1 truncate"><span class="block uppercase tracking-wide opacity-70">Site</span><strong class="text-foreground">${esc(vitals.site_url)}</strong></div>
          </div>`
        : '';

      this.innerHTML = `
        <div class="flex h-full flex-col gap-3 rounded-lg border border-border bg-card p-4">
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-sm font-medium text-foreground">
              <i class="fa-solid fa-rocket text-primary"></i>
              <span>Operator Launch Pad</span>
            </div>
            <button type="button" class="text-xs text-muted-foreground hover:text-foreground" data-refresh>Refresh</button>
          </div>
          <div class="grid gap-2" style="grid-template-columns: repeat(${cols}, minmax(0, 1fr));">
            ${tileHtml || '<p class="text-xs text-muted-foreground">No shortcuts configured.</p>'}
          </div>
          ${vitalsHtml}
        </div>`;

      this.querySelector('[data-refresh]')?.addEventListener('click', () => this.load());
    }
  }

  if (!customElements.get(TAG)) {
    customElements.define(TAG, OperatorDockLaunchpad);
  }
})();
