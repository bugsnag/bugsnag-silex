Contributing
============

-   [Fork](https://help.github.com/articles/fork-a-repo) the [notifier on github](https://github.com/bugsnag/bugsnag-silex)
-   Build and test your changes using `make build` and `make test`
-   Commit and push until you are happy with your contribution
-   [Make a pull request](https://help.github.com/articles/using-pull-requests)
-   Thanks!

Releasing
=========

1. Commit all outstanding changes
2. Update the CHANGELOG.md, and README if appropriate.
3. Commit, tag push
    ```
    git commit -am v2.x.x
    git tag v2.x.x
    git push origin master && git push --tags
    ```
4. Update the setup guides for PHP (and its frameworks) with any new content.
