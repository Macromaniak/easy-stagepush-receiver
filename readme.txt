=== Easy StagePush Receiver ===
Contributors: anandhunadesh, phaseswpdev
Tags: staging, publishing, content-sync, acf, media, rest-api
Requires at least: 3.0.1
Tested up to: 6.8.1
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Receives content pushed from a development or staging WordPress site using the Easy StagePush Sender plugin.

=== Description ===

Easy StagePush Receiver is a companion plugin for [Easy StagePush Sender](https://wordpress.org/plugins/easy-stagepush-sender/). It listens for incoming REST API calls and saves post content, ACF fields, taxonomies, media, and metadata on the live/production site.

This plugin allows you to keep your production content up to date by syncing from your development or staging environment, all while retaining full control over your content structure.

**Important:** This plugin does not create post types, taxonomies, or ACF field groups. Your live site must match the structure of your dev/staging site for content to display and function properly.

=== Features ===

* REST endpoint to receive content pushed from staging/dev
* Automatically creates or updates posts and pages
* Syncs ACF fields including Flexible Content, Relationships, Groups
* Downloads and attaches media including featured images
* Applies taxonomy terms and page templates
* Preserves parent/child relationships
* Compatible with Yoast SEO metadata

=== Usage ===

1. Install and activate **Easy StagePush Receiver** on your **live/production** WordPress site.
2. Make sure [Easy StagePush Sender](https://wordpress.org/plugins/easy-stagepush-sender/) is installed and configured on your **dev/staging** site.
3. Ensure that all required post types, taxonomies, and ACF field groups are registered on the production site.
4. When content is published from the sender site, it will be received and processed via REST API

=== Frequently Asked Questions ===

= Do I need to configure anything in this plugin? =

No configuration is necessary. Just install and activate it on the live site.

= Can this plugin create new post types or ACF fields? =

No. This plugin assumes the content structure is already present on the live site.

= Is this secure? =

This version accepts unauthenticated POST requests. You are encouraged to restrict access to the REST endpoint using server-level controls or future authentication support.

=== Support ===

If you have any questions, issues, or feature requests, please feel free to reach out at anandhu.natesh@gmail.com / anandhu.nadesh@gmail.com  
If you want to contribute, please create an issue or submit a pull request on [Github](https://github.com/Macromaniak/easy-stagepush-receiver).

=== License ===

This plugin is licensed under the GPLv2 or later. You are free to use, modify, and distribute this plugin under the terms of the license.
