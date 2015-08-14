LegionPE-Theta-Base
===

[![Join the chat at https://gitter.im/LegionPE/LegionPE-Theta-Base](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/LegionPE/LegionPE-Theta-Base?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
LegionPE open-source framework plugin: LegionPE Theta Base

In order to protect server safety, there is an `**credentials**` line in `.gitignore`. In the real repo, it stores the credentials as constants, like MySQL connection details, IRC webhook paths, etc. In order to make it work with IDEs, I have created [a stub script](/stubs/creden_tials_stub.php) to replace it.

The content of this repo is licensed under the GNU Lesser General Public License v3. The full copy of the license is available [here](LICENSE).

This _Base_ "library" has an abstract class `legionpe\theta\BasePlugin`, which is to be extended by implementations of LegionPE-Theta servers. If you would like to test this "library", please create your own implementation.
