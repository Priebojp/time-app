<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
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
import positionRoutes from '@/routes/positions';
import type { Position, Team } from '@/types';


const props = defineProps<{
    positions: Array<Position>;
    currentTeam: Team;
}>();

defineOptions({
    layout: (props: { currentTeam: Team }) => ({
        breadcrumbs: [
            {
                title: 'Positions',
                href: positionRoutes.index.url({
                    current_team: props.currentTeam.slug,
                }),
            },
        ],
    }),
});

const isDialogOpen = ref(false);
const editingPosition = ref<Position | null>(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreateDialog = () => {
    editingPosition.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (position: Position) => {
    editingPosition.value = position;
    form.name = position.name;
    form.description = position.description || '';
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingPosition.value) {
        form.patch(
            positionRoutes.update.url({
                current_team: props.currentTeam.slug,
                position: editingPosition.value.id,
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
            positionRoutes.store.url({ current_team: props.currentTeam.slug }),
            {
                onSuccess: () => {
                    isDialogOpen.value = false;
                    form.reset();
                },
            },
        );
    }
};

const deletePosition = (id: number) => {
    if (confirm('Are you sure you want to delete this position?')) {
        form.delete(
            positionRoutes.destroy.url({
                current_team: props.currentTeam.slug,
                position: id,
            }),
        );
    }
};
</script>

<template>
    <Head title="Positions" />

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Positions</h1>
            <Button @click="openCreateDialog">
                <Plus class="w-4 h-4 mr-2" />
                Add Position
            </Button>
        </div>

        <div class="bg-card rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted/50 text-muted-foreground font-medium border-b text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="position in positions" :key="position.id" class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ position.name }}</td>
                        <td class="px-6 py-4 text-muted-foreground">{{ position.description || '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="icon-sm" @click="openEditDialog(position)">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button variant="ghost" size="icon-sm" class="text-destructive hover:text-destructive" @click="deletePosition(position.id)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="positions.length === 0">
                        <td colspan="3" class="px-6 py-10 text-center text-muted-foreground">
                            No positions found. Add your first position to get started.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingPosition ? 'Edit Position' : 'Add Position' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingPosition ? 'Update the details of this position.' : 'Create a new job role for your company.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" required placeholder="e.g. Senior Developer" />
                        <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Input id="description" v-model="form.description" placeholder="Optional description of the role" />
                        <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDialogOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingPosition ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
