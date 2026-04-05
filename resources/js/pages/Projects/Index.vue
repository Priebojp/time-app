<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import projectRoutes from '@/routes/projects';
import type { Client, Project, Team } from '@/types';

const props = defineProps<{
    projects: Project[];
    clients: Client[];
    currentTeam: Team;
}>();

defineOptions({
    layout: (props: { currentTeam: Team }) => ({
        breadcrumbs: [
            {
                title: 'Projects',
                href: projectRoutes.index.url({
                    current_team: props.currentTeam.slug,
                }),
            },
        ],
    }),
});

const isDialogOpen = ref(false);
const searchQuery = ref('');

const form = useForm({
    client_id: '',
    name: '',
    description: '',
    total_budget: '',
    monthly_budget: '',
    start_date: '',
    end_date: '',
});

const filteredProjects = computed(() => {
    return props.projects.filter(
        (project) =>
            project.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            project.client?.name
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()),
    );
});

const openCreateDialog = () => {
    form.reset();
    isDialogOpen.value = true;
};

const submit = () => {
    form.post(
        projectRoutes.store.url({ current_team: props.currentTeam.slug }),
        {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        },
    );
};
</script>

<template>
    <Head title="Projects" />

    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Projects</h1>
            <Button @click="openCreateDialog">
                <Plus class="mr-2 h-4 w-4" />
                New Project
            </Button>
        </div>

        <div class="mb-6 flex gap-4">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Search projects..."
                    class="pl-9"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="project in filteredProjects"
                :key="project.id"
                :href="
                    projectRoutes.show.url({
                        current_team: props.currentTeam.slug,
                        project: project.id,
                    })
                "
                class="group flex flex-col rounded-lg border bg-card p-6 shadow-sm transition-colors hover:border-primary/50"
            >
                <div class="mb-2 flex items-start justify-between">
                    <span
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ project.client?.name }}
                    </span>
                    <div
                        :class="[
                            'rounded-full px-2 py-0.5 text-[10px] font-bold uppercase',
                            project.status === 'active'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-muted text-muted-foreground',
                        ]"
                    >
                        {{ project.status }}
                    </div>
                </div>

                <h3
                    class="mb-2 text-lg font-bold transition-colors group-hover:text-primary"
                >
                    {{ project.name }}
                </h3>

                <p class="mb-4 line-clamp-2 text-sm text-muted-foreground">
                    {{ project.description || 'No description provided.' }}
                </p>

                <div
                    class="mt-auto flex items-center justify-between border-t pt-4 text-xs text-muted-foreground"
                >
                    <div v-if="project.total_budget">
                        Budget: ${{ project.total_budget }}
                    </div>
                    <div v-if="project.end_date">
                        Due:
                        {{ new Date(project.end_date).toLocaleDateString() }}
                    </div>
                </div>
            </Link>

            <div
                v-if="filteredProjects.length === 0"
                class="col-span-full rounded-lg border-2 border-dashed py-20 text-center"
            >
                <p class="text-muted-foreground">
                    No projects found. Create one to start tracking time.
                </p>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Create Project</DialogTitle>
                    <DialogDescription>
                        Set up a new project for a client.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 space-y-2">
                            <Label>Client</Label>
                            <Select v-model="form.client_id">
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Select a client"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.id.toString()"
                                    >
                                        {{ client.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div
                                v-if="form.errors.client_id"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.client_id }}
                            </div>
                        </div>

                        <div class="col-span-2 space-y-2">
                            <Label for="name">Project Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                required
                                placeholder="e.g. Website Redesign"
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="col-span-2 space-y-2">
                            <Label for="description">Description</Label>
                            <Input
                                id="description"
                                v-model="form.description"
                                placeholder="Project goals or scope"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="total_budget">Total Budget</Label>
                            <Input
                                id="total_budget"
                                type="number"
                                v-model="form.total_budget"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="monthly_budget">Monthly Budget</Label>
                            <Input
                                id="monthly_budget"
                                type="number"
                                v-model="form.monthly_budget"
                                placeholder="0.00"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="start_date">Start Date</Label>
                            <Input
                                id="start_date"
                                type="date"
                                v-model="form.start_date"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="end_date">End Date</Label>
                            <Input
                                id="end_date"
                                type="date"
                                v-model="form.end_date"
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="form.processing"
                            >Create Project</Button
                        >
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
