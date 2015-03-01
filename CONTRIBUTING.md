# Contributing to Mods for HESK
So you want to contribute to Mods for HESK? Awesome! However, there are a few guidelines that need to be followed so the project can be as easy to maintain as possible.

## Getting Started
If you have already completed any of these steps in the past (such as creating a GitHub account), you can skip the respective step.
 - Make sure you have a [GitHub account](http://github.com/signup/free)
 - Fork the repository on GitHub (for more help consult the [GitHub documentation](https://help.github.com/articles/fork-a-repo/))

## Making Changes
 - Create a feature branch from where to base your work off of
   - This will be the `master` branch in most cases
   - *Please do not work off of the `master` branch directly*
 - Make commits of logical units.
   - For example, if you add 10 new features, please make at least 10 commits (1 per feature). This way, if a feature needs to be removed, it will be as easy as reverting a commit, rather than removing all 10.
 - Check for unnecessary whitespace using the `git diff --check` command. If there is trailing whitespace, your pull request will be denied.

## Submitting Changes
 - Sign the [Contributor License Agreement](https://www.clahub.com/agreements/mkoch227/Mods-for-HESK)
 - Push your changes to a topic branch in your fork of the repository
 - Submit a pull request to the official Mods for HESK repository (mkoch227/Mods-for-HESK)
 - The owner of Mods for HESK will then inspect and test the code in the pull request.  Feedback will be given via GitHub comments.
 - The owner of Mods for HESK expects responses within two weeks of the original comment. If there is no feedback within that time range, the pull request will be considered abandoned and subsequently will be closed.