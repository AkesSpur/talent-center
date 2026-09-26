/**
 * Charts for the helpdesk analytics page.
 *
 * Chart.js is pulled in with a dynamic import so Vite emits it as its own chunk:
 * the three or four staff who open this page download it, and the other forty
 * pages on the site do not.
 */

const PRIMARY = '#8B4513';
// Not the brand gold #D4AF37 — it measures ~2:1 on white and is unreadable as a line.
const GOLD = '#A67C00';
const INK = '#2C2416';
const MUTED = '#9A8B7A';
const HAIRLINE = 'rgba(212, 175, 55, 0.2)';

/** d.m — the labels come from the server as Y-m-d. */
function shortDay(iso) {
    const [, month, day] = iso.split('-');

    return `${day}.${month}`;
}

export function registerSupportAnalytics(Alpine) {
    Alpine.data('supportAnalytics', (series, statuses) => ({
        async init() {
            const { Chart } = await import('chart.js/auto');

            Chart.defaults.font.family = "'Inter', Arial, sans-serif";
            Chart.defaults.color = MUTED;

            const labels = series.labels.map(shortDay);
            const grid = { color: HAIRLINE, drawBorder: false };

            new Chart(this.$refs.flow, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Новые',
                            data: series.created,
                            borderColor: PRIMARY,
                            backgroundColor: 'rgba(139, 69, 19, 0.08)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Закрытые',
                            data: series.closed,
                            borderColor: GOLD,
                            backgroundColor: 'rgba(166, 124, 0, 0.08)',
                            tension: 0.3,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid },
                        x: { grid: { display: false } },
                    },
                },
            });

            new Chart(this.$refs.statuses, {
                type: 'doughnut',
                data: {
                    labels: statuses.map((s) => s.label),
                    datasets: [{
                        data: statuses.map((s) => s.value),
                        backgroundColor: statuses.map((s) => s.color),
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { position: 'right', labels: { color: INK, boxWidth: 12 } } },
                },
            });

            new Chart(this.$refs.resolution, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Часов до решения',
                        data: series.resolutionHours,
                        borderColor: PRIMARY,
                        backgroundColor: 'rgba(139, 69, 19, 0.08)',
                        tension: 0.3,
                        // Days with no closures are null, not zero — join across them
                        // rather than drawing a dip to the floor every quiet weekend.
                        spanGaps: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid },
                        x: { grid: { display: false } },
                    },
                    plugins: { legend: { display: false } },
                },
            });
        },
    }));
}
