<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Calendar,
    ChevronLeft,
    Clock,
    DollarSign,
    Play,
    Plus,
    CheckCircle2,
    Circle,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import projects from '@/routes/projects';
import tasks from '@/routes/tasks';
import timeEntries from '@/routes/time-entries';
import type { Project, Company, Task } from '@/types';

const props = defineProps<{
    project: Project & { tasks: (Task & { users: any[] })[] };
    currentCompany: Company;
}>();

defineOptions({
    layout: (props: { currentCompany: Company; project: Project }) => ({
        breadcrumbs: [
            {
                title: 'Projects',
                href: projects.index.url({
                    current_company: props.currentCompany.slug,
                }),
            },
            {
                title: props.project.name,
                href: projects.show.url({
                    current_company: props.currentCompany.slug,
                    project: props.project.id,
                }),
            },
        ],
    }),
});

const isTaskDialogOpen = ref(false);
const taskForm = useForm({
    project_id: props.project.id,
    name: '',
    description: '',
    due_date: '',
});

const openTaskDialog = () => {
    taskForm.reset();
    isTaskDialogOpen.value = true;
};

const submitTask = () => {
    taskForm.post(
        tasks.store.url({ current_company: props.currentCompany.slug }),
        {
            onSuccess: () => {
                isTaskDialogOpen.value = false;
                taskForm.reset();
            },
        },
    );
};

const toggleTaskStatus = (task: Task) => {
    const newStatus = task.status === 'done' ? 'todo' : 'done';
    useForm({ status: newStatus }).patch(
        tasks.update.url({
            current_company: props.currentCompany.slug,
            task: task.id,
        }),
    );
};

const startTimer = (taskId: number) => {
    useForm({ task_id: taskId }).post(
        timeEntries.start.url({ current_company: props.currentCompany.slug }),
    );
};

const deleteTask = (taskId: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        useForm({}).delete(
            tasks.destroy.url({
                current_company: props.currentCompany.slug,
                task: taskId,
            }),
        );
    }
};
</script>

<template>
    <Head :title="project.name" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <a
                        :href="
                            projects.index.url({
                                current_company: currentCompany.slug,
                            })
                        "
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </a>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">{{ project.name }}</h1>
                    <p class="text-muted-foreground">
                        {{ project.client?.name }}
                    </p>
                </div>
            </div>
            <Badge
                :variant="project.status === 'active' ? 'default' : 'secondary'"
                class="uppercase"
            >
                {{ project.status }}
            </Badge>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center text-sm font-medium text-muted-foreground uppercase"
                    >
                        <DollarSign class="mr-2 h-4 w-4" />
                        Budget
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        ${{ project.total_budget || '0.00' }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        ${{ project.monthly_budget || '0.00' }} / month
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center text-sm font-medium text-muted-foreground uppercase"
                    >
                        <Calendar class="mr-2 h-4 w-4" />
                        Timeline
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-sm font-medium">
                        {{
                            project.start_date
                                ? new Date(
                                      project.start_date,
                                  ).toLocaleDateString()
                                : 'No start'
                        }}
                        -
                        {{
                            project.end_date
                                ? new Date(
                                      project.end_date,
                                  ).toLocaleDateString()
                                : 'No end'
                        }}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle
                        class="flex items-center text-sm font-medium text-muted-foreground uppercase"
                    >
                        <Clock class="mr-2 h-4 w-4" />
                        Logged Time
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">0.0h</div>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Tasks</h2>
                <Button size="sm" @click="openTaskDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Task
                </Button>
            </div>

            <div class="divide-y rounded-lg border bg-card shadow-sm">
                <div
                    v-for="task in project.tasks"
                    :key="task.id"
                    class="group flex items-center justify-between p-4 transition-colors hover:bg-muted/30"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="toggleTaskStatus(task)"
                            class="text-muted-foreground transition-colors hover:text-primary"
                        >
                            <CheckCircle2
                                v-if="task.status === 'done'"
                                class="h-6 w-6 text-green-500"
                            />
                            <Circle v-else class="h-6 w-6" />
                        </button>
                        <div>
                            <h4
                                :class="[
                                    'font-medium',
                                    task.status === 'done'
                                        ? 'text-muted-foreground line-through'
                                        : '',
                                ]"
                            >
                                {{ task.name }}
                            </h4>
                            <p
                                v-if="task.due_date"
                                class="mt-1 flex items-center text-xs text-muted-foreground"
                            >
                                <Calendar class="mr-1 h-3 w-3" />
                                Due:
                                {{
                                    new Date(task.due_date).toLocaleDateString()
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="task.status !== 'done'"
                            variant="outline"
                            size="sm"
                            @click="startTimer(task.id)"
                        >
                            <Play class="mr-2 h-3 w-3" />
                            Start Timer
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            class="text-destructive opacity-0 transition-opacity group-hover:opacity-100"
                            @click="deleteTask(task.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <div
                    v-if="project.tasks.length === 0"
                    class="p-8 text-center text-muted-foreground"
                >
                    No tasks found. Create a task to start tracking time.
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:open="isTaskDialogOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add Task</DialogTitle>
                <DialogDescription
                    >Create a new task for this project.</DialogDescription
                >
            </DialogHeader>

            <form @submit.prevent="submitTask" class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="task_name">Task Name</Label>
                    <Input
                        id="task_name"
                        v-model="taskForm.name"
                        required
                        placeholder="e.g. Design homepage"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="task_description">Description</Label>
                    <Input
                        id="task_description"
                        v-model="taskForm.description"
                        placeholder="Optional details"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="due_date">Due Date</Label>
                    <Input
                        id="due_date"
                        type="date"
                        v-model="taskForm.due_date"
                    />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="isTaskDialogOpen = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="taskForm.processing"
                        >Create Task</Button
                    >
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
