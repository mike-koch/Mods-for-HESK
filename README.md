[![Stories in Ready](https://badge.waffle.io/mkoch227/Mods-For-Hesk.png?label=waffle:ready&title=Ready)](https://waffle.io/mkoch227/Mods-For-Hesk)
## [BETA] [Mods for HESK](http://mods-for-hesk.mkochcs.com) v2.0.0

Mods for HESK is a set of modifications for HESK v2.6.0, a free and popular helpdesk solution.

## Features
 - A new, responsive user interface
 - Ability to create custom ticket statuses
 - Right-to-left text direction
 - Designate parent/child relationships for tickets
 - Ticket dashboard automatic refresh
 - HTML-formatted e-mails
 - Mailgun API support
 - Customer email verifications
 - Custom fields in multiple languages
 - Create new ticket based on previous ticket
 - Admins can restrict users from modifying notification settings
 - More-restricted settings page access
 - Enable / disable staff members

## Download
You can download Mods for HESK via two ways:
 - **Stable Releases:** Commits that have a release tag associated with a commit are considered releases.  You can click on "releases" on the top of the repo, and then click the green button to download.
 - **Bleeding-edge Releases:** You can also download the latest, bleeding-edge version of Mods for HESK by simply clicking "download as zip" to the right of the repository.  This will download an exact copy of this branch in its current state, which may be contain bugs/other issues.  This is not recommended for a production use.

## Installation
Visit [http://mods-for-hesk.mkochcs.com/download.php](http://mods-for-hesk.mkochcs.com/download.php) for installation instructions.

## Languages
As of current, only English is a supported language, as there have been several language items that have been edited/created.
  If you want to translate Mods for HESK to your own language, it is recommended to download the original HESK language
  file for your language, and then add/edit the lines listed under `//Added or modified in Mods for HESK X.X.X`
  (where X.X.X is a version number) for your language. You will also need to add some email templates. The easiest way
  to find out which ones are needed are by clicking "Test language folder" in HESK's settings.
Mods for HESK translations are available at http://mods-for-hesk.mkochcs.com.

## Browser Compatibility
This list may be incomplete. Please leave a note on the PHP Junkyard forums for additional browser compatibility information.
 - **Google Chrome 33+:** Compatible
 - **Mozilla Firefox 28+:** Compatible
 - **Internet Explorer 6/7:** *NOT* Compatible
 - **Internet Explorer 8:** *PARTIAL* Compatibility
 - **Internet Explorer 9+:** Compatible
There are no intentions of making Mods for HESK compatible with Internet Explorer 6 or 7, or any browser that is 2 or more major revisions older than its latest version.

## Versioning
Mods for HESK will be maintained under the Semantic Versioning guidelines as much as possible. Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:
 - Breaking backward compatibility bumps the major (and resets the minor and patch)
 - New additions, including new minor features, without breaking backward compatibility bumps the minor (and resets the patch)
 - Bug fixes and misc minor changes bumps the patch

For more information on SemVer, please visit http://semver.org.

## Credits
 - Mike Koch - Creator of Mods for HESK
 - Klemen Stirn - Creator of HESK
