<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Plus,
    MoreVertical,
    AlertCircle,
    Clock,
    CheckCircle2,
    Layout,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
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
import { Textarea } from '@/components/ui/textarea';
import issueRoutes from '@/routes/issues';
import type { Issue, Project, Company, User } from '@/types';


const props = defineProps<{
    issues: Issue[];
    projects: Project[];
    members: User[];
    currentCompany: Company;
}>();

defineOptions({
    layout: (props: { currentCompany: Company }) => ({
        breadcrumbs: [
            {
                title: 'Kanban Board',
                href: issueRoutes.index.url({
                    current_company: props.currentCompany.slug,
                }),
            },
        ],
    }),
});

const statuses = [
    { id: 'todo', label: 'To Do', color: 'bg-slate-500' },
    { id: 'in_progress', label: 'In Progress', color: 'bg-blue-500' },
    { id: 'review', label: 'Review', color: 'bg-amber-500' },
    { id: 'done', label: 'Done', color: 'bg-green-500' },
] as const;

const issuesByStatus = computed(() => {
    const groups: Record<string, Issue[]> = {
        todo: [],
        in_progress: [],
        review: [],
        done: [],
    };
    props.issues.forEach((issue) => {
        if (groups[issue.status]) {
            groups[issue.status].push(issue);
        }
    });

    return groups;
});

const isDialogOpen = ref(false);
const editingIssue = ref<Issue | null>(null);

const form = useForm({
    title: '',
    description: '',
    project_id: '',
    assignee_id: '',
    status: 'todo',
    priority: 'medium',
});

const openCreateDialog = (status = 'todo') => {
    editingIssue.value = null;
    form.reset();
    form.status = status;
    isDialogOpen.value = true;
};

const openEditDialog = (issue: Issue) => {
    editingIssue.value = issue;
    form.title = issue.title;
    form.description = issue.description || '';
    form.project_id = issue.project_id?.toString() || '';
    form.assignee_id = issue.assignee_id?.toString() || '';
    form.status = issue.status;
    form.priority = issue.priority;
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingIssue.value) {
        form.patch(
            issueRoutes.update.url({
                current_company: props.currentCompany.slug,
                issue: editingIssue.value.id,
            }),
            {
                onSuccess: () => {
                    isDialogOpen.value = false;
                    form.reset();
                },
            },
        );
    } else {
        form.post(
            issueRoutes.store.url({ current_company: props.currentCompany.slug }),
            {
                onSuccess: () => {
                    isDialogOpen.value = false;
                    form.reset();
                },
            },
        );
    }
};

const deleteIssue = (id: number) => {
    if (confirm('Are you sure you want to delete this issue?')) {
        form.delete(
            issueRoutes.destroy.url({
                current_company: props.currentCompany.slug,
                issue: id,
            }),
        );
    }
};

const moveIssue = (issue: Issue, newStatus: string) => {
    useForm({
        ...issue,
        status: newStatus,
        project_id: issue.project_id?.toString() || '',
        assignee_id: issue.assignee_id?.toString() || '',
    }).patch(
        issueRoutes.update.url({
            current_company: props.currentCompany.slug,
            issue: issue.id,
        }),
    );
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'critical':
            return 'text-red-600 dark:text-red-400';
        case 'high':
            return 'text-orange-600 dark:text-orange-400';
        case 'medium':
            return 'text-blue-600 dark:text-blue-400';
        default:
            return 'text-slate-600 dark:text-slate-400';
    }
};
</script>

