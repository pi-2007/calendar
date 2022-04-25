# Another plugin Fork with CalDAV Support
I am trying to get the changes in this fork back into the original calendar but it needs a newer version of sabre/vobject and I am waiting until https://github.com/kolab-roundcube-plugins-mirror/libcalendaring/pull/1 is accepted.

## TLDR
Contrary to other CalDAV forks, this one is based on kolab-roundcube-plugins-mirror/calendar (which means the calendar itself is most up to date) and adds CalDAV capability on top of it. As far as I have found, it is the most up to date version with the most bugfixes (August 2021).

## Why is this needed?
Unfortunately, the current situation about CalDAV support in roundcube is quite confusing. There are several plugins (/Forks) around that have CalDAV support but from what I found, all of them are slightly buggy or do not work anymore.
All of them are based on <https://gitlab.awesome-it.de/kolab/roundcube-plugins>, a very old Fork which (as far as I can tell) is based on a version of kolab-roundcube-plugins-mirror/calendar that is over 10 years old and been updated in 4 years now.

## History of other Forks so far

### kolab-roundcube-plugins-mirror/calendar :
This is the original calendar that all other forks are based on. It is working very well and is actively maintained but unfortunately, it does not have caldav support

### [https://gitlab.awesome-it.de/kolab/roundcube-plugins](awesome-it) :
This is the "original fork" of the calendar. A lot of work was put into it and caldav is almost fully implemented. Unfortunately it included a few bugs / problems and most of them were not fixed in any other forks:
- The birthday calendar is not supported by the caldav driver.
- While the backend (mostly) supports adding all calendars from a dav-url, the front-end does not. That makes calendar handling a bit clunky and confusing.
- Calendar colors have to be set manually and can not be loaded from DAV.
- Adding and Removing calendars directly in the external source is not supported.
- It prepares the codebase so multiple drivers can be used. But as far as I can tell, this feature is not used in the code and also not really supported by the front-end. This means, that it still only uses one driver but as a result adds a lot of unnecessary changes to the original codebase.


### fasterit/roundcube_calendar :
A fork of awesome-it to make it work with blind-coder/rcmcardav (a CardDAV plugin) by packing the outdated version of sabre/DAV inside the plugin. But it hasn't been maintained and is still based on a very outdated version of kolab-roundcube-plugins-mirror/calendar.

### texxasrulez/calendar :
This is a fork of awesome-it with a few bugfixes to make it work with roundcube 1.3.
It is the most current fork of the original CalDAV fork. But unfortunately, it is treated as its own project (which means that it doesn't have any updates from the original calendar) and is focused primarily on nextcloud (which I don't really understand since nextcloud is using CalDAV anyway).
Also, on top of still having the original bugs included, it is also still based on an ancient sabre/DAV - version and there hasn't been updated since 2019.

### texxasrulez/caldav_calendar :
That one confuses me. It is from Texxas as well and seems to be the basis of Texxas but was abandoned in favour of Texxas. But it seems to be only a few commits behind Texxas.


### Another fork, what am I doing differently?

All CalDAV forks are based on faster-it which has a very different codebase to the original calendar because of its unfinished "multiple-driver" support. That makes it very difficult to get updates from the original calendar.

So I decided to ditch the "multiple driver" support and keep most changes in the CalDAV driver itself to stay compatible with the original calendar. I also added a ton of updates:
- Updated to the most recent version of the calendar plugin.
- Uses the most recent version of sabre/dav (x)
- Only minor changes in the existing code base, meaning that future updates of the calendar plugin should be able to be merged quite easily.
- added support for the birthday calendar.
- Changed the behaviour from "per calendar" to "per CalDAV source".
   - All calendars from a source will be automatically added.
   - Calendars can be created and deleted directly at the CalDAV source.
- ics support included.

### Why does this need a fork of libcalendaring?
The original calendar plugin uses sabre/vobject 3.5.3
In order to be compatible with other plugins (and because version 3.5.3 is ancient), I updated it to version 4.1.5
The problem is, that sabre/vobject makes use of DateTimeImmutable which libcalendaring does not expect.
It only needs minor changes to account for that but unfortunately the roundcube-project does not accept pull requests...

### Installation
I havent published this as a plugin, so you have to instruct composer to install directly from github. Run the following commands in the roundcubemail folder
(If you get an error that the "API rate limit" has been exceeded and you need an GitHub OAuth token, just follow the instructions in the console - you will need a GitHub account).
```
cd /pathTo/roundcubemail

composer config repositories.calendar vcs https://github.com/JodliDev/calendar
composer config repositories.libcalendaring vcs https://github.com/JodliDev/libcalendaring
composer config minimum-stability dev
composer require kolab/calendar

bin/initdb.sh --dir=plugins/calendar/drivers/caldav/SQL
```
