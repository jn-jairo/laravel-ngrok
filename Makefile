# php_version|laravel_version|orchestra_version
STABLE_DEPS += '8.1|8.83|6.24'
STABLE_DEPS += '8.1|9.0|7.0'
STABLE_DEPS += '8.1|10.0|8.0'
STABLE_DEPS += '8.2|9|7.0'
STABLE_DEPS += '8.2|10.0|8.0'

# php_version|laravel_version|orchestra_version
LOWEST_DEPS += '8.1|8.83|6.24'
LOWEST_DEPS += '8.1|9.0|7.0'
LOWEST_DEPS += '8.1|10.0|8.0'
LOWEST_DEPS += '8.2|9|7.0'
LOWEST_DEPS += '8.2|10.0|8.0'

define show_title
	title=$1 ; \
	title="* $$title *" ; \
	n=$${#title} ; \
	echo "" ; \
	printf '%'$${n}'s' | tr ' ' '*' ; \
	echo "\n$$title" ; \
	printf '%'$${n}'s' | tr ' ' '*' ; \
	echo "\n" ;
endef

define composer_update
	$(call show_title,'COMPOSER UPDATE') \
	composer update --prefer-dist --no-interaction --prefer-stable
endef

define test_version
	versions="$1" ; \
	composer_args=$2 ; \
	php_version=$$(echo $${versions} | cut -d'|' -f 1); \
	laravel_version=$$(echo $${versions} | cut -d'|' -f 2); \
	orchestra_version=$$(echo $${versions} | cut -d'|' -f 3); \
	if command -v php$${php_version} > /dev/null 2>&1 ; \
	then \
		$(call show_title,'PHP: '$${php_version}' LARAVEL: '$${laravel_version}' ORCHESTRA: '$${orchestra_version}) \
		echo -n 'Updating dependencies... ' ; \
		output_composer=$$(php$${php_version} $$(which composer) update --prefer-dist --no-interaction $${composer_args} --prefer-stable --with=laravel/framework:^$${laravel_version} --with=orchestra/testbench:^$${orchestra_version} --with=orchestra/testbench-core:^$${orchestra_version} 2>&1) ; \
		if [ $$? -ne 0 ] ; \
		then \
			echo 'ERROR' ; \
			echo "$${output_composer}" ; \
			continue ; \
		fi; \
		echo 'OK' ; \
		echo -n 'Testing... ' ; \
		output_php=$$(php$${php_version} vendor/bin/pest \
			--do-not-cache-result 2>&1) ; \
		if [ $$? -ne 0 ] ; \
		then \
			echo 'ERROR' ; \
			echo "$${output_php}" ; \
			continue ; \
		fi; \
		echo 'OK' ; \
	fi;
endef

.PHONY: fast
fast: parallel-test code-fix code-style

.PHONY: slow
slow: parallel-test code-fix code-style static-analysis

.PHONY: coverage
coverage: test-coverage infection-test

.PHONY: coverage-show
coverage-show: test-coverage infection-test show-coverage show-infection

.PHONY: composer-update
composer-update:
	@$(call composer_update)

.PHONY: test
test:
	@$(call show_title,'TEST') \
	vendor/bin/pest \
		--do-not-cache-result \
		$(ARGS)

.PHONY: test-coverage
test-coverage: clear-coverage
	@$(call show_title,'TEST COVERAGE') \
	XDEBUG_MODE=coverage \
	php -d zend_extension=xdebug.so \
	vendor/bin/pest \
		--configuration phpunit-coverage.xml \
		--do-not-cache-result \
		--coverage

.PHONY: test-stable
test-stable:
	@$(call show_title,'TEST STABLE') \
	for versions in $(STABLE_DEPS) ; \
	do \
		$(call test_version,$${versions}) \
	done; \
	$(call composer_update) > /dev/null 2>&1

.PHONY: test-lowest
test-lowest:
	@$(call show_title,'TEST LOWEST') \
	for versions in $(LOWEST_DEPS) ; \
	do \
		$(call test_version,$${versions},--prefer-lowest) \
	done; \
	$(call composer_update) > /dev/null 2>&1

.PHONY: parallel-test
parallel-test:
	@$(call show_title,'PARALLEL TEST') \
	vendor/bin/pest \
		--parallel \
		--processes=$(shell nproc) \
		--passthru="--do-not-cache-result"

.PHONY: parallel-test-coverage
parallel-test-coverage: clear-coverage
	@$(call show_title,'PARALLEL TEST COVERAGE') \
	XDEBUG_MODE=coverage \
	php -d zend_extension=xdebug.so \
	vendor/bin/pest \
		--parallel \
		--processes=$(shell nproc) \
		--configuration=phpunit-coverage.xml \
		--passthru-php="-d zend_extension=xdebug.so" \
		--passthru="--do-not-cache-result" \
		--coverage

.PHONY: infection-test
infection-test: clear-infection
	@$(call show_title,'INFECTION TEST') \
	infection \
		--threads=$(shell nproc) \
		--coverage=build/coverage \
		--skip-initial-tests \
		--test-framework=pest

.PHONY: code-fix
code-fix:
	@$(call show_title,'CODE FIX') \
	(PHP_CS_FIXER_IGNORE_ENV=1 php-cs-fixer fix --config php-cs-fixer.php --using-cache=no || true) ; \
	(phpcbf -n --extensions=php || true)

.PHONY: code-style
code-style:
	@$(call show_title,'CODE STYLE') \
	phpcs --extensions=php

.PHONY: static-analysis
static-analysis:
	@$(call show_title,'STATIC ANALYSIS') \
	phpstan analyse

.PHONY: show-coverage
show-coverage:
	@xdg-open build/coverage/coverage-html/index.html > /dev/null 2>&1

.PHONY: show-infection
show-infection:
	@xdg-open build/infection/infection.html > /dev/null 2>&1

.PHONY: clear
clear: clear-coverage clear-infection

.PHONY: clear-coverage
clear-coverage:
	@$(call show_title,'CLEAR COVERAGE') \
	(rm -r build/coverage > /dev/null 2>&1 || true)

.PHONY: clear-infection
clear-infection:
	@$(call show_title,'CLEAR INFECTION') \
	(rm -r build/infection > /dev/null 2>&1 || true)
