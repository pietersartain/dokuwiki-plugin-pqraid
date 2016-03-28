# pqraid

pqraid is a dokuwiki plugin that was used by the WoW guild Peace and Quiet (PQ) on the EU Azjol-Nerub realm, to organise raids. The raiding methodology devised by PQ was to allow multiple characters/spec combinations per player, therefore maximising the available roles with a minimum number of players.

The plugin was designed to support this methodology, and allowed attendees to mark which days they were unable to raid while allowing raid organisers to see which characters/specs are available over the coming weeks. Stats were kept to show the % invitations and once a raid was built, those members were emailed to confirm their attendance.

Peace and Quiet ran from November 2008 (WotLK release) until it's indefinite hiatus in September 2009. I consider the PQ raiding methodology a success, and that would not have been true without the support provided by this plugin.

## Installation

This plugin is provided for educational/historical purposes only, so running this with the latest dokuwiki version could be a mistake. However, should you really want to, here's some notes on getting it running:

* You need a dokuwiki installation residing with an accessible MySQL instance.
* Copy all of the files here into the plugin directory in dokuwiki, under a pqraid folder as you usually would with a dokuwiki plugin.
* Change the various usernames and passwords in the $PQRAID/sql/install.php file and then load it up in a browser.
* Run setup.sql, then patch-2.0.sql - this should instantiate the database.
* You should now be more or less ready to use the plugin.

## Usage

pqraid is a syntax plugin that populates the page with the necessary parts of the system. Because of the server-side nature of the plugin, it's not going to work without adding the `~~NOCACHE~~` option into the dokuwiki pages.

The two important options are:

The CSC Editor, used to set up your character/spec combo:
`{{pqraid>csceditor}}`

And the calendar display itself:
`{{pqraid>calendar}}`

## License

The MIT License (MIT)
Copyright (c) 2011-2016 Pieter Sartain

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Thanks

My thanks go to the whole Peace and Quiet guild for agreeing to try out this idea, which must have seemed like madness at the time. Coding thanks go to Jason, while organising thanks go to Owan.


So, remember ...

"If you're not having fun, you're doing something wrong."
_- Groucho Marx_