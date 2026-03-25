import './bootstrap';

/** @returns {string} 64 hex chars (32 bytes) */
function fleetSecureOperatorToken() {
    const bytes = new Uint8Array(32);
    crypto.getRandomValues(bytes);

    return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
}

/**
 * @param {string} text
 * @returns {Promise<boolean>}
 */
async function fleetClipboardWrite(text) {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);

            return true;
        }
    } catch {
        // fall through
    }
    try {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        const ok = document.execCommand('copy');
        document.body.removeChild(ta);

        return ok;
    } catch {
        return false;
    }
}

/**
 * @param {HTMLElement | null | undefined} el
 * @param {string} msg
 * @param {'ok' | 'err'} kind
 */
function fleetTokenUiStatus(el, msg, kind) {
    if (!el) {
        return;
    }
    el.textContent = msg;
    el.className =
        kind === 'err'
            ? 'mt-2 min-h-[1.25rem] text-xs text-red-400/90'
            : 'mt-2 min-h-[1.25rem] text-xs text-emerald-400/90';
}

function fleetHeaders() {
    const t = document.querySelector('meta[name="csrf-token"]');

    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': t?.getAttribute('content') ?? '',
    };
}

function fleetSparklineStrip(bits) {
    if (!Array.isArray(bits) || bits.length === 0) {
        const p = document.createElement('p');
        p.className = 'text-xs text-zinc-500';
        p.textContent = 'No samples in this window.';
        return p;
    }

    const wrap = document.createElement('div');
    wrap.className =
        'flex h-6 max-w-full items-end gap-px overflow-hidden rounded-md bg-zinc-950/50 p-1 ring-1 ring-zinc-800/60';
    wrap.title = 'Green = OK, red = error.';

    for (const bit of bits) {
        const span = document.createElement('span');
        span.className = `min-h-[4px] min-w-[2px] flex-1 rounded-sm ${bit ? 'bg-emerald-500/85' : 'bg-red-500/85'}`;
        span.style.maxWidth = '4px';
        wrap.appendChild(span);
    }

    return wrap;
}

function fleetDetailSectionTitle(text) {
    const el = document.createElement('h3');
    el.className = 'mb-2 text-[11px] font-bold uppercase tracking-[0.15em] text-zinc-500';
    el.textContent = text;

    return el;
}

function formatFleetDetailTime(iso) {
    try {
        const d = new Date(iso);

        return Number.isNaN(d.getTime()) ? iso : d.toLocaleString();
    } catch {
        return iso;
    }
}

