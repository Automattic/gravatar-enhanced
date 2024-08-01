# Tests

Gravatar Enhanced has two types of tests: unit tests and integration tests.
- Unit tests: Are supposed to work on isolation, they test a single function or class. No dependencies required.
- Integration tests: Test a more complete scenario, they require some more complex setup.

## Unit Tests

### Running
These are set up to be the 'default' tests, so you can run them with:
```bash
./vendor/bin/phpunit
```

From your IDE you should be able to run them directly.

## Integration Tests

### Setup
These tests require a database and some initial setup.

- Create a database and user for the tests. The easiest way might be with docker:
```bash
docker run --name mysql_80 -p 3306:3306 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=test_gravatar_enhanced -e MYSQL_USER=gravatar_enhanced -e MYSQL_PASSWORD=gravatar_enhanced --rm -d mysql:8.0
```
- Run the initial setup:
```bash
bash tests/integration/bin/install-wp-tests.sh test_gravatar_enhanced gravatar_enhanced 'gravatar_enhanced' 127.0.0.1 latest true
```
- By default, the script will download the wp test suite to `~/workspace/automattic/wordpress-develop`. **Make sure you add it to your IDE's include path**.


### Running
Once you have done the setup, you can run the tests with:
```bash
./vendor/bin/phpunit -c phpunit-integration.xml
```

> [!NOTE]
> To run these from your IDE you might need to add some specific run configuration pointing to `phpunit-integration.xml` instead of the default one.
