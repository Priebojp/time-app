<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Check, ChevronsUpDown, Plus, Users } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CreateCompanyModal from '@/components/CreateCompanyModal.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { switchMethod } from '@/routes/companies';
import type { Company } from '@/types';

const props = withDefaults(
    defineProps<{
        inHeader?: boolean;
    }>(),
    {
        inHeader: false,
    },
);

const page = usePage();
const isMobile = ref(false);
let mediaQuery: MediaQueryList | null = null;
const updateIsMobile = () => {
    if (mediaQuery) {
        isMobile.value = mediaQuery.matches;
    }
};

const currentCompany = computed(() => page.props.currentCompany);
const companies = computed(() => page.props.companies ?? []);
const menuContentClass = computed(() =>
    props.inHeader
        ? 'w-56'
        : 'w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg',
);
const companyItemClass = computed(() =>
    props.inHeader ? 'cursor-pointer gap-2' : 'cursor-pointer gap-2 p-2',
);
const checkIconClass = computed(() =>
    props.inHeader ? 'ml-auto size-4' : 'ml-auto h-4 w-4',
);
const plusIconClass = computed(() => (props.inHeader ? 'size-4' : 'h-4 w-4'));

const switchCompany = (company: Company) => {
    const previousCompanySlug = currentCompany.value?.slug;

    router.visit(switchMethod(company.slug), {
        onFinish: () => {
            if (!previousCompanySlug || typeof window === 'undefined') {
                router.reload();

                return;
            }

            const currentUrl = `${window.location.pathname}${window.location.search}${window.location.hash}`;
            const segment = `/${previousCompanySlug}`;

            if (currentUrl.includes(segment)) {
                router.visit(currentUrl.replace(segment, `/${company.slug}`), {
                    replace: true,
                });

                return;
            }

            router.reload();
        },
    });
};

onMounted(() => {
    mediaQuery = window.matchMedia('(max-width: 767px)');
    updateIsMobile();
    mediaQuery.addEventListener('change', updateIsMobile);
});

onUnmounted(() => {
    mediaQuery?.removeEventListener('change', updateIsMobile);
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                data-test="company-switcher-trigger"
                variant="ghost"
                :class="
                    props.inHeader
                        ? 'h-8 gap-1 px-2'
                        : 'w-full justify-start px-2 has-[>svg]:px-2 data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground'
                "
            >
                <Users
                    :class="
                        props.inHeader
                            ? 'hidden'
                            : 'hidden size-4 shrink-0 group-data-[collapsible=icon]:block'
                    "
                />
                <div
                    :class="
                        props.inHeader
                            ? 'grid flex-1 text-left text-sm leading-tight'
                            : 'grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden'
                    "
                >
                    <span
                        :class="
                            props.inHeader
                                ? 'max-w-[120px] truncate font-medium'
                                : 'truncate font-semibold'
                        "
                    >
                        {{ currentCompany?.name ?? 'Select company' }}
                    </span>
                </div>
                <ChevronsUpDown
                    :class="
                        props.inHeader
                            ? 'size-4 opacity-50'
                            : 'ml-auto group-data-[collapsible=icon]:hidden'
                    "
                />
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
            :class="menuContentClass"
            :side="props.inHeader ? undefined : isMobile ? 'bottom' : 'right'"
            :align="props.inHeader ? 'end' : 'start'"
            :side-offset="props.inHeader ? undefined : 4"
        >
            <DropdownMenuLabel class="text-xs text-muted-foreground">
                Companies
            </DropdownMenuLabel>
            <DropdownMenuItem
                v-for="company in companies"
                :key="company.id"
                data-test="company-switcher-item"
                :class="companyItemClass"
                @click="switchCompany(company)"
            >
                {{ company.name }}
                <Check
                    v-if="currentCompany?.id === company.id"
                    :class="checkIconClass"
                />
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <CreateCompanyModal>
                <DropdownMenuItem
                    data-test="company-switcher-new-company"
                    :class="companyItemClass"
                    @select.prevent
                >
                    <Plus :class="plusIconClass" />
                    <span class="text-muted-foreground">New company</span>
                </DropdownMenuItem>
            </CreateCompanyModal>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
