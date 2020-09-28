# Popup Manager plugin
It contains marketing tools which is used for collecting more subscribers. Create, manage exit-popups and it is integrated with other October's plugin. The popups show, when the visitors leave the webpage or the defined time is out.

## Advantages
* __It works with other plugins__ Campaign Manager and News & Newsletter.
* __Support the A/B test feature__ Run campaigns with A/B tests.
* __Customize the popup operation__ and launch successful campaign.
* __Make your own popup design__ what is perfectly fits your website.

## Other features
* Many animated popup styles
* Permissions for menus
* Admin dashboard widgets
* Build-in bot detection

## Usage steps
1. Add a new theme on the __Popup__ > __Themes__ page.
1. Add a new campaign on the __Popup__ > __Campaigns__ page.
1. Insert the __Popup component__ to bottom of the main layout.

__Important!__ The theme of the website must contain the {% styles %} and the {% scripts %} tags and the [jQuery](https://jquery.com) JavaScript framework too.

## Theme A/B test
You can select popup design from themes on the __Different designs__ section. The plugin choose one, when the popup is shown.

## Page A/B test
Add an unique code (for instance: _landingpage1_) on the __Page links__ section. After that you use the following url: www.examplewebpage.com/buyproduct?abtest=_landingpage1_

## Supported plugins
* [RainLab Pages](http://octobercms.com/plugin/rainlab-pages)
* [Indikator News & Newsletter](http://octobercms.com/plugin/indikator-news)
* [Responsiv Campaign Manager](http://octobercms.com/plugin/responsiv-campaign)

## Statistics
* Display the number of the views counted the last 20 days and actions for each campaign.
* If the visitor is a bot, the popup do not show on the page.
* The campaign appears only once (use cookie) in the active status. In the test mode it is always visible.
* If you are logged in as an administrator, the statistics is disabled.

## Dashboard widgets
* Report - Graph
* Report - List
* Summary

## Available languages
* en - English
* hu - Magyar

## Credits
* [Bot detection](https://github.com/fabiomb/is_bot)
* [Mobile Detect](http://mobiledetect.net)
