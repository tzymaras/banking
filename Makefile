build-dev:
	@docker-compose -f docker-compose.dev.yml up \
		--build \
		--remove-orphans \
		--always-recreate-deps \
		--force-recreate