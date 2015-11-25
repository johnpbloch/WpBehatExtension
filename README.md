# WP Behat Extension

A Behat 3.0 extension for WordPress development.

### Installation

Add this repository to your `composer.json`. You should also add at least one mink driver (such as Goutte or Selenium2):

```json
{
  "require-dev": {
    "johnpbloch/wp-behat-extension": "dev-master",
    "behat/mink-goutte-driver": "^1.2",
    "behat/mink-selenium-driver": "^1.2"
  }
}
```

Then run `composer update`.

### Use

This extension assumes that you have the test environment already set up. It won't try to create a config file for you or drop/setup your database, or install WordPress for you. It assumes you already have a working installation of WordPress. It might be a good idea to have a snapshot of a test database that you can start from. You could then export a snapshot of the database before the tests run and restore it after they run:

```sh
wp db export pre-bdd-snapshot.sql
vendor/bin/behat
wp db import pre-bdd-snapshot.sql
```

To get behat loading WordPress, you must add the extension to your `behat.yml` file:

```yaml
default:
  extensions:
    JPB\WpBehatExtension:
      path: '%paths.base%/wordpress'
    Behat\MinkExtension:
      base_url: 'http://your-site.dev'
```

The WP Behat extension needs a `path` parameter to be set telling it where to find WordPress core. This is how it will find and load `wp-load.php`. You also need to define the base url as part of the mink extension's parameters. The WordPress extension will use this to set global values that WordPress expects to be available (and which are very important when running in the context of multisite).

Once the extension is configured, add any contexts you want to in your suites:

```yaml
default:
  suites:
    default:
      contexts:
        - JPB\WpBehatExtension\Context\WpContext
        - JPB\WpBehatExtension\Context\AuthenticationContext
        - Behat\MinkExtension\Context\MinkContext
```

The WP behat extension defines several Context objects that you can use. None of them extend `Behat\MinkExtension\Context\MinkContext`, so if you want that context, or if you want to use another package that *does* extend it, you can do so without conflicting with this package. Most contexts are defined as traits that are then imported into the contexts that need them. `JPB\WpBehatExtension\Context\WpContext` imports all contexts, so if you need all contexts, that will let you use them all at once. `JPB\WpBehatExtension\Context\AuthenticationContext` is not included in `WpContext`, so if you need to run tests that have steps dealing with authentication, you will need to include that too.

The current list of all contexts is:

- `JPB\WpBehatExtension\Context\WpContext`
  - Contains all contexts except `AuthenticationContext`
- `JPB\WpBehatExtension\Context\AuthenticationContext`
  - Steps:
    - `@Given I am not logged in`
    - `@Given I am logged in as :username with :password`
    - `@Then I should be logged out`
    - `@Then I should be logged in`
- `JPB\WpBehatExtension\Context\UserContext`
  - Steps:
    - `@Given Users exist:`
      - | login* | email | password | display_name | first_name | last_name | role |
- `JPB\WpBehatExtension\Context\PostContext`

#### Gotchas

This is probably pretty obvious, but when a mink driver makes web requests to your site, it will be happening in a different process from the step definitions. This means that in-memory-only operations made in a step definition (e.g. actions, filters, etc.) will not carry over into those web requests visiting pages on your test site.

### Credits

This work is based loosely on [John Blackbourn's WordPress Behat Extension](https://github.com/johnbillion/WordPressBehatExtension). If you need an extension that assumes you *don't* already have a site running, that one will probably be a better fit. You could theoretically still use this package alongside his package, as long as you don't load both extensions at once.

### License

MIT