function renderFleetDetail(data) {
    const body = document.getElementById('fleet-detail-body');
    if (!body) {
        return;
    }

    body.replaceChildren();

    const sloWrap = document.createElement('div');
    sloWrap.className = 'mb-6 grid gap-4 sm:grid-cols-2';
    const sloText = document.createElement('p');
    sloText.className = 'font-mono text-xs leading-relaxed text-zinc-400 sm:col-span-2';
    const p24 = data.slo_24h != null ? `${data.slo_24h}% OK · 24h` : '24h: no samples';
    const p7 = data.slo_7d != null ? `${data.slo_7d}% OK · 7d` : '7d: no samples';
    sloText.textContent = `${p24}   ·   ${p7}`;
    sloWrap.appendChild(sloText);

    const col24 = document.createElement('div');
    col24.appendChild(fleetDetailSectionTitle('Strip · 24h'));
    col24.appendChild(fleetSparklineStrip(data.sparkline_24h));

    const col7 = document.createElement('div');
    col7.appendChild(
        fleetDetailSectionTitle(data.sparkline_7d_rollups ? 'Strip · 7d (daily buckets)' : 'Strip · 7d'),
    );
    col7.appendChild(fleetSparklineStrip(data.sparkline_7d));

    sloWrap.appendChild(col24);
    sloWrap.appendChild(col7);
    body.appendChild(sloWrap);

    body.appendChild(fleetDetailSectionTitle('Last successful summary (stored JSON)'));
    const pre = document.createElement('pre');
    pre.className =
        'mb-6 max-h-64 overflow-auto rounded-lg bg-zinc-900/80 p-3 font-mono text-[11px] leading-relaxed text-zinc-300 ring-1 ring-zinc-800/80 outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/35';
    pre.setAttribute('tabindex', '0');
    pre.setAttribute(
        'aria-label',
        data.last_ok_summary != null ? 'Last successful operator summary JSON' : 'No stored summary message',
    );
    pre.textContent =
        data.last_ok_summary != null ? JSON.stringify(data.last_ok_summary, null, 2) : 'No successful poll with a stored summary yet.';
    body.appendChild(pre);

    body.appendChild(fleetDetailSectionTitle('Recent errors'));
    if (!data.recent_errors?.length) {
        const none = document.createElement('p');
        none.className = 'mb-6 text-xs text-zinc-500';
        none.textContent = 'No failed polls recorded.';
        body.appendChild(none);
    } else {
        const ul = document.createElement('ul');
        ul.className = 'mb-6 space-y-2 text-xs';
        for (const err of data.recent_errors) {
            const li = document.createElement('li');
            li.className = 'rounded-lg bg-red-950/30 p-2.5 text-red-100/90 ring-1 ring-red-500/15';
            const meta = document.createElement('div');
            meta.className = 'mb-1 font-mono text-[10px] text-red-300/80';
            const st = err.http_status != null ? `HTTP ${err.http_status} · ` : '';
            meta.textContent = `${st}${formatFleetDetailTime(err.at)}`;
            const msg = document.createElement('div');
            msg.className = 'whitespace-pre-wrap break-words text-red-100/95';
            msg.textContent = err.message || '—';
            li.appendChild(meta);
            li.appendChild(msg);
            ul.appendChild(li);
        }
        body.appendChild(ul);
    }

    body.appendChild(fleetDetailSectionTitle('Recent polls'));
    const table = document.createElement('table');
    table.className = 'w-full border-collapse text-left font-mono text-[11px] text-zinc-400';
    const thead = document.createElement('thead');
    thead.innerHTML =
        '<tr class="border-b border-zinc-800 text-[10px] uppercase tracking-wider text-zinc-500"><th scope="col" class="py-2 pr-3 font-medium">Time</th><th scope="col" class="py-2 pr-3 font-medium">OK</th><th scope="col" class="py-2 pr-3 font-medium">ms</th><th scope="col" class="py-2 font-medium">Status</th></tr>';
    table.appendChild(thead);
    const tbody = document.createElement('tbody');
    if (!data.recent_polls?.length) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 4;
        td.className = 'py-3 text-zinc-500';
        td.textContent = 'No polls stored yet.';
        tr.appendChild(td);
        tbody.appendChild(tr);
    } else {
        for (const row of data.recent_polls) {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-zinc-800/60';
            const t1 = document.createElement('td');
            t1.className = 'py-1.5 pr-3 align-top text-zinc-500';
            t1.textContent = formatFleetDetailTime(row.at);
            const t2 = document.createElement('td');
            t2.className = 'py-1.5 pr-3 align-top';
            t2.textContent = row.ok ? 'yes' : 'no';
            const t3 = document.createElement('td');
            t3.className = 'py-1.5 pr-3 align-top';
            t3.textContent = row.latency_ms != null ? String(row.latency_ms) : '—';
            const t4 = document.createElement('td');
            t4.className = 'py-1.5 align-top';
            const statusParts = [];
            if (row.http_status != null) {
                statusParts.push(String(row.http_status));
            }
            if (row.error_excerpt) {
                statusParts.push(row.error_excerpt);
            }
            t4.textContent = statusParts.length ? statusParts.join(' · ') : '—';
            tr.appendChild(t1);
            tr.appendChild(t2);
            tr.appendChild(t3);
            tr.appendChild(t4);
            tbody.appendChild(tr);
        }
    }
    table.appendChild(tbody);
    body.appendChild(table);
}

const FLEET_FOCUSABLE_SELECTOR =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/** @type {HTMLElement | null} */
let fleetDetailTriggerEl = null;

/** @type {((e: KeyboardEvent) => void) | null} */
let fleetDetailTabListener = null;

let fleetDetailBodyOverflow = '';

function fleetGetConsoleShell() {
    return document.getElementById('fleet-console-shell');
}

