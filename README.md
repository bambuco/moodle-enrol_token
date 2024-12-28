# Token enrol #

Plugin to allow user registration through single-access tokens.

Any user attempting to enrol in the course will be required to supply the token.
Note that a user only needs to supply the enrolment token ONCE, when they enrol in the course.

It is based in the enrol_self plugin.

Package tested in: 4.3+.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/enrol/token

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## About
* **Developed by:** David Herney - david dot herney at bambuco dot co
* **GIT:** https://github.com/bambuco/moodle-enrol_token
* **Powered by:** [BambuCo](https://bambuco.co/) - [Universidad CES](https://www.ces.edu.co/)

## In version
### 2024122201:
* First version.

## License ##

2024 David Herney @ BambuCo

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
