regenerate:
	php artisan optimize && php artisan l5-swagger:generate

start:
	php artisan optimize && php artisan l5-swagger:generate && php artisan serve

migrate:
	php artisan migrate

migrate.fresh:
	php artisan migrate:fresh --seed

migrate.alter:
	php artisan make:migration ${name} --table=${table}

migrate.create:
	php artisan make:migration ${name}

pull:
	git reset --hard && git pull && php artisan optimize && php artisan l5-swagger:generate

deploy:
	php -r "file_exists('.env') || copy('.env.example', '.env');" &&
	php artisan jwt:secret &&
	php artisan key:generate &&
	php artisan cache:clear &&
	php artisan view:clear &&
	php artisan package:discover

controller.create:
	php artisan make:controller ${controllerName} --model=${modelName} --api

brew.link:
	brew unlink php@7.4 && brew link --force php@8.1

push:
	ssh -p 65002 u235482616@154.41.236.83