function getFleetDetailModal() {
    return document.getElementById('fleet-detail-modal');
}

/**
 * @returns {HTMLElement[]}
 */
function fleetGetFocusableInPanel() {
    const panel = document.getElementById('fleet-detail-panel');
    if (!panel) {
        return [];
    }

    return Array.from(panel.querySelectorAll(FLEET_FOCUSABLE_SELECTOR)).filter((el) => {
        if (!(el instanceof HTMLElement)) {
            return false;
        }
        if (el.hasAttribute('disabled')) {
            return false;
        }
        if (el.closest('[hidden], .hidden')) {
            return false;
        }

        return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
    });
}

/**
 * @param {KeyboardEvent} e
 */
function fleetDetailTabTrap(e) {
    if (e.key !== 'Tab') {
        return;
    }
    const list = fleetGetFocusableInPanel();
    if (list.length === 0) {
        return;
    }

    const first = list[0];
    const last = list[list.length - 1];

    if (e.shiftKey) {
        if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
        }
    } else if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

function fleetDetailSetBusy(busy) {
    const modal = getFleetDetailModal();
    if (modal) {
        modal.setAttribute('aria-busy', busy ? 'true' : 'false');
    }
}

function fleetDetailSetStatus(text) {
    const el = document.getElementById('fleet-detail-status');
    if (el) {
        el.textContent = text;
    }
}

/**
 * @param {HTMLElement | null} [triggerEl]
 */
function openFleetDetailModal(triggerEl) {
    const modal = getFleetDetailModal();
    if (!modal) {
        return;
    }

    fleetDetailTriggerEl = triggerEl ?? null;
    fleetDetailBodyOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const shell = fleetGetConsoleShell();
    if (shell) {
        shell.inert = true;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    fleetDetailSetBusy(true);

    if (!fleetDetailTabListener) {
        fleetDetailTabListener = (e) => fleetDetailTabTrap(e);
    }
    modal.addEventListener('keydown', fleetDetailTabListener);

    requestAnimationFrame(() => {
        document.getElementById('fleet-detail-close-btn')?.focus();
    });
}

function closeFleetDetailModal() {
    const modal = getFleetDetailModal();
    if (modal && fleetDetailTabListener) {
        modal.removeEventListener('keydown', fleetDetailTabListener);
    }

    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    }

    fleetDetailSetBusy(false);
    fleetDetailSetStatus('');

    document.body.style.overflow = fleetDetailBodyOverflow;

    const shell = fleetGetConsoleShell();
    if (shell) {
        shell.inert = false;
    }

    const errEl = document.getElementById('fleet-detail-error');
    if (errEl) {
        errEl.classList.add('hidden');
        errEl.textContent = '';
    }

    const toFocus = fleetDetailTriggerEl;
    fleetDetailTriggerEl = null;

    if (toFocus instanceof HTMLElement && document.contains(toFocus)) {
        requestAnimationFrame(() => toFocus.focus());
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') {
        return;
    }
    const modal = getFleetDetailModal();
    if (modal && !modal.classList.contains('hidden')) {
        e.preventDefault();
        closeFleetDetailModal();
    }
});

document.getElementById('fleet-refresh-all')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    try {
        const r = await fetch('/refresh', {
            method: 'POST',
            headers: fleetHeaders(),
        });
        const data = await r.json();
        if (!r.ok) {
            throw new Error(data.message || 'Refresh failed');
        }
        const stats = document.getElementById('fleet-stats-strip');
        if (stats && data.html_stats) {
            stats.outerHTML = data.html_stats;
        }
        const alerts = document.getElementById('fleet-alert-timeline-section');
        if (alerts && data.html_alerts) {
            alerts.outerHTML = data.html_alerts;
        }
        const cmp = document.getElementById('fleet-compare-section');
        if (cmp && data.html_compare) {
            cmp.outerHTML = data.html_compare;
        }
        const grid = document.getElementById('fleet-cards-grid');
        if (grid && data.html_grid) {
            grid.outerHTML = data.html_grid;
        }
    } catch (e) {
        alert(e instanceof Error ? e.message : 'Refresh failed');
    } finally {
        btn.disabled = false;
    }
});

