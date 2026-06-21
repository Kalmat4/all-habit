<script setup>
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="app-shell">
        <header class="app-header">
            <h1>All - Habit</h1>
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:0.8rem;color:var(--text-muted)">{{ user?.name }}</span>
                <button class="btn-ghost" @click="logout">Выйти</button>
            </div>
        </header>
        <slot />
    </div>
</template>
