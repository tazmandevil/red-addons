<?php
/**
 * Name: Random location, Norway
 * Note: Clone of Random Planet, Empirial Version
 * Description: Sample Red Matrix plugin/addon. Set a random location in Norway when posting.
 * Old description: Set a random planet from the Emprire when posting.
 * Version: 1.0
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Tony Baldwin <https://free-haven.org/profile/tony>
 * Author: Haakon Meland Eriksen <https://friendicared.net/channel/haakon>
 */


function locations_load() {

	/**
	 * 
	 * Our demo plugin will attach in three places.
	 * The first is just prior to storing a local post.
	 *
	 */

	register_hook('post_local', 'addon/locations/locations.php', 'locations_post_hook');

	/**
	 *
	 * Then we'll attach into the plugin settings page, and also the 
	 * settings post hook so that we can create and update
	 * user preferences.
	 *
	 */

	register_hook('feature_settings', 'addon/locations/locations.php', 'locations_settings');
	register_hook('feature_settings_post', 'addon/locations/locations.php', 'locations_settings_post');

	logger("loaded locations");
}


function locations_unload() {

	/**
	 *
	 * unload unregisters any hooks created with register_hook
	 * during load. It may also delete configuration settings
	 * and any other cleanup.
	 *
	 */

	unregister_hook('post_local',    'addon/locations/locations.php', 'locations_post_hook');
	unregister_hook('feature_settings', 'addon/locations/locations.php', 'locations_settings');
	unregister_hook('feature_settings_post', 'addon/locations/locations.php', 'locations_settings_post');


	logger("removed locations");
}



function locations_post_hook($a, &$item) {

	/**
	 *
	 * An item was posted on the local system.
	 * We are going to look for specific items:
	 *      - A status post by a profile owner
	 *      - The profile owner must have allowed our plugin
	 *
	 */

	logger('locations invoked');

	if(! local_user())   /* non-zero if this is a logged in user of this system */
		return;

	if(local_user() != $item['uid'])    /* Does this person own the post? */
		return;

	if($item['parent'])   /* If the item has a parent, this is a comment or something else, not a status post. */
		return;

	/* Retrieve our personal config setting */

	$active = get_pconfig(local_user(), 'locations', 'enable');

	if(! $active)
		return;

	/**
	 *
	 * OK, we're allowed to do our stuff.
	 * Here's what we are going to do:
	 * load the list of timezone names, and use that to generate a list of world planets.
	 * Then we'll pick one of those at random and put it in the "location" field for the post.
	 *
	 */
	 
	 /**
	 * Regional county capital cities in Norway. Oslo is listed twice, because it is both capital in Akershus and Oslo itself.
	 */
	 

	$locations = array(
			'Oslo',
			'Arendal',
			'Drammen',
			'Vadsø',
			'Hamar',
			'Bergen',
			'Molde',
			'Bodø',
			'Steinkjer',
			'Lillehammer',
			'Oslo',
			'Stavanger',
			'Hermansverk',
			'Trondheim',
			'Skien',
			'Tromsø',
			'Kristiansand',
			'Tønsberg',
			'Sarpsborg',
		);

	$location = array_rand($locations,1);
	$item['location'] = '#[url=http://www.norge.no]' . $locations[$location] . '[/url]';

	return;
}




/**
 *
 * Callback from the settings post function.
 * $post contains the $_POST array.
 * We will make sure we've got a valid user account
 * and if so set our configuration setting for this person.
 *
 */

function locations_settings_post($a,$post) {
	if(! local_user())
		return;
	if($_POST['locations-submit']) {
		set_pconfig(local_user(),'locations','enable',intval($_POST['locations']));
		info( t('Locations Settings updated.') . EOL);
	}
}


/**
 *
 * Called from the Plugin Setting form. 
 * Add our own settings info to the page.
 *
 */



function locations_settings(&$a,&$s) {

	if(! local_user())
		return;

	/* Add our stylesheet to the page so we can make our settings look nice */

	$a->page['htmlhead'] .= '<link rel="stylesheet"  type="text/css" href="' . $a->get_baseurl() . '/addon/locations/locations.css' . '" media="all" />' . "\r\n";

	/* Get the current state of our config variable */

	$enabled = get_pconfig(local_user(),'locations','enable');

	$checked = (($enabled) ? ' checked="checked" ' : '');

	/* Add some HTML to the existing form */

	$s .= '<div class="settings-block">';
	$s .= '<h3>' . t('Locations Settings') . '</h3>';
	$s .= '<div id="locations-enable-wrapper">';
	$s .= '<label id="locations-enable-label" for="locations-checkbox">' . t('Enable Locations Plugin') . '</label>';
	$s .= '<input id="locations-checkbox" type="checkbox" name="locations" value="1" ' . $checked . '/>';
	$s .= '</div><div class="clear"></div>';

	/* provide a submit button */

	$s .= '<div class="settings-submit-wrapper" ><input type="submit" name="locations-submit" class="settings-submit" value="' . t('Submit') . '" /></div></div>';

}
