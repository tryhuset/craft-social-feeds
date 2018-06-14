# Social feeds plugin for Craft CMS 3.x

Utilizes json api to get latest social feeds

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Add the following to `composer.json`
```
"repositories": [
    {
      "type": "vcs",
      "url": "https://git1.apt.no/open/craft-social-feeds.git"
    }
  ],
```

3. Then tell Composer to load the plugin:

        composer require apt/craft-social-feeds

4. In the Control Panel, go to Settings → Plugins and click the “Install” button for Social feeds.

## Social feeds Overview

Adds api to fetch social feeds as JSON.

The plugin can feed from
* Facebook
* Youtube
* Instagram
* Twitter
* Flickr

The individual feeds must be activated through the plugin settings.

All credetials is registered in the plugin settings.

## Configuring Social feeds

If you need to disable/hide some services in admin, add a file named apt-social-feeds.php to your craft config folder.

Add following code to the file:

```
<?php
return [
    'facebook' => true,
    'youtube' => false,
    'twitter' => false,
    'instagram' => true,
    'flickr' => false,
];
```

Set the service you want to disable to false.

## Using Social feeds

When activated the feeds can be accessed by the following url:

* /actions/apt-social-feeds/facebook
* /actions/apt-social-feeds/youtube
* /actions/apt-social-feeds/instagram
* /actions/apt-social-feeds/twitter
* /actions/apt-social-feeds/flickr

The feeds has a default limit of 6.
If you need more add ?limit=[your limit] to the query string

Brought to you by [Thomas Sømoen](https://apt.no/)
