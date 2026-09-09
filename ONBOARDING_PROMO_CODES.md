# Onboarding promo codes (WWA + CarServices)

Business signup can redeem a **free posts** promo code. Credits activate promoted / featured / sponsored posts without payment.

## Deploy (API)

```bash
cd ~/domains/api.worldwideadverts.info/laravel
git pull origin main
php artisan migrate --force
php artisan db:seed --class=OnboardingPromoCodeSeeder --force
php artisan optimize:clear
```

## Seeded codes

| Code | Platform | Grants |
|------|----------|--------|
| `WWA-WELCOME` | WWA | 1 promoted + 1 featured + 1 sponsored (7 days each) |
| `WWA-PROMO` | WWA | 1 promoted |
| `WWA-FEATURED` | WWA | 1 featured |
| `WWA-SPONSORED` | WWA | 1 sponsored |
| `CSL-WELCOME` | CarServices | 1 of each |
| `CSL-PROMO` / `CSL-FEATURED` / `CSL-SPONSORED` | CarServices | 1 of that type |

Manage in Filament → **Marketing & Ads → Reward Codes** (`type = free_posts`, `purpose = onboarding`).

## API

- `POST /api/v1/promo/codes/validate-onboarding` `{ code, platform: "wwa"|"carservices" }`
- `POST /api/v1/auth/register` business signup fields: `onboarding_promo_code` (or `promo_code`), `signup_platform`
- `GET /api/v1/promo/my-credits` (JWT) — remaining credits
- Creating promoted / featured / sponsored listings auto-consumes a matching credit when available

## CarServices Ltd

Same API. Pass `signup_platform: "carservices"` (or `platform`) and a `CSL-*` code on business register.
