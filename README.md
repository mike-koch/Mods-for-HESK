[![Analytics](https://ga-beacon.appspot.com/UA-49251479-1/hesk/README)](https://github.com/mkoch227/Hesk)

<h2>HESK UI v1.1.5</h2>

This branch contains all files modified from the base version of HESK to become HESK UI, a clean user interface for HESK v2.5.3

<h2>Download</h2>

You can download this tweak via two ways:

<ol>
<li><strong>Stable Releases:</strong> Releases that have a release tag associated with a commit are considered releases.  You can click on "releases" on the top of the repo, and then click "zip" or "tar.gz" to download the repo at that stage.</li>
<li><strong>Bleeding-edge Releases:</strong> You can also download the latest, bleeding-edge version of HESK UI by simply clicking "download as zip" to the right of the repository.  This will download an exact copy of this branch in its current state, which may be contain bugs/other issues.  This is not recommended for a production use.</li>
</ol>

<h2>Installation</h2>

<ol>
<li>Download HESK from <a href="http://www.hesk.com/download.php" target="_blank">http://www.hesk.com/download.php</a>.</li>
<li>Extract the contents of HESK to a directory of your choice.</li>
<li>Download HESK UI from one of the two methods described above.</li>
<li>Copy and paste the contents of the zip/tar.gz bundle and overwrite any files in the original HESK 2.5.3 folder.</li>
<li>Upload the resulting folder to your webserver.</li>
</ol>
<p>Please consult the official HESK Documentation on how to install HESK, as it is the same for both HESK and HESK UI.</p>

<h2>Languages</h2>
<p>As of current, only English is a supported language, as there have been several language items that have been edited/created. If you want to translate HESK UI to your own language, it is recommended to download the original HESK language file for your language, and then add/edit the lines listed under <code>//Added or modified in HESK UI</code> for your language.</p>
<p>If you create a translation for HESK UI, please submit it via a pull request or via the PHP Junkyards forum, where it will be committed to this branch.</p>

<h2>Browser Compability</h2>
<p>This list may be incomplete. Please leave a note on the PHP Junkyard forums for additional browser compatibility information.
<ul>
<li><strong>Google Chrome 33+: </strong> Compatible</li>
<li><strong>Mozilla Firefox 28+:</strong> Compatible</li>
<li><strong>Internet Explorer 6/7:</strong> <em>NOT</em> Compatible</li>
<li><strong>Internet Explorer 8:</strong> Partial Compatibility</li>
<li><strong>Internet Explorer 9:</strong> Compatible</li>
</ul>
<p>There are no intentions of making HESK UI compatible with Internet Explorer 6 or 7, or any browser that is 2 or more major revisions older than its latest version.</p>

<h2>Versioning</h2>
<p>HESK UI will be maintained under the Semantic Versioning guidelines as much as possible. Releases will be numbered with the following format:</p>

<code>&lt;major&gt;.&lt;minor&gt;.&lt;patch&gt;</code>

<p>And constructed with the following guidelines:</p>

<ul>
<li>Breaking backward compatibility bumps the major (and resets the minor and patch)</li>
<li>New additions, including new minor features, without breaking backward compatibility bumps the minor (and resets the patch)</li>
<li>Bug fixes and misc minor changes bumps the patch</li>
</ul>

<p>For more information on SemVer, please visit http://semver.org.</p>

<h2>Credits</h2>
<p>Mike Koch - Creator of Hesk UI</p>
<p>Klemen Stirn - Creator of Hesk</p>
<p>lupolo from PHPJunkyard Scripts - diligent bug reporting on the PHPJunkyard Scripts Forum</p>
