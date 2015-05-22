LegionPE-Theta-Base
===
LegionPE open-source base plugin: LegionPE Theta Base

This plugin is open-sourced here for the following reasons:

* To encourage the free flow of knowledge in the community
* To increase the transparency of the server (for example, what we do with our passwords)
* To let other developers learn from this plugin programming techniques and PocketMine "hacks", like PocketMine
* To let other developers contribute to this plugin by making source more accessible.

In order to protect server safety, there is an `**credentials**` line in `.gitignore`. In the real repo, it stores the credentials as constants, like MySQL connection details, IRC webhook paths, etc. In order to make it work with IDEs, I have created [a stub script](/PEMapModder/LegionPE-Theta-Base/blob/master/stubs/creden_tials_stub.php) to replace it.

The content of this repo is licensed under the GNU General Public License v2. The full copy of the license is available [here](/PEMapModder/LegionPE-Theta-Base/blob/master/LICENSE).

This _Base_ "library" has an abstract class `legionpe\theta\BasePlugin`, which is to be extended by implementations of LegionPE-Theta servers. If you would like to test this "library", please create your own implementation.
