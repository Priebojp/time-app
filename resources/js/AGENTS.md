# Frontend AI Instructions

## Scope
These rules apply to everything in `resources/js`.

## Frontend goal
Build a clean and fast UI for:
- time tracking
- projects and tasks
- company and team management
- assignments to users and positions
- kanban / issue board workflows
- deadlines, timelines, and work planning
- client-facing project overview data where needed

## UI principles
- Keep screens simple and task-focused.
- Make the most common actions obvious.
- Optimize for quick daily use.
- Favor clarity over visual complexity.
- Use consistent spacing, labels, and status indicators.
- Prefer reusable components over one-off UI blocks.

## Inertia + Vue rules
- Follow the existing Inertia + Vue conventions in the project.
- Use page components, layouts, and shared UI consistently.
- Prefer typed, predictable props and explicit component APIs.
- Use the project’s existing routing and navigation patterns.
- Keep forms and navigation aligned with the current app structure.
- Use loading states, empty states, and error states where appropriate.

## Time tracking UX
- Make start / pause / stop actions easy to reach.
- Show current running time clearly.
- Show assigned project, task, and position clearly.
- Show recent time entries and current work status at a glance.
- Keep the flow for employees as short as possible.

## Board / kanban UX
- Keep columns and card content easy to scan.
- Show status, assignee, priority, and due date clearly.
- Use drag-and-drop only if it remains simple and reliable.
- Prefer useful board metadata over decorative UI.

## Data display rules
- Use readable tables and cards for project, user, and company data.
- Use filters when lists can grow large.
- Use badges, chips, and small status labels for quick recognition.
- Avoid cluttering the screen with too many actions at once.

## Component rules
- Reuse existing components before creating new ones.
- Keep components small and focused.
- Prefer composition over duplication.
- Avoid deeply nested component trees unless necessary.

## Quality rules
- Make UI changes accessible and understandable.
- Handle loading, empty, and error states explicitly.
- Keep layouts responsive and usable on smaller screens.
- Do not introduce unnecessary visual complexity.
