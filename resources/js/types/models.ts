import type { Company } from './companies';

export interface Position {
    id: number;
    company_id: number;
    name: string;
    description: string | null;
    created_at: string;
    updated_at: string;
    company?: Company;
}

export interface Client {
    id: number;
    company_id: number;
    name: string;
    contact_email: string | null;
    address: string | null;
    created_at: string;
    updated_at: string;
    company?: Company;
}

export interface Project {
    id: number;
    client_id: number;
    name: string;
    description: string | null;
    total_budget: string | null;
    monthly_budget: string | null;
    start_date: string | null;
    end_date: string | null;
    status: 'active' | 'archived' | 'completed';
    created_at: string;
    updated_at: string;
    client?: Client;
    tasks?: Task[];
}

export interface Task {
    id: number;
    project_id: number;
    name: string;
    description: string | null;
    due_date: string | null;
    status: 'todo' | 'in_progress' | 'review' | 'done';
    created_at: string;
    updated_at: string;
    project?: Project;
}

export interface HourlyRate {
    id: number;
    user_id: number;
    company_id: number;
    rate: string;
    currency: string;
    valid_from: string;
    valid_to: string | null;
    created_at: string;
    updated_at: string;
}

export interface TimeEntry {
    id: number;
    user_id: number;
    task_id: number;
    started_at: string;
    stopped_at: string | null;
    duration_seconds: number;
    note: string | null;
    is_running: boolean;
    created_at: string;
    updated_at: string;
    task?: Task;
}

export interface Issue {
    id: number;
    company_id: number;
    project_id: number | null;
    reporter_id: number;
    assignee_id: number | null;
    title: string;
    description: string | null;
    status: 'todo' | 'in_progress' | 'review' | 'done';
    priority: 'low' | 'medium' | 'high' | 'critical';
    order_index: number;
    created_at: string;
    updated_at: string;
    project?: Project;
    reporter?: { id: number; name: string; avatar?: string };
    assignee?: { id: number; name: string; avatar?: string };
}
