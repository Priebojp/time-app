<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, Plus } from 'lucide-vue-next';
import CreateCompanyModal from '@/components/CreateCompanyModal.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { edit, index } from '@/routes/companies';
import type { Company } from '@/types';

type Props = {
    companies: Company[];
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Companies',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Companies" />

    <h1 class="sr-only">Companies</h1>

    <div class="flex flex-col space-y-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Companies"
                description="Manage your companies and company memberships"
            />

            <CreateCompanyModal>
                <Button data-test="companies-new-company-button">
                    <Plus /> New company
                </Button>
            </CreateCompanyModal>
        </div>

        <div class="space-y-3">
            <div
                v-for="company in companies"
                :key="company.id"
                data-test="company-row"
                class="flex items-center justify-between rounded-lg border p-4"
            >
                <div class="flex items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ company.name }}</span>
                            <Badge v-if="company.isPersonal" variant="secondary">
                                Personal
                            </Badge>
                        </div>
                        <span class="text-sm text-muted-foreground">
                            {{ company.roleLabel }}
                        </span>
                    </div>
                </div>

                <TooltipProvider>
                    <div class="flex items-center gap-2">
                        <Tooltip v-if="company.role === 'member'">
                            <TooltipTrigger as-child>
                                <Button
                                    data-test="company-view-button"
                                    variant="ghost"
                                    size="sm"
                                    as-child
                                >
                                    <Link :href="edit(company.slug)">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>View company</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip v-else>
                            <TooltipTrigger as-child>
                                <Button
                                    data-test="company-edit-button"
                                    variant="ghost"
                                    size="sm"
                                    as-child
                                >
                                    <Link :href="edit(company.slug)">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Edit company</p>
                            </TooltipContent>
                        </Tooltip>
                    </div>
                </TooltipProvider>
            </div>

            <p
                v-if="companies.length === 0"
                class="py-8 text-center text-muted-foreground"
            >
                You don't belong to any companies yet.
            </p>
        </div>
    </div>
</template>
