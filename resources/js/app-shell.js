/**
 * Cabinet shell: the sidebar drawer, its collapsible sections and the narrow (icons-only) mode.
 *
 * The sidebar is docked from the `xl` breakpoint (1280px) up; below it the same element
 * slides in from the left as a drawer and slides back out the same way. Docked, it can be
 * narrowed to icons; that choice is kept per device.
 */

const COLLAPSED_KEY = 'tc.sidebar.collapsed';
// Also read by the inline script in layouts/app.blade.php — keep the two in sync.
const COMPACT_KEY = 'tc.sidebar.compact';
const COMPACT_CLASS = 'sidebar-compact';
// Matches Tailwind's `xl` — below it the sidebar is a drawer, so wide admin tables keep full width.
const DESKTOP = '(min-width: 1280px)';

// localStorage can throw (private mode, blocked site data) — never let that break the menu.
function readCollapsed() {
    try {
        return JSON.parse(localStorage.getItem(COLLAPSED_KEY)) || {};
    } catch {
        return {};
    }
}

function writeCollapsed(map) {
    try {
        localStorage.setItem(COLLAPSED_KEY, JSON.stringify(map));
    } catch {
        /* state just won't persist */
    }
}

function writeCompact(compact) {
    try {
        localStorage.setItem(COMPACT_KEY, compact ? '1' : '0');
    } catch {
        /* state just won't persist */
    }
}

export function registerAppShell(Alpine) {
    Alpine.data('appShell', () => ({
        sidebarOpen: false,
        // The <html> class does the styling; it's already set when the saved choice is "narrow".
        compact: document.documentElement.classList.contains(COMPACT_CLASS),
        // Narrow, the sidebar hides its labels, so hovering or focusing an icon names it here.
        tip: { text: '', top: 0, show: false },

        init() {
            // Growing the window to desktop while the drawer is open must not leave the page scroll-locked.
            window.matchMedia(DESKTOP).addEventListener('change', (event) => {
                if (event.matches) {
                    this.sidebarOpen = false;
                    document.body.classList.remove('overflow-hidden');
                }
            });
        },

        openSidebar() {
            this.sidebarOpen = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.sidebarClose?.focus());
        },

        closeSidebar() {
            if (!this.sidebarOpen) {
                return;
            }
            this.sidebarOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.$refs.menuButton?.focus());
        },

        toggleCompact() {
            this.compact = !this.compact;
            this.hideTip();
            document.documentElement.classList.toggle(COMPACT_CLASS, this.compact);
            writeCompact(this.compact);
        },

        showTip(el) {
            if (!this.compact || !window.matchMedia(DESKTOP).matches) {
                return;
            }
            // Positioned against the sidebar, not inside the scrolling menu, so it isn't clipped.
            const sidebar = el.closest('#app-sidebar').getBoundingClientRect();
            const box = el.getBoundingClientRect();
            this.tip = { text: el.dataset.tip, top: box.top - sidebar.top + box.height / 2, show: true };
        },

        hideTip() {
            this.tip.show = false;
        },
    }));

    // A section containing the current page is always open; the rest follow the
    // user's last choice, falling back to the server-provided default.
    Alpine.data('navSection', (key, hasActive, collapsedByDefault = false) => ({
        open: true,
        // The arrow only animates for user toggles — applying a saved choice on
        // page load must not show a rotation.
        animate: false,

        init() {
            const collapsed = readCollapsed();
            const isCollapsed = key in collapsed ? collapsed[key] : collapsedByDefault;
            this.open = hasActive || !isCollapsed;
            requestAnimationFrame(() => requestAnimationFrame(() => { this.animate = true; }));
        },

        toggle() {
            this.open = !this.open;
            const collapsed = readCollapsed();
            collapsed[key] = !this.open;
            writeCollapsed(collapsed);
        },
    }));
}
