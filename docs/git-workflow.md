# Pods Framework Git Workflow

## Branching

Based on the [Git-flow branching model](https://nvie.com/posts/a-successful-git-branching-model/)

* `main`
 * Latest stable version
* `release/x.x.x`
 * Contains features, enhancements, and bug fixes merged from PRs on the release
 * This should be based off of `main`
* `feature/1234-fix-this-problem`
 * Contains features, enhancements, and bug fixes work
 * Please use the issue number at the start after `feature/` to ensure it's easy to track the branch against what it's fixing and for branch organization

## Further notes

Some PHP IDEs and Git applications w/ UI support git-flow natively or through extensions and are compatible with our branching strategy above.
