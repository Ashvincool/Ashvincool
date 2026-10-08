# Keshav Technosys CRM

Single-file CRM — open `crm/index.html` in a browser. No build or server needed.

- Dashboard: contact/company counts, open pipeline, won revenue, upcoming tasks
- Contacts, Companies: add/edit/delete, search, contacts CSV export
- Deals: drag-and-drop kanban (New → Qualified → Proposal → Negotiation → Won/Lost)
- Activities: calls, emails, meetings, notes, tasks with due dates and checkboxes
- Data is stored in the browser (localStorage). Use **Export backup / Import backup** to move or save it.

`database/crm_schema.sql` is the Postgres/Supabase schema for when you want a shared multi-user backend.
