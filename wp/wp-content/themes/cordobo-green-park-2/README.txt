
----------------------------------------------------------------------
RELEASE NOTES FOR Cordobo Green Park 2 (GP2)
----------------------------------------------------------------------

Version: Beta 5 (0.9.502)
Release date: January 11th, 2010


Get the latest Version here:
http://cordobo.com/green-park-2/


Please report all bugs:
http://cordobo.com/1449-cordobo-green-park-2-beta-5/
Please add your Wordpress version, your browser details
and a screenshot if necessary.




----------------------------------------------------------------------
FAQ - Frequently Asked Questions
----------------------------------------------------------------------

- DONATIONS
- TRANSLATION & LOCALIZATION
- ABOUT (change the "about me" box in the sidebar)
- LOGO
- SUPPORTED PLUGINS (e.g. Twitter)
- ADMANAGER (e.g. Google Adsense)
-- ADS ON THE FRONT PAGE
-- ADS ON THE SIDEBAR
- CHANGELOG




----------------------------------------------------------------------
DONATIONS
----------------------------------------------------------------------

Coders love coffee — and I'm not an exception ;)
If you like my free themes, feel free to donate 1$ for a coffee.
It'll be highly appreciated!

Paypal: Chungonet@gmail.com




----------------------------------------------------------------------
TRANSLATION & LOCALIZATION
----------------------------------------------------------------------

Cordobo Green Park 2 is even better in your own language ;-)
If you want to translate GP2 into your language, you find a default.mo
and default.po file in the folder "languages".

Please send your translated files to i18n@cordobo.de

More information & ressources can be found here:
http://cordobo.com/1381-green-park-2-beta-5-pre/


   1. Chinese by wxzbb (http://wxzbb.com/)
   2. English (default) Andreas Jacob (http://cordobo.com/)
   3. French by Julien
   4. German by Andreas Jacob (http://cordobo.com/)
   5. Icelandic by Jóhannes Birgir Jensson (http://joi.betra.is/)
   6. Italian by Saverio Tonno
   7. Norwegian Bokmål by Bjørn-Arild Eriksen-Woll (http://imback.net/)
   8. Polish by Tosiek (http://tosiek.pl/)
   9. Romanian by Cristian Boba (http://www.ebacalaureat.net/)
  10. Russian (formal) by Dr. Dmitry Tarasov (http://garden.t-i-m.me/)
  11. Russian (informal) by Perllover (http://blog.perlover.com/)
  12. Spanish (castellano) by José Manuel Mao (http://www.cuarenton.com/)
  13. Swedish by Jon Klarström (http://www.jonvision.se/)





----------------------------------------------------------------------
Change ABOUT information
----------------------------------------------------------------------

Use the Cordobo Green Park 2 settings page in your Wordpress Admin
to change the ABOUT text.




----------------------------------------------------------------------
LOGO as an image
----------------------------------------------------------------------

You can simply replace the "text logo" with an image.
Open the file "styles.css" in the themes folder

1. Find the text
   "Start EXAMPLE CODE for an image logo" (line 246)

2. Delete "/*" before (without the "")
   "#logo, ..." (line 247)

3. Delete "*/" after (line 252)
   ".description ..." (line 251)

4. Find "logo.png" (line 250) and replace it with the name of your logo.

5. Change the height and width to fit your logo (line 248)
   "#logo, #logo a { display: block; height: 19px; width: 87px; }"

6. Find the text
   "Start EXAMPLE CODE for a text logo" (line 257)

7. Add "/*" before (without the "")
   "#branding ..." (line 259)

8. Add "*/" (line 262) after
   "#logo, .description { color: #868F98; float: left; margin: 17px 0 0 10px; }" (line 261)

Save your changes and upload the file style.css and your logo to your themes folder.




----------------------------------------------------------------------
SUPPORTED PLUGINS
----------------------------------------------------------------------

Built-In support for Twitter & WP-PageNavi plugins
(You need to download and activate the plugins separately)

- Twitter for Wordpress by Ricardo González
  http://rick.jinlabs.com/code/twitter

- WP-PageNavi by Lester ‘GaMerZ’ Chan
  http://lesterchan.net/portfolio/programming/php/#wp-pagenavi


INSTALLATION:
Upload the plugins to your plugins directory in your wordpress installation.
Activate the plug-ins. WP-PageNavi needs no more fine-tuning.
To use “Twitter for Wordpress” with your account, log into your
Wordpress ADMIN PANEL and select:

Appearance & Editor › Sidebar (sidebar.php)

Replace all 3 appearances of “cordobo” with your username on twitter
(upcoming RC1 features an options panel).




----------------------------------------------------------------------
ADMANAGER (e.g. Google Adsense)
----------------------------------------------------------------------

Use the Cordobo Green Park 2 settings page in your Wordpress admin panel
to add your Google Adsense code (or any other advertisement).

Ads are currently only displayed below the content on single pages,
neither on the front page nor pages nor archives.


ADS ON THE FRONT PAGE

If you want to display ads below each entry on the front page,
rename "index.php" to "index-orig.php" and "index-ads.php" to
"index.php" and upload the new "index.php" to your webserver.

NOTE: Due to the terms of use of Google Adsense, Google will only
show 3 ads on every page - that's not a bug of the theme ;-)


ADS ON THE SIDEBAR

The sidebar box ("about box") can be used to display ads as well.
You'll find the corresponding settings on the GP2 settings page.




----------------------------------------------------------------------
CHANGELOG for Cordobo Greenpark 2 Beta 5
----------------------------------------------------------------------

For a full changelog visit:
http://cordobo.com/green-park-2/#changelog

