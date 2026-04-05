<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import reports from '@/routes/reports';
import type { Team } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BarChart3, Clock, DollarSign, Briefcase } from 'lucide-vue-next';

const props = defineProps<{
    projectReports: {
        project_id: number;
        project_name: string;
        client_name: string;
        total_hours: number;
        total_budget: string | null;
        status: string;
    }[];
    currentTeam: Team;
}>();

defineOptions({
    layout: (props: { currentTeam: Team }) => ({
        breadcrumbs: [
            {
                title: 'Reports',
                href: reports.index(props.currentTeam.slug),
            },
        ],
    }),
});

const totalHours = props.projectReports.reduce((sum, report) => sum + report.total_hours, 0);
const activeProjects = props.projectReports.filter(r => r.status === 'active').length;
</script>

<template>
    <Head title="Reports" />

    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Reports & Analytics</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase flex items-center">
                        <Clock class="w-4 h-4 mr-2" />
                        Total Hours Tracked
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ totalHours.toFixed(1) }}h</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase flex items-center">
                        <Briefcase class="w-4 h-4 mr-2" />
                        Active Projects
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ activeProjects }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase flex items-center">
                        <BarChart3 class="w-4 h-4 mr-2" />
                        Avg. Project Load
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ props.projectReports.length > 0 ? (totalHours / props.projectReports.length).toFixed(1) : '0' }}h
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="bg-card rounded-lg border shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-muted/30 font-semibold">Project Performance</div>
            <table class="w-full text-sm text-left">
                <thead class="bg-muted/50 text-muted-foreground font-medium border-b text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Project</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Total Hours</th>
                        <th class="px-6 py-3 text-right">Budget</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="report in projectReports" :key="report.project_id" class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 font-medium">{{ report.project_name }}</td>
                        <td class="px-6 py-4 text-muted-foreground">{{ report.client_name }}</td>
                        <td class="px-6 py-4 text-center">
                            <Badge variant="outline" class="text-[10px] uppercase">{{ report.status }}</Badge>
                        </td>
                        <td class="px-6 py-4 text-right font-mono">{{ report.total_hours.toFixed(1) }}h</td>
                        <td class="px-6 py-4 text-right font-mono">${{ report.total_budget || '0.00' }}</td>
                    </tr>
                    <tr v-if="projectReports.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-muted-foreground italic">
                            No data available for reports yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
