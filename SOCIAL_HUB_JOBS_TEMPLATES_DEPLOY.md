# Backend align — Social Hub + Jobs templates

## Hostinger

```bash
cd ~/path/to/laravel   # api.worldwideadverts.info root
git pull
php artisan db:seed --class=BusinessTemplateSeeder --force
php artisan cache:clear
php artisan config:clear
```

## What changed

1. **Jobs templates** — `BusinessTemplateSeeder` now seeds vertical `jobs` (resume, CV, cover letter, JD, offer letter, scorecard). HTML lives in `public/templates/`. Filament Templates resource includes **Jobs** vertical.
2. **Social Hub labels** — Filament user dashboard stats + Community API success messages say Social Hub (routes stay `/communities`).

## Smoke test

- Filament → Templates → filter vertical **Jobs** → 7 catalog packs
- `GET /api/v1/business-templates?vertical=jobs` returns resume/CV packs
- Open `/templates/professional-resume.html` on the API host
