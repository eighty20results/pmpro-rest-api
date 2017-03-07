=== REST API Endpoints for Paid Memberships Pro ===
Contributors: eighty20results
Tags: rest api, paid memberships pro, pmpro
Requires at least: 4.4
Tested up to: 4.7.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress REST API v2 endpoints for the Paid Memberships Pro plugin

== Description ==

This plugin adds basic REST API support for the free [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/)
plugin. It currently extends Paid Memberships Pro to support the same endpoints/API calls as the plugin itself includes
for XMLRPC.

The plan is to add more API endpoints as needed/requested.

Please submit your required endpoints [on our development site](https://eighty20results.com/submit-plugin-feature-request/)

== Installation ==

1. Upload the `pmpro-rest-api` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What endpoints/routes does this plugin support? =

The REST API Endpoints for Paid Memberships Pro plugin currently supports 2 different endpoints and provides the same
functionality as the built-in XMLRPC api  in the core Paid Memberships Pro plugin.

That is, there is support for the `hasaccess` endpoint, and for the `getlevelforuser` endpoint

= How would I use the hasaccess endpoint/route? =

The `hasaccess` endpoint has two (2) required variables that needs to be included in the readable (GET) request route:
1. `post ID`
1. `user ID`

Both arguments are a numeric value representing the post ID and user ID to check access on behalf of.
against.

The following example URI would return an array containing the access permissing (true/false), the membership ID, and the name of the membership level.

`
http://example.com/wp-json/pmpro/v1/hasaccess/<post_id>/<user_id>
`
Result: `[true,["2"],["Level Name for Membership Level with ID 2"]]`

= How would I use the getlevelforuser endpoint/route? =

The `getlevelforuser` endpoint accepts one required variable that needs to be included in the readable request route
1. `user ID`

This is a numerical value representing the WordPress user ID to return the membership level information for.

The following example URI would return an array containing the membership level definition for the specified user ID.

`http://example.com/wp-json/pmpro/v1/getlevelforuser/<user_id>`

Result: `{"ID":"2","id":"2","subscription_id":"47","name":"Membership Level Name","description":"Membership level description.","expiration_number":"0","expiration_period":"","allow_signups":"1","initial_payment":"1.00","billing_amount":"1.50","cycle_number":"1","cycle_period":"Month","billing_limit":"0","trial_amount":"0.00","trial_limit":"0","code_id":"6","startdate":"1436162400","enddate":null}`

== Changelog ==
== 1.2 ==

* ENHANCEMENT: Update plugin header
* ENHANCEMENT: Update licence text
* ENHANCEMENT: Add conditional definition of pmproRestAPI class
* ENHANCEMENT: Refactor for WP code style
* ENHANCEMENT: Add stub for plugin activation
* ENHANCEMENT: Add stub for plugin deactivation
* ENHANCEMENT: Add initial function to support different post types, including Custom Post Types (CPTs)
* ENHANCEMENT: Simplify routes for endpoints/requests
* ENHANCEMENT: Use plugin slug as translation domain
* ENHANCEMENT: Add & update the readme.txt file

== 1.1 ==

* BUG/FIX: Endpoint fixed for Checking access to a post for a user of a certain level (hasaccess)
* BUG/FIX: Endpoint fixed for getting the membership level for a specific user ID (getmembershiplevel)

