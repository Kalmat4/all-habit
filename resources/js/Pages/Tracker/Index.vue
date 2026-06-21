<script setup>
import { ref, computed, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const DEP_COLORS = [
    { bg: 'rgba(99,102,241,0.12)',  border: 'rgba(99,102,241,0.3)',  text: '#818cf8' },
    { bg: 'rgba(244,114,182,0.12)', border: 'rgba(244,114,182,0.3)', text: '#f472b6' },
    { bg: 'rgba(251,191,36,0.12)',  border: 'rgba(251,191,36,0.3)',  text: '#fbbf24' },
    { bg: 'rgba(52,211,153,0.12)',  border: 'rgba(52,211,153,0.3)',  text: '#34d399' },
    { bg: 'rgba(251,146,60,0.12)',  border: 'rgba(251,146,60,0.3)',  text: '#fb923c' },
    { bg: 'rgba(167,139,250,0.12)', border: 'rgba(167,139,250,0.3)', text: '#a78bfa' },
    { bg: 'rgba(56,189,248,0.12)',  border: 'rgba(56,189,248,0.3)',  text: '#38bdf8' },
    { bg: 'rgba(248,113,113,0.12)', border: 'rgba(248,113,113,0.3)', text: '#f87171' },
];

function depColor(index) {
    return DEP_COLORS[index % DEP_COLORS.length];
}

const props = defineProps({
    dependencies: Array,
    todayEntries: Array,
    report7d: Object,
    report30d: Object,
    reportAll: Object,
    trend: Array,
    topTriggers: Object,
});

const tab = ref('log');

// ── Impulse form ──
const impulseForm = useForm({
    dependency_id: null,
    resisted: true,
    trigger: '',
    comment: '',
});

function selectDep(id) {
    impulseForm.dependency_id = impulseForm.dependency_id === id ? null : id;
}

function logImpulse(resisted) {
    impulseForm.resisted = resisted;
    impulseForm.post('/tracker/impulses', {
        preserveScroll: true,
        onSuccess: () => {
            impulseForm.trigger = '';
            impulseForm.comment = '';
        },
    });
}

function deleteImpulse(id) {
    router.delete(`/tracker/impulses/${id}`, { preserveScroll: true });
}

// ── Dependency form ──
const depForm = useForm({ name: '' });

function addDep() {
    depForm.post('/tracker/dependencies', {
        preserveScroll: true,
        onSuccess: () => { depForm.name = ''; },
    });
}

function deleteDep(id) {
    if (!confirm('Удалить зависимость и все записи?')) return;
    router.delete(`/tracker/dependencies/${id}`, { preserveScroll: true });
}

// ── Helpers ──
function depName(id) {
    return props.dependencies?.find(d => d.id === id)?.name ?? '—';
}

function formatTime(ts) {
    return new Date(ts).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

const period = ref('7d');

const PERIODS = { '7d': 'report7d', '30d': 'report30d', 'all': 'reportAll' };

const periodStats = computed(() => {
    if (!props.dependencies) return [];
    const data = props[PERIODS[period.value]] ?? {};
    return props.dependencies.map(dep => {
        const s = data[dep.id];
        return {
            id: dep.id,
            name: dep.name,
            total: s?.total ?? 0,
            resisted: s?.resisted ?? 0,
            rate: s?.rate ?? null,
        };
    });
});

// ── Accordion: impulse details per dependency ──
const expandedDep = ref(null);
const depImpulses = reactive({});
const depLoading = ref(false);

function toggleAccordion(depId) {
    if (expandedDep.value === depId) {
        expandedDep.value = null;
        return;
    }
    expandedDep.value = depId;
    loadImpulses(depId, 1);
}

async function loadImpulses(depId, page) {
    depLoading.value = true;
    try {
        const params = new URLSearchParams({
            dependency_id: depId,
            period: period.value,
            page,
        });
        const res = await fetch(`/tracker/impulses?${params}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        depImpulses[depId] = await res.json();
    } finally {
        depLoading.value = false;
    }
}

function goPage(depId, page) {
    loadImpulses(depId, page);
}

watch(period, () => {
    if (expandedDep.value) {
        loadImpulses(expandedDep.value, 1);
    }
});

function formatDate(ts) {
    return new Date(ts).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function trendColor(rate) {
    if (rate >= 80) return 'var(--success)';
    if (rate >= 50) return 'var(--warning)';
    return 'var(--danger)';
}

function weekLabel(w) {
    if (!w.week_start || !w.week_end) return '';
    const fmt = (d) => {
        const p = d.split('-');
        return `${p[2]}.${p[1]}`;
    };
    const s = fmt(w.week_start);
    const e = w.week_end.split('-');
    return `${s}–${e[2]}.${e[1]}`;
}
</script>

<template>
    <AppLayout>
        <!-- Tab bar -->
        <div class="tab-bar">
            <button class="tab-btn" :class="{ active: tab === 'log' }" @click="tab = 'log'">
                Лог
            </button>
            <button class="tab-btn" :class="{ active: tab === 'report' }" @click="tab = 'report'">
                Отчёт
            </button>
            <button class="tab-btn" :class="{ active: tab === 'deps' }" @click="tab = 'deps'">
                Зависимости
            </button>
        </div>

        <!-- ═══ TAB: Log ═══ -->
        <div v-show="tab === 'log'">
            <!-- Dependency chips -->
            <div class="dep-chips">
                <button
                    v-for="(dep, i) in dependencies"
                    :key="dep.id"
                    class="dep-chip"
                    :class="{ selected: impulseForm.dependency_id === dep.id }"
                    :style="{
                        '--chip-bg': depColor(i).bg,
                        '--chip-border': depColor(i).border,
                        '--chip-text': depColor(i).text,
                    }"
                    @click="selectDep(dep.id)"
                >
                    {{ dep.name }}
                </button>
            </div>

            <div v-if="impulseForm.errors.dependency_id" class="field-error" style="margin-bottom:8px">
                {{ impulseForm.errors.dependency_id }}
            </div>

            <!-- Quick log form (inputs + actions) -->
            <div class="quick-log">
                <input
                    v-model="impulseForm.trigger"
                    type="text"
                    placeholder="Триггер (необязательно)"
                />

                <input
                    v-model="impulseForm.comment"
                    type="text"
                    placeholder="Комментарий (необязательно)"
                />

                <div class="btn-row">
                    <button
                        class="btn-resist"
                        :disabled="!impulseForm.dependency_id || impulseForm.processing"
                        @click="logImpulse(true)"
                    >
                        Устоял
                    </button>
                    <button
                        class="btn-slip"
                        :disabled="!impulseForm.dependency_id || impulseForm.processing"
                        @click="logImpulse(false)"
                    >
                        Сорвался
                    </button>
                </div>
            </div>

            <!-- Today entries -->
            <p class="section-title">Сегодня</p>

            <div v-if="todayEntries?.length">
                <div v-for="entry in todayEntries" :key="entry.id" class="log-entry">
                    <span class="log-dot" :class="entry.resisted ? 'resisted' : 'slipped'" />
                    <div class="log-body">
                        <div class="log-name">
                            {{ entry.dependency?.name ?? depName(entry.dependency_id) }}
                        </div>
                        <div class="log-meta">
                            {{ formatTime(entry.created_at) }}
                            <template v-if="entry.trigger"> &middot; {{ entry.trigger }}</template>
                            <template v-if="entry.comment"> &middot; {{ entry.comment }}</template>
                        </div>
                    </div>
                    <button class="log-delete" title="Удалить" @click="deleteImpulse(entry.id)">
                        &times;
                    </button>
                </div>
            </div>
            <div v-else class="empty-state">
                <div class="empty-icon">~</div>
                <div>Записей пока нет. Зафиксируйте первый импульс.</div>
            </div>
        </div>

        <!-- ═══ TAB: Report ═══ -->
        <div v-show="tab === 'report'">
            <!-- Period switcher -->
            <div class="period-bar">
                <button class="period-btn" :class="{ active: period === '7d' }"  @click="period = '7d'">7 дней</button>
                <button class="period-btn" :class="{ active: period === '30d' }" @click="period = '30d'">30 дней</button>
                <button class="period-btn" :class="{ active: period === 'all' }" @click="period = 'all'">Всё время</button>
            </div>

            <template v-if="periodStats.some(s => s.total)">
                <div v-for="s in periodStats" :key="s.id" style="margin-bottom:12px">
                    <div
                        class="card-dark accordion-header"
                        :class="{ expanded: expandedDep === s.id }"
                        @click="toggleAccordion(s.id)"
                    >
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                            <span style="font-weight:700;font-size:0.95rem;display:flex;align-items:center;gap:8px">
                                <span class="accordion-arrow" :class="{ open: expandedDep === s.id }">&#9654;</span>
                                {{ s.name }}
                            </span>
                            <span v-if="s.rate !== null" class="rate-badge" :class="s.rate >= 80 ? 'good' : s.rate >= 50 ? 'warn' : 'bad'">
                                {{ s.rate }}%
                            </span>
                        </div>
                        <div class="stat-row">
                            <div class="stat-box">
                                <div class="stat-num">{{ s.total }}</div>
                                <div class="stat-label">Импульсов</div>
                            </div>
                            <div class="stat-box" :class="s.rate >= 80 ? 'good' : s.rate >= 50 ? 'warn' : s.rate !== null ? 'bad' : ''">
                                <div class="stat-num">{{ s.resisted }}</div>
                                <div class="stat-label">Устоял</div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion body -->
                    <div v-if="expandedDep === s.id" class="accordion-body">
                        <div v-if="depLoading" style="text-align:center;padding:20px;color:var(--text-muted)">
                            Загрузка...
                        </div>
                        <template v-else-if="depImpulses[s.id]?.data?.length">
                            <table class="impulse-table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Время</th>
                                        <th>Результат</th>
                                        <th>Триггер</th>
                                        <th>Комментарий</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="imp in depImpulses[s.id].data" :key="imp.id">
                                        <td>{{ formatDate(imp.created_at) }}</td>
                                        <td>{{ formatTime(imp.created_at) }}</td>
                                        <td>
                                            <span class="result-dot" :class="imp.resisted ? 'resisted' : 'slipped'">
                                                {{ imp.resisted ? 'Устоял' : 'Сорвался' }}
                                            </span>
                                        </td>
                                        <td class="cell-truncate">{{ imp.trigger || '—' }}</td>
                                        <td class="cell-truncate">{{ imp.comment || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div v-if="depImpulses[s.id].last_page > 1" class="table-pagination">
                                <button
                                    class="page-btn"
                                    :disabled="depImpulses[s.id].current_page <= 1"
                                    @click="goPage(s.id, depImpulses[s.id].current_page - 1)"
                                >&laquo;</button>
                                <span class="page-info">
                                    {{ depImpulses[s.id].current_page }} / {{ depImpulses[s.id].last_page }}
                                </span>
                                <button
                                    class="page-btn"
                                    :disabled="depImpulses[s.id].current_page >= depImpulses[s.id].last_page"
                                    @click="goPage(s.id, depImpulses[s.id].current_page + 1)"
                                >&raquo;</button>
                            </div>
                        </template>
                        <div v-else style="text-align:center;padding:16px;color:var(--text-muted);font-size:0.85rem">
                            Нет записей за этот период
                        </div>
                    </div>
                </div>
            </template>

            <div v-else class="empty-state">
                <div class="empty-icon">~</div>
                <div>Нет данных за этот период</div>
            </div>

            <!-- Trend chart -->
            <template v-if="trend?.length > 1">
                <p class="section-title">Тренд (resist rate)</p>
                <div class="card-dark">
                    <div class="trend-chart" style="margin-bottom:24px">
                        <div
                            v-for="(w, i) in trend"
                            :key="i"
                            class="trend-bar"
                            :style="{
                                height: (w.rate ?? 0) + '%',
                                background: trendColor(w.rate ?? 0),
                                opacity: 0.7 + (i / trend.length) * 0.3,
                            }"
                        >
                            <span class="trend-label">{{ weekLabel(w) }}</span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Top triggers -->
            <template v-if="topTriggers && Object.keys(topTriggers).length">
                <p class="section-title">Частые триггеры</p>
                <div v-for="dep in dependencies" :key="'trig-' + dep.id">
                    <template v-if="topTriggers[dep.id]?.length">
                        <div class="card-dark">
                            <div style="font-weight:600;font-size:0.85rem;margin-bottom:8px">
                                {{ dep.name }}
                            </div>
                            <div>
                                <span
                                    v-for="t in topTriggers[dep.id]"
                                    :key="t.name"
                                    class="trigger-tag"
                                >
                                    {{ t.name }}<span class="trigger-count">{{ t.count }}</span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- ═══ TAB: Dependencies ═══ -->
        <div v-show="tab === 'deps'">
            <p class="section-title">Добавить</p>
            <form class="dep-add flex-column flex-sm-row" @submit.prevent="addDep">
                <input
                    v-model="depForm.name"
                    type="text"
                    placeholder="Название зависимости"
                />
                <button type="submit" :disabled="!depForm.name.trim() || depForm.processing">
                    Добавить
                </button>
            </form>
            <div v-if="depForm.errors.name" class="field-error" style="margin-top:-12px;margin-bottom:12px">
                {{ depForm.errors.name }}
            </div>

            <p class="section-title">Отслеживаемые</p>

            <div v-if="dependencies?.length">
                <div v-for="dep in dependencies" :key="dep.id" class="dep-item">
                    <span class="dep-name">{{ dep.name }}</span>
                    <button class="btn-ghost" style="font-size:0.75rem;padding:4px 10px" @click="deleteDep(dep.id)">
                        Удалить
                    </button>
                </div>
            </div>
            <div v-else class="empty-state">
                <div class="empty-icon">~</div>
                <div>Добавьте первую зависимость для отслеживания</div>
            </div>
        </div>
    </AppLayout>
</template>
