# Teams & Roles — Hostinger deploy

## Backend (api.worldwideadverts.info)

```bash
cd ~/domains/api.worldwideadverts.info/public_html   # or your Laravel root
git pull
php artisan migrate --force
php artisan db:seed --class=TeamRoleSeeder
php artisan cache:clear
php artisan config:clear
php artisan filament:cache-components   # if available
```

## What you get in Filament (`/admin`)

1. **User Management → Teams & Roles** (`/admin/teams-roles`)
   - 9 department teams + sub-roles (seeded)
   - Edit a **team** → Sub-roles tab + Members
   - Edit a **sub-role** → Members tab (Assign user syncs `can_*` flags)
2. **Users** → assign **Team / Sub-role** (grouped select); permissions sync from role on save
3. **View user** → **User Dashboard** section + **Open user dashboard**
   - Full page: `/admin/users/{id}/dashboard`

## Frontend

- Sidebar **Teams & Roles** opens Filament URL (`REACT_APP_ADMIN_URL` + `/admin/teams-roles`)
- `/admin/roles` shows CTA page linking to the same portal

Optional env:

```
REACT_APP_ADMIN_URL=https://api.worldwideadverts.info
```

## Smoke test

1. Login Filament as Super Admin
2. Open Teams & Roles — confirm HR, Accountants, Legal, etc.
3. Users → edit a staff user → set e.g. `HR / Payroll Admin` → save → check `can_*` toggles
4. View that user → Open user dashboard → listing/affiliate counts load
