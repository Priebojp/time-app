<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    Briefcase,
    Users,
    Kanban,
    BarChart3,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import CompanySwitcher from '@/components/CompanySwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import clients from '@/routes/clients';
import issues from '@/routes/issues';
import positions from '@/routes/positions';
import projects from '@/routes/projects';
import reports from '@/routes/reports';
import type { NavItem } from '@/types';

const page = usePage();

const dashboardUrl = computed(() =>
    page.props.currentCompany
        ? dashboard.url({
              current_company: (page.props.currentCompany as any).slug,
          })
        : '/',
);

const mainNavItems = computed<NavItem[]>(() => {
    const slug = page.props.currentCompany?.slug;

    if (!slug) {
        return [
            {
                title: 'Dashboard',
                href: dashboardUrl.value,
                icon: LayoutGrid,
            },
        ];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboardUrl.value,
            icon: LayoutGrid,
        },
        {
            title: 'Positions',
            href: positions.index.url({ current_company: slug }),
            icon: Briefcase,
        },
        {
            title: 'Clients',
            href: clients.index.url({ current_company: slug }),
            icon: Users,
        },
        {
            title: 'Projects',
            href: projects.index.url({ current_company: slug }),
            icon: FolderGit2,
        },
        {
            title: 'Kanban',
            href: issues.index.url({ current_company: slug }),
            icon: Kanban,
        },
        {
            title: 'Reports',
            href: reports.index.url({ current_company: slug }),
            icon: BarChart3,
        },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarMenu>
                <SidebarMenuItem>
                    <CompanySwitcher />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
