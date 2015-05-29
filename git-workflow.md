# Pods Framework Git Workflow

## Branching

Based on the [Git-flow branching model](http://nvie.com/posts/a-successful-git-branching-model/)

### Current 2.x release cycle

* master
 * Latest stable
 * Should be based off of commit that tagged 2.4.3 from the 2.x branch
* `2.x`
 * Development release, features and fixes merged in that work and are tested
 * This would normally be called ‘develop', but too many things are based off if it to change it right now
* `feature/1234`
 * Bug fixes, enhancements, features work goes here, and should be constrained to the issue number specifically, except in circumstances where that may be difficult.
 * Prerequisite is an issue number and must be named in this format:
   * `feature/{issue-number}`
* `hotfix/1235`
 * Hot fix work that must based off of and go directly into master, bypassing existing work going into develop for the next release
 * Issue number not required, but if in reference to it, the prerequisite is an issue number and must be named in this format:
   * `hotfix/{issue-number}`

### **Current 3.0 development cycle**

* _There is no stable branch during 3.0 development work, see next section for 3.x stable branching._
* `release/3.0`
 * Development release, features and fixes merged in that work and are tested
 * Should be based off of the current 3.0-unstable branch
* `feature/3.0/1234`
 * Use `feature/1234` normally, unless you need to commit to 2.x AND 3.0, in which you would need to make the 3.0 branch as `feature/3.0/1234`
 * Bug fixes, enhancements, features work goes here, and should be constrained to the issue number specifically, except in circumstances where that may be difficult.
 * Prerequisite is an issue number and must be named in this format:
   * `feature/3.0/{issue-number}`

### **3.0 stable release, branches restructured**

#### **2.x becomes secondary stable**

* `2.x`
 * Latest stable (2.5)
 * Should be based off of commit that tagged 2.5 from the master branch (**_prior to 3.0 stable_**)
 * This would probably be `release/2.x` but due to legacy reasons, changing it would present some challenges for folks
* `develop/2.x`
 * Development release, features and fixes merged in that work and are tested
 * Should be based off of the current develop branch (**_prior to 3.0 stable_**)
* `feature/1234-2.x`
 * Use `feature/1234` normally, unless you need to commit to 2.x AND 3.x, in which you would need to make the 3.0 branch as `feature/1234-2.x`
 * Bug fixes, enhancements, features work goes here, and should be constrained to the issue number specifically, except in circumstances where that may be difficult.
 * Prerequisite is an issue number and must be named in this format:
   * `feature/{issue-number}`
* `hotfix/1235-2.x`
 * Use `hotfix/1234` normally, unless you need to commit to 2.x AND 3.x, in which you would need to make the 3.0 branch as `hotfix/1234-2.x`
 * Hot fix work that must based off of and go directly into master, bypassing existing work going into develop for the next release
 * Issue number not required, but if in reference to it, the prerequisite is an issue number and must be named in this format:
   * `hotfix/{issue-number}`

#### **3.x becomes new stable**

* `master`
 * Latest stable (3.0)
 * Should be based off of commit that tagged 3.0 from the `release/3.0` branch
 * See 2.x secondary stable for what to do with existing _master_
* `develop`
 * Development release, features and fixes merged in that work and are tested
 * Should be based off of the current `release/3.0` branch
 * See 2.x secondary stable for what to do with existing _develop_
* `feature/1234`
 * Bug fixes, enhancements, features work goes here, and should be constrained to the issue number specifically, except in circumstances where that may be difficult.
 * Prerequisite is an issue number and must be named in this format:
   * `feature/{issue-number}`
* `hotfix/1235`
 * Hot fix work that must based off of and go directly into _master_, bypassing existing work going into _develop_ for the next release
 * Issue number not required, but if in reference to it, the prerequisite is an issue number and must be named in this format:
   * `hotfix/{issue-number}`

### **1.x EOL**

* The current 1.x branch should get archived and cease to be supported, when 3.0 becomes stable.
* Until then, it will continue on as 1.x branch, and follow same structure as 2.x secondary stable above.
* We don't anticipate any future 1.x release now or prior to 3.0 stable, but we don't want to rule it out entirely, especially without notice.

## Creating and Merging a Feature branch

`$develop` = _develop_ Branch to merge into
`$feature` = Branch with feature to merge

Instructions:

1. **Create new local `$feature` branch** from `$develop`
2. **Push / Publish** `$feature` branch
3. Ensure **Pull Request** is created on GitHub.com
 * PR to merge `$feature` into `$develop`
 * Set release milestone
 * Set proper labels
   * Patch?
 * Set assignee (yourself, lead dev, or tester)
4. Complete remaining development on `$feature` (if any)
 * **Commit / Push** additional changes to `$feature` as needed
5. **Pull** `$develop` **from remote**
6. **Merge** `$develop` **into** `$feature`
7. **Commit** `$feature`
8. **Push** `$feature`
9. **Test** code again (to ensure `$develop` or merge has not broken anything)
 * If broken, follow Steps 4 through 8 again
10. Verify **Travis-CI** passes on latest push to **Pull Request**
 * If broken, follow Steps 4 through 8 again
11. **Merge Pull Request** on GitHub.com
12. **Delete** `$feature` branch

## Creating and Merging a Hotfix branch

`$master` = _master_ Branch to merge into
`$hotfix` = Branch with hotfix to merge
`$develop` = _develop_ Branch to merge into

Instructions:

1. **Create new local `$hotfix` branch** from `$master`
2. **Push / Publish** `$hotfix` branch
3. Ensure **Pull Request** is created on GitHub.com
 * PR to merge `$hotfix` into `$master`
 * Set release milestone
 * Set proper labels
   * Patch?
 * Set assignee (yourself, lead dev, or tester)
4. Complete remaining development on `$hotfix` (if any)
 * **Commit / Push** additional changes to `$hotfix` as needed
5. **Pull** `$master` **from remote**
6. **Merge** `$master` **into** `$hotfix`
7. **Commit** `$hotfix`
8. **Push** `$hotfix`
9. **Test** code again (to ensure `$master` or merge has not broken anything)
 * If broken, follow Steps 4 through 8 again (may want to also foll
10. Verify **Travis-CI** passes on latest push to **Pull Request**
 * If broken, follow Steps 4 through 8 again
11. **Merge Pull Request** on GitHub.com
12. **Merge** `$hotfix` into `$develop`
13. **Commit** `$develop`
14. **Push** `$develop`
15. **Delete** `$hotfix` branch

## Releasing and tagging a new version

1. **Merge** _develop_ **into** _master_
2. **Tag** latest commit in master using the following format:
 * `{x}.x/{x.x.x}` (the .x is literal, like `3.x`)
 * Where **_{x}_** is the major release version (`2.x`)
 * Where **_{x.x.x}_** is the full release version (`2.5`)
3. **Push all tags**

## Further notes

Some PHP IDEs and Git applications w/ UI support git-flow natively or through extensions. They will work great with this model, however working alongside the multiple versions (master vs 3.0 current; master vs 2.x in future) will **not** support the built-in master / develop / feature / hotfix / archive / release workflows for the secondary branching we've added to suit our needs.