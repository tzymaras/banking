docker-build-dev:
	@docker-compose -f docker-compose.dev.yml up \
		--build \
		--remove-orphans \
		--always-recreate-deps \
		--force-recreate

docker-down-dev:
	@docker-compose -f docker-compose.dev.yml down

docker-run-migrations:
	@docker exec banking-php-1 ./bin/console doctrine:migrations:migrate --no-interaction

docker-start-dev:
	@docker-compose -f docker-compose.dev.yml start

docker-stop-dev:
	@docker-compose -f docker-compose.dev.yml stop

docker-logs-dev:
	@docker-compose -f docker-compose.dev.yml logs -f