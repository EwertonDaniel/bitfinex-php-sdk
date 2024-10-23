# Contributing to Bitfinex PHP SDK

Thank you for considering contributing to `bitfinex-php-sdk`! Here are some guidelines to ensure that your contribution is useful and consistent with
the project.

## How to Contribute

1. **Fork** the repository.
2. Create a **branch** for your contribution (`git checkout -b feature/new-feature`).
3. **Implement** your changes clearly and with proper documentation.
4. Add **tests** to cover your functionality.
5. Run tests locally to ensure everything is working correctly:
   ```bash
   ./vendor/bin/pest
    ```
6. **Commit** your changes (`git commit -am 'Add new feature)`.
7. **Push** to your branch (`git push origin feature/new-feature`).
8. Open a **Pull Request** on GitHub.

## Code Standards

Please follow the coding standards established in the project:

* **PSR-12** for PHP code style.
* **PHPDoc** for documenting functions, methods, and classes.
* Each change should be accompanied by appropriate tests.

## Testing

This project uses Pest as the testing framework. Make sure all tests pass before submitting your PR. If you're adding a new feature, please include
relevant tests as well.

To run the tests, use:

```bash
./vendor/bin/pest
```

### Reporting Issues

If you encounter a bug or have a suggestion for improvement, please open an **Issue** on GitHub with as many details as possible:

* A clear description of the issue or suggestion.
* Steps to reproduce the issue, if applicable.
* Details about the environment (PHP version, operating system, etc.).

### Pull Requests

* Make sure your code is **up to date** with the main branch before submitting a PR.
* Include a clear description of the changes made.
* If the PR introduces a new feature, explain how it works and provide usage examples.
  Thank you for helping to improve the `bitfinex-php-sdk`!
