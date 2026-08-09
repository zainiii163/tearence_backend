# Production ops checklist (Clive) — run on each app server / LB pool
#
# 1) Redis (cache + queue + session)
#    CACHE_DRIVER=redis
#    QUEUE_CONNECTION=redis
#    SESSION_DRIVER=redis
#    Then: php artisan config:cache && php artisan queue:work redis --tries=3 --sleep=1
#    (or Supervisor program for queue:work / multiple workers behind LB)
#
# 2) MySQL replica (optional HA)
#    DB_READ_HOST=replica.internal
#    Writes stay on DB_HOST; reads use replica when set (config/database.php sticky).
#
# 3) Object storage / CDN for media
#    FILESYSTEM_DISK=s3
#    AWS_* + MEDIA_CDN_URL=https://cdn.example.com
#    FRONTEND: REACT_APP_STORAGE_URL=https://cdn.example.com
#
# 4) Load balancer
#    Point health checks at GET /api/v1/health (returns 503 when DB/cache degraded).
#    Enable sticky sessions only if SESSION_DRIVER is not redis/shared.
#
# 5) Nginx
#    client_max_body_size 64m; (see nginx-production.conf)
#
# 6) Signup / KYC
#    Signup does not require OTP. Email verify before first post (middleware verified.to.post).
#    KYC via /api/v1/user/kyc-* after first post.
#