document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-fleet-refresh]');
    if (!btn || btn.disabled || btn.id === 'fleet-refresh-all') {
        return;
    }
    const key = btn.getAttribute('data-fleet-refresh');
    if (!key) {
        return;
    }
    btn.disabled = true;
    try {
        const r = await fetch('/refresh/' + encodeURIComponent(key), {
            method: 'POST',
            headers: fleetHeaders(),
        });
        const data = await r.json();
        if (!r.ok) {
            throw new Error(data.message || 'Refresh failed');
        }
        const li = document.querySelector('[data-fleet-card="' + key + '"]');
        if (li && data.html) {
            li.outerHTML = data.html;
        }
        const stats = document.getElementById('fleet-stats-strip');
        if (stats && data.html_stats) {
            stats.outerHTML = data.html_stats;
        }
        const alertsOne = document.getElementById('fleet-alert-timeline-section');
        if (alertsOne && data.html_alerts) {
            alertsOne.outerHTML = data.html_alerts;
        }
        if (data.html_compare_row && key) {
            const row = document.querySelector('#fleet-compare-table .fc-compare-row[data-compare-key="' + key + '"]');
            if (row) {
                row.outerHTML = data.html_compare_row;
            }
        }
    } catch (err) {
        alert(err instanceof Error ? err.message : 'Refresh failed');
    } finally {
        btn.disabled = false;
    }
});

document.addEventListener('click', function (e) {
    const tab = e.target.closest('.fc-spark-range');
    if (!tab) {
        return;
    }
    const card = tab.closest('[data-fleet-card]');
    if (!card) {
        return;
    }
    e.preventDefault();
    const range = tab.getAttribute('data-spark-range');
    if (!range) {
        return;
    }

    card.querySelectorAll('[data-spark-panel]').forEach((p) => {
        const match = p.getAttribute('data-spark-panel') === range;
        p.classList.toggle('hidden', !match);
    });

    card.querySelectorAll('[data-latency-panel]').forEach((p) => {
        const match = p.getAttribute('data-latency-panel') === range;
        p.classList.toggle('hidden', !match);
    });

    card.querySelectorAll('.fc-spark-range').forEach((b) => {
        const active = b.getAttribute('data-spark-range') === range;
        b.classList.toggle('text-cyan-200', active);
        b.classList.toggle('font-medium', active);
        b.classList.toggle('ring-2', active);
        b.classList.toggle('ring-cyan-500/40', active);
        b.classList.toggle('text-zinc-500', !active);
        b.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
});

document.addEventListener('click', function (e) {
    if (e.target.closest('[data-fleet-detail-close]')) {
        closeFleetDetailModal();
    }
});

document.addEventListener('click', async function (e) {
    const detailsBtn = e.target.closest('[data-fleet-details]');
    if (!detailsBtn) {
        return;
    }
    const key = detailsBtn.getAttribute('data-fleet-details');
    if (!key) {
        return;
    }

    const modal = getFleetDetailModal();
    const body = document.getElementById('fleet-detail-body');
    const title = document.getElementById('fleet-detail-title');
    const subtitle = document.getElementById('fleet-detail-subtitle');
    const errEl = document.getElementById('fleet-detail-error');

    if (!modal || !body || !title || !subtitle) {
        return;
    }

    openFleetDetailModal(detailsBtn);
    title.textContent = 'Loading…';
    subtitle.textContent = key;
    fleetDetailSetStatus('Loading poll details.');

    body.replaceChildren();
    const loading = document.createElement('div');
    loading.className = 'flex flex-col gap-3 rounded-lg border border-zinc-800/50 bg-zinc-900/25 p-4';
    loading.setAttribute('aria-hidden', 'true');
    const loadingLabel = document.createElement('p');
    loadingLabel.className = 'text-sm text-zinc-400 motion-safe:animate-pulse';
    loadingLabel.textContent = 'Fetching poll history…';
    const bar = document.createElement('div');
    bar.className = 'h-1 max-w-xs overflow-hidden rounded-full bg-zinc-800';
    bar.setAttribute('aria-hidden', 'true');
    const barFill = document.createElement('div');
    barFill.className = 'h-full w-2/5 rounded-full bg-cyan-500/35 motion-safe:animate-pulse';
    bar.appendChild(barFill);
    loading.appendChild(loadingLabel);
    loading.appendChild(bar);
    body.appendChild(loading);

    if (errEl) {
        errEl.classList.add('hidden');
        errEl.textContent = '';
    }

    try {
        const r = await fetch('/targets/' + encodeURIComponent(key) + '/poll-detail', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await r.json();
        if (!r.ok) {
            throw new Error(data.message || 'Could not load detail');
        }
        title.textContent = data.name || key;
        subtitle.textContent = data.key || key;
        fleetDetailSetBusy(false);
        fleetDetailSetStatus(data.name ? `Loaded details for ${data.name}.` : 'Details loaded.');
        renderFleetDetail(data);
        requestAnimationFrame(() => {
            const jsonPre = body.querySelector('pre[tabindex]');
            if (jsonPre instanceof HTMLElement) {
                jsonPre.focus();
            } else {
                document.getElementById('fleet-detail-close-btn')?.focus();
            }
        });
    } catch (err) {
        fleetDetailSetBusy(false);
        fleetDetailSetStatus('');
        body.replaceChildren();
        if (errEl) {
            errEl.textContent = err instanceof Error ? err.message : 'Request failed';
            errEl.classList.remove('hidden');
        }
        title.textContent = 'Poll detail';
        requestAnimationFrame(() => {
            document.getElementById('fleet-detail-close-btn')?.focus();
        });
    }
});

function fleetFocusFleetCardsGrid() {
    const el = document.getElementById('fleet-cards-grid');
    if (!(el instanceof HTMLElement)) {
        return;
    }
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    el.focus({ preventScroll: true });
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('#fleet-skip-to-cards')) {
        return;
    }
    e.preventDefault();
    fleetFocusFleetCardsGrid();
});

document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#fleet-cards-grid') {
        fleetFocusFleetCardsGrid();
    }
});

