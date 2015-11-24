# Contributing to Mods for HESK
So you want to contribute to Mods for HESK? Awesome! However, there are a few guidelines that need to be followed so the project can be as easy to maintain as possible.

## Submitting an issue
If all you are doing is submitting an issue, please check if your "issue" qualifies as a GitHub issue:
 - **Feature Requests:** Feature requests are now being recorded at the Mods for HESK [UserVoice page](https://mods-for-hesk.uservoice.com/forums/254758-general). Please do not open these types of issues on GitHub. Issues opened that are "feature requests" will be closed.
 - **Translations:** Translations are now being recorded at the official Mods for HESK [website](https://mods-for-hesk.mkochcs.com/download.php). Please do not open these types of issues on GitHub. Issues opened that pertain to submitting new translations will be closed.
 - **Bugs:** Yes, please open these types of issues here. :grinning:

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
 - Push your changes to a topic branch in your fork of the repository
 - Submit a pull request to the official Mods for HESK repository (mkoch227/Mods-for-HESK)
 - If necessary, sign the Contributor License Agreement by checking the "status checks" area of your pull request.
 - The owner of Mods for HESK will then inspect and test the code in the pull request.  Feedback will be given via GitHub comments.
 - The owner of Mods for HESK expects responses within two weeks of the original comment. If there is no feedback within that time range, the pull request will be considered abandoned and subsequently will be closed.
