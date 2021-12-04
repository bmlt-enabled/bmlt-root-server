# Basic Meeting List Toolbox Root Server

[![Build Status](https://travis-ci.org/bmlt-enabled/bmlt-root-server.svg?branch=master)](https://travis-ci.org/bmlt-enabled/bmlt-root-server)

DESCRIPTION
-----------

The Basic Meeting List Toolbox (BMLT, hereafter) is a very powerful client/server system
that has been written for a very specific purpose, for a very specific clientele.

It is designed to track and locate Narcotics Anonymous meetings, which are regularly-scheduled, weekly, recurring events.

The original intended clientele is Narcotics Anonymous Service bodies (although other 12 step fellowships have started
using BMLT as well). The service body implements a BMLT server, and provides the server to other NA Service bodies.
This project is the "root" server for the BMLT. It is the "server" part of the BMLT "client/server" architecture.

You can find out way too much about the BMLT on [the BMLT Documentation Site](https://bmlt.app), including information
on lots of ways you can contribute to the project.

The source files are hosted on [GitHub](https://github.com).

[Follow this link to access the BMLT Root Server GitHub repository](https://github.com/bmlt-enabled/BMLT-Root-Server).
There are also links to various predecessor legacy repositories [here](#older-repositories) at the end of this README.
For specific information on setting up a development environment for work on the BMLT root server, please
see [CONTRIBUTING.md](CONTRIBUTING.md) in the GitHub repository.

[You can follow us on Twitter for release announcements](http://twitter.com/BMLT_NA).

REQUIREMENTS
------------

The entire system is written in PHP, JavaScript, XHTML and CSS. Most of the code is PHP.

PHP 5.6 or above is required to establish a root server, and 5.0 or above for a satellite.

For more information about server requirements, see the "Things You Will Need Before You Install" section of
[Installing a New Root Server](https://bmlt.app/setting-up-the-bmlt/).
 
INSTALLATION
------------

You set up a root server as the central database and administration area for a BMLT
implementation, but most people access it through what we call "satellite servers." These
satellites comprise the "client" part of the BMLT "client/server" architecture.

For instructions on installing the root server, see [Installing a New Root Server](https://bmlt.app/setting-up-the-bmlt/)

OLDER REPOSITORIES
------------------

The first BMLT release was in 2009. 

Here are various legacy repositories that are predecessors to the
[current BMLT Root Server GitHub repository](https://github.com/bmlt-enabled/BMLT-Root-Server).

[Follow this link to see the legacy BitBucket repository](https://bitbucket.org/bmlt/bmlt-root-server-deprecated/src/Release/).

[Follow this link to see the legacy GitHub repository](https://github.com/MAGSHARE/BMLT-Root-Server).

[Follow this link to see the legacy antediluvian repository on SourceForge](https://sourceforge.net/projects/comdef/).
(Note: this link isn't working any more as of July 2020.)

NOTE: The repository origin has been transferred to [Bitbucket](http://bitbucket.org).
[The legacy GitHub repository](https://github.com/MAGSHARE/BMLT-Root-Server) is now only archival
(ends at version 2.0.2).
