<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Mail, MapPin } from 'lucide-vue-next';
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
import clientRoutes from '@/routes/clients';
import type { Client, Team } from '@/types';

const props = defineProps<{
    clients: Client[];
    currentTeam: Team;
}>();

defineOptions({
    layout: (props: { currentTeam: Team }) => ({
        breadcrumbs: [
            {
                title: 'Clients',
                href: clientRoutes.index.url({
                    current_team: props.currentTeam.slug,
                }),
            },
        ],
    }),
});

const isDialogOpen = ref(false);
const editingClient = ref<Client | null>(null);

const form = useForm({
    name: '',
    contact_email: '',
    address: '',
});

const openCreateDialog = () => {
    editingClient.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEditDialog = (client: Client) => {
    editingClient.value = client;
    form.name = client.name;
    form.contact_email = client.contact_email || '';
    form.address = client.address || '';
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingClient.value) {
        form.patch(
            clientRoutes.update.url({
                current_team: props.currentTeam.slug,
                client: editingClient.value.id,
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
            clientRoutes.store.url({ current_team: props.currentTeam.slug }),
            {
                onSuccess: () => {
                    isDialogOpen.value = false;
                    form.reset();
                },
            },
        );
    }
};

const deleteClient = (id: number) => {
    if (confirm('Are you sure you want to delete this client?')) {
        form.delete(
            clientRoutes.destroy.url({
                current_team: props.currentTeam.slug,
                client: id,
            }),
        );
    }
};
</script>

<template>
    <Head title="Clients" />

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Clients</h1>
            <Button @click="openCreateDialog">
                <Plus class="w-4 h-4 mr-2" />
                Add Client
            </Button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="client in clients" :key="client.id" class="bg-card rounded-lg border shadow-sm p-6 flex flex-col hover:border-primary/50 transition-colors">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold leading-none">{{ client.name }}</h3>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="icon-sm" @click="openEditDialog(client)">
                            <Pencil class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="icon-sm" class="text-destructive hover:text-destructive" @click="deleteClient(client.id)">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <div class="space-y-3 mt-auto">
                    <div v-if="client.contact_email" class="flex items-center text-sm text-muted-foreground">
                        <Mail class="w-4 h-4 mr-2" />
                        {{ client.contact_email }}
                    </div>
                    <div v-if="client.address" class="flex items-start text-sm text-muted-foreground">
                        <MapPin class="w-4 h-4 mr-2 mt-0.5" />
                        {{ client.address }}
                    </div>
                </div>
            </div>

            <div v-if="clients.length === 0" class="col-span-full py-20 text-center border-2 border-dashed rounded-lg">
                <p class="text-muted-foreground">No clients found. Add your first client to start managing projects.</p>
                <Button variant="outline" class="mt-4" @click="openCreateDialog">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Client
                </Button>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingClient ? 'Edit Client' : 'Add Client' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingClient ? 'Update the details of this client.' : 'Create a new client to associate with projects.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Company Name</Label>
                        <Input id="name" v-model="form.name" required placeholder="e.g. Acme Corp" />
                        <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="contact_email">Contact Email</Label>
                        <Input id="contact_email" type="email" v-model="form.contact_email" placeholder="e.g. contact@acme.com" />
                        <div v-if="form.errors.contact_email" class="text-sm text-destructive">{{ form.errors.contact_email }}</div>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">Address</Label>
                        <Input id="address" v-model="form.address" placeholder="Physical or billing address" />
                        <div v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDialogOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingClient ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