<template>
    <Head title="Kanban Board" />

    <div class="flex h-full flex-col overflow-hidden p-6">
        <div class="mb-6 flex shrink-0 items-center justify-between">
            <h1 class="text-2xl font-semibold">Operations Board</h1>
            <Button @click="openCreateDialog()">
                <Plus class="mr-2 h-4 w-4" />
                New Issue
            </Button>
        </div>

        <div class="scrollbar-hide flex h-full gap-6 overflow-x-auto pb-4">
            <div
                v-for="status in statuses"
                :key="status.id"
                class="flex w-80 flex-none flex-col rounded-xl border bg-muted/40 p-3"
            >
                <div class="mb-4 flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <div
                            :class="['h-2 w-2 rounded-full', status.color]"
                        ></div>
                        <h3
                            class="text-sm font-bold tracking-wider text-muted-foreground uppercase"
                        >
                            {{ status.label }}
                        </h3>
                        <Badge variant="secondary" class="ml-1 text-[10px]">{{
                            issuesByStatus[status.id].length
                        }}</Badge>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        @click="openCreateDialog(status.id)"
                    >
                        <Plus class="h-4 w-4" />
                    </Button>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                    <div
                        v-for="issue in issuesByStatus[status.id]"
                        :key="issue.id"
                        class="group relative cursor-pointer rounded-lg border bg-card p-4 shadow-sm transition-all hover:border-primary/40"
                        @click="openEditDialog(issue)"
                    >
                        <div
                            class="mb-2 flex items-start justify-between gap-2"
                        >
                            <h4
                                class="text-sm leading-tight font-medium transition-colors group-hover:text-primary"
                            >
                                {{ issue.title }}
                            </h4>
                            <div
                                :class="[
                                    'shrink-0',
                                    getPriorityColor(issue.priority),
                                ]"
                            >
                                <AlertCircle class="h-4 w-4" />
                            </div>
                        </div>

                        <p
                            v-if="issue.project"
                            class="mb-3 flex items-center text-[10px] font-bold text-muted-foreground uppercase"
                        >
                            <Layout class="mr-1 h-3 w-3" />
                            {{ issue.project.name }}
                        </p>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex -space-x-2">
                                <Avatar
                                    v-if="issue.assignee"
                                    class="h-6 w-6 border-2 border-card ring-0"
                                >
                                    <AvatarImage :src="issue.assignee.avatar" />
                                    <AvatarFallback class="text-[8px]">{{
                                        issue.assignee.name
                                            .substring(0, 2)
                                            .toUpperCase()
                                    }}</AvatarFallback>
                                </Avatar>
                                <div
                                    v-else
                                    class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/30 bg-muted"
                                >
                                    <Plus
                                        class="h-3 w-3 text-muted-foreground/50"
                                    />
                                </div>
                            </div>

                            <div class="text-[10px] text-muted-foreground">
                                {{
                                    new Date(
                                        issue.created_at,
                                    ).toLocaleDateString()
                                }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="issuesByStatus[status.id].length === 0"
                        class="flex h-24 items-center justify-center rounded-lg border-2 border-dashed text-xs text-muted-foreground/40 italic"
                    >
                        Empty column
                    </div>
                </div>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{
                        editingIssue ? 'Edit Issue' : 'Create New Issue'
                    }}</DialogTitle>
                    <DialogDescription
                        >Track operational problems, requests, or internal
                        tasks.</DialogDescription
                    >
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            required
                            placeholder="What needs to be fixed?"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Provide more context..."
                            class="min-h-[100px]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Project (Optional)</Label>
                            <Select v-model="form.project_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select project" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">None</SelectItem>
                                    <SelectItem
                                        v-for="project in projects"
                                        :key="project.id"
                                        :value="project.id.toString()"
                                    >
                                        {{ project.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Assignee</Label>
                            <Select v-model="form.assignee_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select member" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Unassigned</SelectItem>
                                    <SelectItem
                                        v-for="member in members"
                                        :key="member.id"
                                        :value="member.id.toString()"
                                    >
                                        {{ member.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="status in statuses"
                                        :key="status.id"
                                        :value="status.id"
                                    >
                                        {{ status.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label>Priority</Label>
                            <Select v-model="form.priority">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="low">Low</SelectItem>
                                    <SelectItem value="medium"
                                        >Medium</SelectItem
                                    >
                                    <SelectItem value="high">High</SelectItem>
                                    <SelectItem value="critical"
                                        >Critical</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <DialogFooter class="border-t pt-4">
                        <Button
                            v-if="editingIssue"
                            variant="destructive"
                            type="button"
                            @click="
                                useForm({}).delete(
                                    issueRoutes.destroy(
                                        currentCompany.slug,
                                        editingIssue.id,
                                    ),
                                )
                            "
                            class="mr-auto"
                        >
                            Delete
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="form.processing">
                            {{ editingIssue ? 'Update Issue' : 'Create Issue' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
