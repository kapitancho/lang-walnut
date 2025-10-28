.PHONY: build build-phar build-server serve test test-coverage coverage-report clean help install-deps

help:
	@echo "Walnut Build & Development Commands"
	@echo ""
	@echo "Build:"
	@echo "  make build-phar       Build walnut.phar executable"
	@echo "  make build-server     Build walnut-server (requires RoadRunner binary)"
	@echo ""
	@echo "Development:"
	@echo "  make install-deps     Install PHP dependencies"
	@echo "  make test             Run all PHPUnit tests"
	@echo "  make test-coverage    Run tests with code coverage (HTML + Clover)"
	@echo "  make coverage-report  Open coverage HTML report in browser"
	@echo "  make serve            Run HTTP server (requires walnut.phar)"
	@echo ""
	@echo "Cleanup:"
	@echo "  make clean            Remove build artifacts and coverage reports"
	@echo ""

install-deps:
	composer install

build-phar: install-deps
	@command -v box >/dev/null 2>&1 || { echo "Installing box..."; composer global require humbug/box; }
	@VERSION=$$(git describe --tags --always 2>/dev/null | sed 's/^v//'); \
	echo "$$VERSION" > VERSION; \
	echo "Building version: $$VERSION"
	box compile
	@echo "✅ Built: build/walnut.phar"

build-server: build-phar
	@echo "Building walnut-server requires RoadRunner binary."
	@echo "Use GitHub Actions for cross-platform builds, or build manually:"
	@echo ""
	@echo "  1. Download RoadRunner: https://github.com/roadrunner-server/roadrunner/releases"
	@echo "  2. Place 'rr' in build/"
	@echo "  3. Copy .rr.yaml to build/"
	@echo ""

serve: build-phar
	./walnut-serve

test:
	vendor/bin/phpunit

test-coverage:
	vendor/bin/phpunit --coverage-html=./dev/report/phpunit --coverage-clover=coverage.xml --coverage-text

coverage-report:
	@if [ -d "./dev/report/phpunit" ]; then \
		echo "Opening coverage report..."; \
		open ./dev/report/phpunit/index.html || xdg-open ./dev/report/phpunit/index.html; \
	else \
		echo "Coverage report not found. Run: make test-coverage"; \
	fi

clean:
	rm -rf build/ dev/ coverage.xml coverage-report/ VERSION
	@echo "✅ Cleaned artifacts"