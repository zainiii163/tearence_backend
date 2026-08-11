# Demo business logins (category dashboards)

Password for all: `Dashboard@123`

Use **Business** account type on the login screen.

| Category | Email |
|----------|-------|
| Vehicles | `vehicles-demo@worldwideadverts.info` |
| Funding | `funding-demo@worldwideadverts.info` |
| Property | `property-demo@worldwideadverts.info` |
| Jobs | `jobs-demo@worldwideadverts.info` |
| Buy & Sell | `buy-sell-demo@worldwideadverts.info` |
| Business | `business-demo@worldwideadverts.info` |
| Services | `services-demo@worldwideadverts.info` |
| Software | `software-demo@worldwideadverts.info` |
| Events | `events-demo@worldwideadverts.info` |
| Adverts | `adverts-demo@worldwideadverts.info` |
| Stores | `stores-demo@worldwideadverts.info` |
| Books | `books-demo@worldwideadverts.info` |
| Donations | `donations-demo@worldwideadverts.info` |
| Images | `images-demo@worldwideadverts.info` |
| Classifieds | `classifieds-demo@worldwideadverts.info` |
| Affiliate | `affiliate-demo@worldwideadverts.info` |
| Resorts | `resorts-demo@worldwideadverts.info` |
| Investment | `investment-demo@worldwideadverts.info` |

## Seed / refresh on API server (recommended)

```bash
cd ~/domains/api.worldwideadverts.info/public_html
git pull origin main
php artisan db:seed --class=BusinessCategoryDashboardUserSeeder --force
```

This creates/updates real `customer` + `customer_business` rows with `user_type=business` and `business_category` set.
