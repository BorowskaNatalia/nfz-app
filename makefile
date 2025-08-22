stan:
	docker compose run --rm app vendor/bin/phpstan analyse

fix:
	docker compose run --rm app vendor/bin/pint

rector:
	docker compose run --rm app vendor/bin/rector process
