@echo off
cd /d "%~dp0"
REM Raise upload limits for local dev (default PHP is often 2M/8M and causes HTTP 413)
php -d post_max_size=50M -d upload_max_filesize=50M -d max_execution_time=300 artisan serve --host=127.0.0.1 --port=8000 %*
