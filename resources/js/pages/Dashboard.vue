<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Play, Square, Users, Briefcase, CheckCircle2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { dashboard } from '@/routes';
import timeEntryRoutes from '@/routes/time-entries';
import type { Company, Project } from '@/types';

interface Props {
    currentCompany: Company;
    role: 'admin' | 'member';
    stats: {
        // Admin stats
        total_projects?: number;
        total_clients?: number;
        active_tasks?: number;
        company_members?: number;
        // Member stats
        last_project?: Project | null;
        week_hours?: number;
        month_hours?: number;
        current_status?: 'working' | 'idle';
    };
    available_projects?: Array<{
        id: number;
        name: string;
        tasks: Array<{ id: number; name: string }>;
    }>;
}

const props = defineProps<Props>();
const page = usePage();
const user = computed(() => page.props.auth.user);

const selectedProjectId = ref<number | null>(null);
const selectedTaskId = ref<number | null>(null);

const startForm = useForm({
    task_id: null as number | null,
});

const stopForm = useForm({});

const startWorking = () => {
    if (!selectedTaskId.value) {
return;
}

    startForm.task_id = selectedTaskId.value;
    startForm.post(timeEntryRoutes.start.url(props.currentCompany.slug), {
        preserveScroll: true,
        onSuccess: () => {
            selectedProjectId.value = null;
            selectedTaskId.value = null;
        }
    });
};

const stopWorking = () => {
    const activeEntryId = user.value?.active_time_entry?.id;

    if (!activeEntryId || !props.currentCompany?.slug) {
        return;
    }

    stopForm.post(
        timeEntryRoutes.stop.url({
            current_company: props.currentCompany.slug,
            time_entry: activeEntryId,
        }),
        {
            preserveScroll: true,
        },
    );
};

defineOptions({
    layout: (props: { currentCompany?: Company | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentCompany
                    ? dashboard(props.currentCompany.slug)
                    : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Admin Dashboard -->
        <div v-if="role === 'admin'" class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-bold tracking-tight">Admin Overview</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Projects</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_projects }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Clients</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_clients }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending Tasks</CardTitle>
                        <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.active_tasks }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Company Members</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.company_members }}</div>
                    </CardContent>
                </Card>
            </div>

            <Card class="col-span-4">
                <CardHeader>
                    <CardTitle>Welcome back, Admin!</CardTitle>
                    <CardDescription>
                        Use the sidebar to manage projects, clients, and your company.
                    </CardDescription>
                </CardHeader>
            </Card>
        </div>

        <!-- Member Dashboard -->
        <div v-else class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Your Dashboard</h2>
                    <p class="text-muted-foreground">Welcome back, {{ user?.name }}!</p>
                </div>
                <Badge :variant="stats.current_status === 'working' ? 'default' : 'secondary'" class="px-4 py-1 text-sm">
                    {{ stats.current_status === 'working' ? 'Currently Working' : 'Idle' }}
                </Badge>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Weekly Hours</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.week_hours }}h</div>
                        <p class="text-xs text-muted-foreground">Current week</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Monthly Hours</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.month_hours }}h</div>
                        <p class="text-xs text-muted-foreground">Current month</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Last Project</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-bold truncate">
                            {{ stats.last_project?.name || 'No recent projects' }}
                        </div>
                        <p class="text-xs text-muted-foreground">Most recent activity</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Time Tracking Controls -->
            <Card>
                <CardHeader>
                    <CardTitle>Time Tracking</CardTitle>
                    <CardDescription>Start a new session or manage your active work.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="stats.current_status === 'working'" class="flex flex-col items-center justify-center space-y-4 py-6">
                        <div class="text-center">
                            <p class="text-sm font-medium text-muted-foreground">Currently working on:</p>
                            <h3 class="text-2xl font-bold">{{ user?.active_time_entry?.task?.project?.name }}</h3>
                            <p class="text-lg text-primary">{{ user?.active_time_entry?.task?.name }}</p>
                        </div>
                        <div class="flex gap-4">
                            <Button variant="destructive" size="lg" @click="stopWorking" :disabled="stopForm.processing">
                                <Square class="mr-2 h-5 w-5" />
                                Stop Working
                            </Button>
                        </div>
                    </div>

                    <div v-else class="space-y-6 py-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Select Project</label>
                                <select
                                    v-model="selectedProjectId"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                >
                                    <option :value="null">Select a project...</option>
                                    <option v-for="project in available_projects" :key="project.id" :value="project.id">
                                        {{ project.name }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="selectedProjectId" class="space-y-2">
                                <label class="text-sm font-medium">Select Task</label>
                                <select
                                    v-model="selectedTaskId"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                >
                                    <option :value="null">Select a task...</option>
                                    <option
                                        v-for="task in available_projects?.find(p => p.id === selectedProjectId)?.tasks"
                                        :key="task.id"
                                        :value="task.id"
                                    >
                                        {{ task.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Button
                            class="w-full md:w-auto"
                            size="lg"
                            @click="startWorking"
                            :disabled="!selectedTaskId || startForm.processing"
                        >
                            <Play class="mr-2 h-5 w-5" />
                            Start Working
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
