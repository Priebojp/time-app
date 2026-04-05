<?php

namespace App\Enums;

enum CompanyPermission: string
{
    case UpdateCompany = 'company:update';
    case DeleteCompany = 'company:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    case CreatePositions = 'positions:create';
    case UpdatePositions = 'positions:update';
    case DeletePositions = 'positions:delete';

    case CreateClients = 'clients:create';
    case UpdateClients = 'clients:update';
    case DeleteClients = 'clients:delete';

    case CreateProjects = 'projects:create';
    case UpdateProjects = 'projects:update';
    case DeleteProjects = 'projects:delete';

    case CreateTasks = 'tasks:create';
    case UpdateTasks = 'tasks:update';
    case DeleteTasks = 'tasks:delete';

    case CreateHourlyRates = 'hourly_rates:create';
    case UpdateHourlyRates = 'hourly_rates:update';
    case DeleteHourlyRates = 'hourly_rates:delete';

    case CreateIssues = 'issues:create';
    case UpdateIssues = 'issues:update';
    case DeleteIssues = 'issues:delete';
}
