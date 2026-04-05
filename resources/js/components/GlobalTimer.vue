<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { Clock, Square } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import timeEntries from '@/routes/time-entries';
import type { TimeEntry } from '@/types';

const page = usePage();
const activeEntry = computed(() => page.props.auth.user?.active_time_entry as TimeEntry | null);

const seconds = ref(0);
const timerInterval = ref<number | null>(null);

const formatDuration = (totalSeconds: number) => {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

const updateTimer = () => {
    if (activeEntry.value) {
        const start = new Date(activeEntry.value.started_at).getTime();
        const now = new Date().getTime();
        seconds.value = Math.floor((now - start) / 1000);
    } else {
        seconds.value = 0;
    }
};

const startInterval = () => {
    if (timerInterval.value) {
clearInterval(timerInterval.value);
}

    timerInterval.value = window.setInterval(updateTimer, 1000);
};

const stopInterval = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
        timerInterval.value = null;
    }
};

const stopTimer = () => {
    if (activeEntry.value && page.props.currentTeam) {
        useForm({}).post(
            timeEntries.stop.url({
                current_team: (page.props.currentTeam as any).slug,
                time_entry: activeEntry.value.id,
            }),
            {
                preserveScroll: true,
            },
        );
    }
};

watch(activeEntry, (newEntry) => {
    if (newEntry) {
        updateTimer();
        startInterval();
    } else {
        stopInterval();
        seconds.value = 0;
    }
}, { immediate: true });

onMounted(() => {
    if (activeEntry.value) {
        startInterval();
    }
});

onUnmounted(() => {
    stopInterval();
});
</script>

<template>
    <div v-if="activeEntry" class="flex items-center gap-3 px-3 py-1.5 bg-primary/10 rounded-full border border-primary/20 animate-pulse-slow">
        <div class="flex items-center gap-2 text-sm font-mono font-bold text-primary">
            <Clock class="w-4 h-4 animate-spin-slow" />
            {{ formatDuration(seconds) }}
        </div>
        <div class="h-4 w-px bg-primary/20"></div>
        <div class="text-xs font-medium truncate max-w-[120px]">
            {{ activeEntry.task?.name || 'Tracking time...' }}
        </div>
        <Button variant="ghost" size="icon-sm" class="h-6 w-6 text-destructive hover:bg-destructive/10" @click="stopTimer">
            <Square class="w-3 h-3 fill-current" />
        </Button>
    </div>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}
.animate-pulse-slow {
    animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 8s linear infinite;
}
</style>