document.addEventListener('click', async function (e) {
    const genPer = e.target.closest('[data-fleet-generate-operator-token]');
    if (genPer) {
        const toolkit = genPer.closest('[data-fleet-operator-token-toolkit]');
        const input = toolkit?.querySelector('#operator_token');
        const status = toolkit?.querySelector('[data-fleet-operator-token-status]');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }
        const token = fleetSecureOperatorToken();
        input.type = 'text';
        input.value = token;
        input.focus();
        fleetTokenUiStatus(
            status,
            'Token generated. Use “Copy for target .env”, paste into that app’s environment, then Save here.',
            'ok',
        );
        return;
    }

    const copyPer = e.target.closest('[data-fleet-copy-operator-token-only]');
    if (copyPer) {
        const toolkit = copyPer.closest('[data-fleet-operator-token-toolkit]');
        const input = toolkit?.querySelector('#operator_token');
        const status = toolkit?.querySelector('[data-fleet-operator-token-status]');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }
        const v = input.value.trim();
        if (!v) {
            fleetTokenUiStatus(status, 'Nothing to copy — generate or paste a token first.', 'err');
            return;
        }
        const ok = await fleetClipboardWrite(v);
        fleetTokenUiStatus(
            status,
            ok ? 'Token copied. Paste into the target app’s secret/config (see its docs for the env name).' : 'Could not copy — select the field and copy manually.',
            ok ? 'ok' : 'err',
        );
        return;
    }

    const copyEnv = e.target.closest('[data-fleet-copy-operator-target-env]');
    if (copyEnv) {
        const toolkit = copyEnv.closest('[data-fleet-operator-token-toolkit]');
        const input = toolkit?.querySelector('#operator_token');
        const status = toolkit?.querySelector('[data-fleet-operator-token-status]');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }
        const v = input.value.trim();
        if (!v) {
            fleetTokenUiStatus(status, 'Nothing to copy — generate or paste a token first.', 'err');
            return;
        }
        const line = `FLEET_OPERATOR_TOKEN=${v}`;
        const ok = await fleetClipboardWrite(line.endsWith('\n') ? line : `${line}\n`);
        fleetTokenUiStatus(
            status,
            ok
                ? 'Copied FLEET_OPERATOR_TOKEN=… — paste into the target app .env (or rename the key if that product uses a different name).'
                : 'Could not copy — copy the line from the field manually.',
            ok ? 'ok' : 'err',
        );
        return;
    }

    const genShared = e.target.closest('[data-fleet-generate-shared-operator-token]');
    if (genShared) {
        const toolkit = genShared.closest('[data-fleet-shared-operator-token-toolkit]');
        const lineInput = toolkit?.querySelector('#fleet-shared-operator-env-line');
        const status = toolkit?.querySelector('[data-fleet-shared-operator-token-status]');
        if (!(lineInput instanceof HTMLInputElement)) {
            return;
        }
        const token = fleetSecureOperatorToken();
        lineInput.value = `FLEET_OPERATOR_TOKEN=${token}`;
        lineInput.focus();
        lineInput.select();
        fleetTokenUiStatus(
            status,
            'New line ready. Copy it into Fleet’s .env and each target app, then clear per-target tokens if you use this shared secret.',
            'ok',
        );
        return;
    }

    const copyShared = e.target.closest('[data-fleet-copy-shared-operator-token]');
    if (copyShared) {
        const toolkit = copyShared.closest('[data-fleet-shared-operator-token-toolkit]');
        const lineInput = toolkit?.querySelector('#fleet-shared-operator-env-line');
        const status = toolkit?.querySelector('[data-fleet-shared-operator-token-status]');
        if (!(lineInput instanceof HTMLInputElement)) {
            return;
        }
        const v = lineInput.value.trim();
        if (!v) {
            fleetTokenUiStatus(status, 'Generate a line first.', 'err');
            return;
        }
        const ok = await fleetClipboardWrite(v.endsWith('\n') ? v : `${v}\n`);
        fleetTokenUiStatus(
            status,
            ok ? 'Copied. Paste into Fleet .env and each target app (same variable name on each side).' : 'Could not copy — select the field above and copy manually.',
            ok ? 'ok' : 'err',
        );
    }
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.fc-compare-sort');
    if (!btn) {
        return;
    }
    const table = btn.closest('#fleet-compare-table');
    if (!table) {
        return;
    }
    const tbody = table.querySelector('tbody');
    if (!tbody) {
        return;
    }

    const sortKey = btn.getAttribute('data-sort');
    if (!sortKey) {
        return;
    }

    const prevKey = table.getAttribute('data-sort-key');
    let dir = 'asc';
    if (prevKey === sortKey) {
        dir = table.getAttribute('data-sort-dir') === 'desc' ? 'asc' : 'desc';
    }
    table.setAttribute('data-sort-key', sortKey);
    table.setAttribute('data-sort-dir', dir);

    const mult = dir === 'asc' ? 1 : -1;
    const rows = Array.from(tbody.querySelectorAll('.fc-compare-row'));

    const cmpNum = (a, b, attr) => {
        const av = parseFloat(a.getAttribute(attr) ?? '0');
        const bv = parseFloat(b.getAttribute(attr) ?? '0');
        const aMissing = av < 0;
        const bMissing = bv < 0;
        if (aMissing && bMissing) {
            return 0;
        }
        if (aMissing) {
            return 1;
        }
        if (bMissing) {
            return -1;
        }

        return mult * (av - bv);
    };

    rows.sort((a, b) => {
        if (sortKey === 'name') {
            return mult * (a.dataset.name || '').localeCompare(b.dataset.name || '');
        }
        if (sortKey === 'live') {
            return mult * (parseInt(a.dataset.live ?? '0', 10) - parseInt(b.dataset.live ?? '0', 10));
        }
        if (sortKey === 'slo24') {
            return cmpNum(a, b, 'data-slo24');
        }
        if (sortKey === 'slo7') {
            return cmpNum(a, b, 'data-slo7');
        }
        if (sortKey === 'p50') {
            return cmpNum(a, b, 'data-p50');
        }
        if (sortKey === 'last') {
            return mult * (parseInt(a.dataset.last ?? '0', 10) - parseInt(b.dataset.last ?? '0', 10));
        }

        return 0;
    });

    rows.forEach((r) => tbody.appendChild(r));
});
