<?php
/**
 * @component jOpenSim
 * @copyright Copyright (C) 2021 FoTo50 https://www.jopensim.com/
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

namespace jOpenSim\Component\OpenSim\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;
//use jOpenSim\Component\OpenSim\Administrator\Extension\jOpenSimGridDB;
use jOpenSim\Component\OpenSim\Administrator\Extension\OpenSim;
use jOpenSim\Component\OpenSim\Administrator\Helper\jOpenSimMoneyHelper;
use jOpenSim\Component\OpenSim\Site\Helper\InterfaceGroupsHelper;
use Joomla\CMS\Log\Log;

/**
 * main helper class for com_opensim
 *
 * @since  0.4.0.0
 */
class jOpenSimHelper
{
	public $gridDB;
	public $moneyEnabled;
	public $settingsData; // all config values of com_opensim
	public $zerouid = "00000000-0000-0000-0000-000000000000";

	public function __construct() {
//		$start	= hrtime(true);
		$this->getSettingsData();
		$this->opensim		= new opensim();
		$this->gridDB		= $this->opensim->_osgrid_db;
		$this->moneyEnabled	= $this->moneyEnabled();
		if(JDEBUG) $this->addLogger();
//		$end	= hrtime(true);
//		$eta	= $end - $start; // nanoseconds
//		$msec	= $eta / 1e+6;
		
//		if(JDEBUG) Log::add('jOpenSimHelper __construct demo: '.$demo, Log::DEBUG, 'jopensim-debug');
//		if(JDEBUG) Log::add('jOpenSimHelper __construct demo-array: '.var_export($demo,TRUE), Log::DEBUG, 'jopensim-debug');
	}

	public function getSettingsData() {
		// Lets load the data if it doesn't already exist
		if (empty( $this->settingsData )) {
			$settings = array();

			$params								= ComponentHelper::getParams('com_opensim');

			$settings['gridname']				= $params->get('jopensim_gridname','OpenSim');
			$settings['tp_host']				= $params->get('opensim_host'); // for TP links without any http or so
			$settings['opensim_host']			= $params->get('opensim_host');
			if(substr($settings['opensim_host'],0,4) != "http") $settings['opensim_host'] = "http:/"."/".$settings['opensim_host'];
			$settings['robust_port']			= $params->get('robust_port');
			$settings['getTextureEnabled']		= $params->get('getTextureEnabled');
			$settings['getTextureFormat']		= $params->get('getTextureFormat','png');
			$settings['getTextureCache']		= $params->get('getTextureCache',0);

			$settings['oshost']					= $params->get('opensim_host');
			$settings['osport']					= $params->get('opensim_port');

			$settings['hg_override']			= $params->get('hg_override');
			$settings['robust_hghost']			= $params->get('robust_hghost');
			$settings['robust_hgport']			= $params->get('robust_hgport');



			$settings['osdbhost']				= $params->get('opensimgrid_dbhost');
			$settings['osdbuser']				= $params->get('opensimgrid_dbuser');
			$settings['osdbpasswd']				= $params->get('opensimgrid_dbpasswd');
			$settings['osdbname']				= $params->get('opensimgrid_dbname');
			$settings['osdbport']				= $params->get('opensimgrid_dbport',3306);

			$settings['loginscreen_layout']					= $params->get('loginscreen_layout','modular');
			$settings['loginscreen_image']					= $params->get('loginscreenbackground');
			$settings['loginscreen_boxborder_inline']		= $params->get('loginscreen_boxborder_inline');
			$settings['loginscreen_color']					= $params->get('jopensim_loginscreen_color_background_screen');
			$settings['loginscreen_msgbox_color']			= $params->get('jopensim_loginscreen_color_background_box');
			$settings['loginscreen_text_color']				= $params->get('jopensim_loginscreen_color_text');
			$settings['loginscreen_xdays']					= $params->get('loginscreen_show_uniquevisitors_days');

			$settings['jopensim_loginscreen_matrix_fx']			= $params->get('jopensim_loginscreen_matrix_fx', '0');
			$settings['jopensim_loginscreen_table_optimize']	= $params->get('jopensim_loginscreen_table_optimize', '1');

			$settings['loginscreen_msgbox_title']				= $params->get('loginscreen_messagebox_title');
			$settings['loginscreen_msgbox_message']				= $params->get('loginscreen_messagebox_content');
			$settings['loginscreen_msgbox_title_background']	= $params->get('jopensim_loginscreen_color_background_title');
			$settings['loginscreen_msgbox_title_text']			= $params->get('jopensim_loginscreen_color_text_title');
			$settings['jopensim_loginscreen_color_links']       = $params->get('jopensim_loginscreen_color_links');
			$settings['jopensim_loginscreen_boxborder_title']   = $params->get('jopensim_loginscreen_boxborder_title');
			$settings['loginscreen_box_padding']         		= $params->get('loginscreen_box_padding',0);
			$settings['loginscreen_box_radius']         		= $params->get('loginscreen_box_radius',0);
			$settings['loginscreen_title_padding_horizontal']   = $params->get('loginscreen_title_padding_horizontal', 0);
            $settings['loginscreen_title_padding_vertical']     = $params->get('loginscreen_title_padding_vertical', 0);
			$settings['loginscreen_title_radius']         		= $params->get('loginscreen_title_radius',0);
			$settings['jopensim_loginscreen_customcss']         = $params->get('jopensim_loginscreen_customcss','');

			$settings['enableremoteadmin']		= $params->get('enableremoteadmin');
			$settings['remotehost']				= $params->get('remotehost');
			$settings['remoteport']				= $params->get('remoteport');
			$settings['remotepasswd']			= $params->get('remotepasswd');
			$settings['remoteadmin_version']	= $params->get('remoteadmin_version');
			$settings['remoteadmin_message']	= $params->get('remoteadmin_message');
			$settings['remoteadmin_addregion']	= $params->get('remoteadmin_addregion');

			$settings['addons_messages']		= $params->get('addons_messages');
			$settings['addons_profile']			= $params->get('addons_profile');
			$settings['addons_groups']			= $params->get('addons_groups');
			$settings['addons_search']			= $params->get('addons_search');
			$settings['addons_inworldauth']		= $params->get('addons_inworldauth');
			$settings['addons_terminalchannel']	= $params->get('addons_terminalchannel');
			$settings['addons_identminutes']	= $params->get('addons_identminutes');
			$settings['addons_currency']		= $params->get('addons_currency');
			$settings['addons_authorize']		= $params->get('addons_authorize');
			$settings['addons_authorizehg']		= $params->get('addons_authorizehg');
			$settings['auth_regionrating']		= $params->get('auth_regionrating');
			$settings['addons']					= $settings['addons_messages'] + ($settings['addons_profile']*2) + ($settings['addons_groups']*4) + ($settings['addons_inworldauth']*8) + ($settings['addons_search']*16) + ($settings['addons_currency']*32) + ($settings['addons_authorize']*64);

			$settings['auth_minage']			= $params->get('auth_minage');
			$settings['auth_link']				= $params->get('auth_link');

			$settings['jopensim_userhome_region']		= $params->get('jopensim_userhome_region');
			$settings['jopensim_userhome_x']			= $params->get('jopensim_userhome_x');
			$settings['jopensim_userhome_y']			= $params->get('jopensim_userhome_y');
			$settings['jopensim_userhome_z']			= $params->get('jopensim_userhome_z',21);
			$settings['jopensim_defaultuserlevel']		= $params->get('jopensim_defaultuserlevel');
			$settings['jopensim_usersetting_flag3']		= $params->get('jopensim_usersetting_flag3');
			$settings['jopensim_usersetting_flag4']		= $params->get('jopensim_usersetting_flag4');
			$settings['jopensim_usersetting_flag5']		= $params->get('jopensim_usersetting_flag5');
			$settings['jopensim_usersetting_flag6']		= $params->get('jopensim_usersetting_flag6');
			$settings['jopensim_defaultusertype']		= $params->get('jopensim_defaultusertype');
			$settings['jopensim_usersetting_flags']		= $settings['jopensim_usersetting_flag3'] +
														  $settings['jopensim_usersetting_flag4'] +
														  $settings['jopensim_usersetting_flag5'] +
														  $settings['jopensim_usersetting_flag6'] +
														  $settings['jopensim_defaultusertype'];
			$settings['jopensim_usersetting_title']		= $params->get('jopensim_usersetting_title');
			$settings['jopensim_selectableavatar']		= $params->get('jopensim_selectableavatar',0);

			$settings['jopensim_maps_cacheage']			= $params->get('jopensim_maps_cacheage',0);
			$settings['jopensim_maps_width']			= $params->get('jopensim_maps_width',600);
			$settings['jopensim_maps_width_style']		= $params->get('jopensim_maps_width_style','px');
			$settings['jopensim_maps_height']			= $params->get('jopensim_maps_height',400);
			$settings['jopensim_maps_height_style']		= $params->get('jopensim_maps_height_style','px');
			$settings['jopensim_maps_homename']			= $params->get('jopensim_maps_homename',Text::_('JOPENSIM_MAPS_DEFAULTNAME'));
			$settings['jopensim_maps_copyright']		= $params->get('jopensim_maps_copyright');
			$settings['jopensim_maps_homex']			= $params->get('jopensim_maps_homex',1000);
			$settings['jopensim_maps_homey']			= $params->get('jopensim_maps_homey',1000);
			$settings['jopensim_maps_offsetx']			= $params->get('jopensim_maps_offsetx',0);
			$settings['jopensim_maps_offsety']			= $params->get('jopensim_maps_offsety',0);
			$settings['jopensim_maps_zoomstart']		= $params->get('jopensim_maps_zoomstart',8);
			$settings['jopensim_maps_bubble_bgcolor']	= $params->get('jopensim_maps_bubble_bgcolor','#000000');
			$settings['jopensim_maps_bubble_alpha']		= $params->get('jopensim_maps_bubble_alpha','50');
			$settings['jopensim_maps_bubble_textcolor']	= $params->get('jopensim_maps_bubble_textcolor','#ffffff');
			$settings['jopensim_maps_bubble_linkcolor']	= $params->get('jopensim_maps_bubble_linkcolor','#ffffff');
			$settings['jopensim_maps_bubble_color']		= $settings['jopensim_maps_bubble_bgcolor'];
			$settings['jopensim_maps_showteleport']		= $params->get('jopensim_maps_showteleport',1);
			$settings['jopensim_maps_showcoords']		= $params->get('jopensim_maps_showcoords',0);
			$settings['jopensim_maps_link2article']		= $params->get('jopensim_maps_link2article',1);
			$settings['jopensim_maps_link2article_icon']= $params->get('jopensim_maps_link2article_icon',1);
			$settings['jopensim_maps_water']			= $params->get('jopensim_maps_water');
			if(!$settings['jopensim_maps_water']) {
				$settings['jopensim_maps_water']		= Uri::base(true)."/media/com_opensim/assets/images/water.jpg";
				$settings['jopensim_maps_displaytype']	= "auto";
				$settings['jopensim_maps_displayrepeat']= 1;
			} else {
				$settings['jopensim_maps_water']		= Uri::base(true)."/".$settings['jopensim_maps_water'];
				$settings['jopensim_maps_displaytype']	= $params->get('jopensim_maps_displaytype');
				$settings['jopensim_maps_displayrepeat']= $params->get('jopensim_maps_displayrepeat');
			}
			$settings['jopensim_maps_varregions']		= $params->get('jopensim_maps_varregions');
			$settings['jopensim_maps_visibility']		= $params->get('jopensim_maps_visibility',1);

			$settings['profile_display']				= $params->get('profile_display');
			$settings['matureprofiles']					= $params->get('matureprofiles',0);
			$settings['profile_images']					= $params->get('profile_images');
			$settings['profile_images_maxwidth']		= $params->get('profile_images_maxwidth',512);
			$settings['profile_images_maxheight']		= $params->get('profile_images_maxheight',512);

			$settings['jopensimmoney_currencyname']				= $params->get('jopensimmoney_currencyname');
			$settings['jopensimmoneybanker']					= $params->get('jopensimmoneybanker');
			$settings['jopensimmoney_bankername']				= $params->get('jopensimmoney_bankername');
			$settings['jopensimmoney_startbalance']				= $params->get('jopensimmoney_startbalance',0);
			$settings['jopensimmoney_upload']					= $params->get('jopensimmoney_upload',0);
			$settings['jopensimmoney_groupcreation']			= $params->get('jopensimmoney_groupcreation',0);
			$settings['jopensimmoney_groupdividend']			= $params->get('jopensimmoney_groupdividend',0);
			$settings['jopensimmoney_zerolines']				= $params->get('jopensimmoney_zerolines',3);
			$settings['jopensimmoney_buycurrency']				= $params->get('jopensimmoney_buycurrency',0);
			$settings['jopensimmoney_buycurrency_url']			= $params->get('jopensimmoney_buycurrency_url',0);
			$settings['jopensimmoney_buycurrency_customized']	= $params->get('jopensimmoney_buycurrency_customized',0);
			$settings['jopensimmoney_buycurrency_custom_url']	= $params->get('jopensimmoney_buycurrency_custom_url',URI::root());
			$settings['jopensimmoney_buycurrency_custom_msg']	= $params->get('jopensimmoney_buycurrency_custom_msg',Text::_('JOPENSIM_MONEY_BUYCURRENCY_MSG'));
			$settings['jopensimmoney_sendgridbalancewarning']	= $params->get('jopensimmoney_sendgridbalancewarning',0);
			$settings['jopensimmoney_warningrecipient']			= $params->get('jopensimmoney_warningrecipient','');
			$settings['jopensimmoney_warningsubject']			= $params->get('jopensimmoney_warningsubject','Grid Balance Warning');

			$settings['groupMinDividend']						= $params->get('jopensimmoney_groupdividend',0);

// search options now moved to database table
//			$settings['search_objects']					= $params->get('search_objects');
			$settings['search_objects']					= $this->getSearchSetting('JOPENSIM_SEARCH_OBJECTS');
//			$settings['search_parcels']					= $params->get('search_parcels');
			$settings['search_parcels']					= $this->getSearchSetting('JOPENSIM_SEARCH_PARCELS');
//			$settings['search_parcelsales']				= $params->get('search_parcelsales');
			$settings['search_parcelsales']				= $this->getSearchSetting('JOPENSIM_SEARCH_PARCELSALES');
//			$settings['search_events']					= $params->get('search_events');
			$settings['search_events']					= $this->getSearchSetting('JOPENSIM_SEARCHEVENTS');
//			$settings['search_classified']				= $params->get('search_classified');
			$settings['search_classified']				= $this->getSearchSetting('JOPENSIM_SEARCHCLASSIFIED');
//			$settings['search_regions']					= $params->get('search_regions');
			$settings['search_regions']					= $this->getSearchSetting('JOPENSIM_SEARCH_REGIONS');
			$settings['events_post_access']				= $params->get('events_post_access');
			$settings['events_grouppower']				= $params->get('events_grouppower');
			$settings['eventdescriptioneditor']			= $params->get('eventdescriptioneditor',0);
			$settings['eventtimedefault']				= $params->get('eventtimedefault');
			$settings['listmatureevents']				= $params->get('listmatureevents');


			$settings['classified_hide']				= $params->get('classified_hide',604800);
			$settings['classified_sort']				= $params->get('classified_sort','creationdate');
			$settings['classified_order']				= $params->get('classified_order','DESC');
			$settings['classified_guide']				= $params->get('classified_guide',1);
			$settings['classified_guide_mature']		= $params->get('classified_guide_mature',0);
			$settings['classified_images']				= $params->get('classified_images');
			$settings['classified_images_maxwidth']		= $params->get('classified_images_maxwidth',512);
			$settings['classified_images_maxheight']	= $params->get('classified_images_maxheight',512);

			$settings['lastnametype']					= $params->get('lastnametype');
			$settings['lastnamelist']					= $params->get('lastnamelist');
			$settings['jopensim_defaultlandlevel']		= $params->get('jopensim_defaultlandlevel',0);
			$settings['userchange_firstname']			= $params->get('userchange_firstname');
			$settings['userchange_lastname']			= $params->get('userchange_lastname');
			$settings['userchange_email']				= $params->get('userchange_email');
			$settings['userchange_password']			= $params->get('userchange_password');

			$settings['jopensim_debug_path']			= $params->get('jopensim_debug_path',JPATH_SITE."/components/com_opensim/");
			if(substr($settings['jopensim_debug_path'],-1) != "/") $settings['jopensim_debug_path'] = $settings['jopensim_debug_path']."/"; // ensure it ends with a slash
			$settings['jopensim_debug_reminder']		= $params->get('jopensim_debug_reminder');
			$settings['jopensim_debug_access']			= $params->get('jopensim_debug_access');
			$settings['jopensim_debug_input']			= $params->get('jopensim_debug_input');
			$settings['jopensim_debug_profile']			= $params->get('jopensim_debug_profile');
			$settings['jopensim_debug_groups']			= $params->get('jopensim_debug_groups');
			$settings['jopensim_debug_search']			= $params->get('jopensim_debug_search');
			$settings['jopensim_debug_messages']		= $params->get('jopensim_debug_messages');
			$settings['jopensim_debug_currency']		= $params->get('jopensim_debug_currency');
			$settings['jopensim_debug_terminal']		= $params->get('jopensim_debug_terminal');
			$settings['jopensim_debug_other']			= $params->get('jopensim_debug_other');
			$settings['jopensim_debug_settings']		= $params->get('jopensim_debug_settings');
			$settings['jopensim_supportaccesskey']		= $params->get('jopensim_supportaccesskey');

			$settings['grp_readkey']					= $params->get('grp_readkey');
			$settings['grp_writekey']					= $params->get('grp_writekey');
			$settings['grp_everyone']					= $params->get('grp_everyone');

			$settings['lastnames']						= array();
			if($settings['lastnamelist']) {
				$namelist	= explode("\n",$settings['lastnamelist']);
				foreach($namelist AS $key => $val) {
					if(trim($val)) $settings['lastnames'][]	= trim($val);
				}
			}

			$this->settingsData = $settings;
		}
		return $this->settingsData;
	}

	public function getSearchSetting($searchCategory) {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query->select($db->quoteName('a.state'));
		$query->from($db->quoteName('#__opensim_search_options', 'a'));
		$query->where($db->quoteName('a.searchoption')." = ".$db->quote($searchCategory));
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getAddonSettings() {
		$addon	= $this->settingsData['addons'];

		$addons['messages']		= $addon & 1;
		$addons['profile']		= $addon & 2;
		$addons['groups']		= $addon & 4;
		$addons['inworldident']	= $addon & 8;
		$addons['search']		= $addon & 16;
		$addons['currency']		= $addon & 32;

		return $addons;
	}

	public function getUserDataList() {
		$app = Factory::getApplication();
		if(!$this->gridDB) return FALSE;
		$opensim = $this->opensim;

		$limitstart					= $app->getUserStateFromRequest( 'users_limitstart', 'limitstart', 0, 'int' );
		$limit						= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
//		$orderby					= "UserAccounts.Created";
		$orderby					= $app->getUserStateFromRequest( 'users_filter_order', 'filter_order', 'UserAccounts.Created', 'STR' );
		$orderdir					= $app->getUserStateFromRequest( 'users_filter_order_Dir', 'filter_order_Dir', 'desc', 'STR' );
		$search						= $app->getUserStateFromRequest( 'users_filter_search', 'filter_search', '', 'STR' );

		$this->userquery			= $this->opensim->getUserQuery($search,$orderby,$orderdir);
		$this->UserQueryObject		= $this->opensim->getUserQueryObject($search,$orderby,$orderdir);

//		if(!$orderby) $orderby		= "UserAccounts.Created";
//		if(!$orderdir) $orderdir	= "DESC";
//		if(!$limit) $limit			= 20;

//		$userquery					= $this->userquery." ORDER BY ".$orderby." ".$orderdir." LIMIT ".$limitstart.",".$limit;

//		error_log("userquery: ".$userquery);

//		$this->gridDB->setQuery($userquery);

		$userquery = $this->userquery." ORDER BY ".$orderby." ".$orderdir;

		$this->gridDB->setQuery($userquery,$limitstart,$limit);

		try {
			$this->os_user = $this->gridDB->loadAssocList();
		} catch(Exception $e) {
			$errormsg = $e->getMessage();
			Factory::getApplication()->enqueueMessage($errormsg." (".$this->userquery.")","error");
			return array();
		}

		foreach($this->os_user AS $userkey => $user) {
			$statusquery = $opensim->userGridStatusQuery($user['userid']);
			$this->gridDB->setQuery($statusquery);
			$userstatus = $this->gridDB->loadAssoc();
			if(!is_array($userstatus)) {
				$userstatus['last_login'] = "";
				$userstatus['last_logout'] = "";
			}
			$userstatus['online'] = $opensim->getUserPresence($user['userid']);
			$this->os_user[$userkey] = array_merge($this->os_user[$userkey],$userstatus);
		}

		return $this->os_user;
	}

	public function getUserData($userid) {
		$userdata = array();
		$griddata = array();
		$authdata = array();
		$opensim = $this->opensim;
		$query = $opensim->getUserDataQuery($userid);
		$this->gridDB->setQuery($query['userdata']);
		$userdata = $this->gridDB->loadAssoc();
		$this->gridDB->setQuery($query['griddata']);
		$griddata = $this->gridDB->loadAssoc();
		$this->gridDB->setQuery($query['authdata']);
		$authdata = $this->gridDB->loadAssoc();
		if(!is_array($griddata)) $griddata = $this->emptyGridData(); // in case no home region is defined and/or user never was online yet, give an empty array to prevent php warnings
		if(!is_array($userdata)) $userdata = $this->emptyUserData();
		if(!is_array($authdata)) $authdata = $this->emptyAuthData();
		$juserdata = $this->getJuserData($userid);
		$retval = array_merge($userdata,$griddata,$authdata,$juserdata);
		return $retval;
	}

	public function getJuserData($uuid) { // Collect settings from Joomlas DB
		$db = Factory::getDBO();
		$query = sprintf("SELECT im2email,visible,timezone FROM  #__opensim_usersettings WHERE `uuid` = '%s'",$uuid);
		$db->setQuery($query);
		$db->execute();
		if($uuid && $db->getNumRows() == 1) {
			$jUserData = $db->loadAssoc();
		} else {
			$jUserData = array( 'im2email'	=> 0,
								'visible'	=> 0,
								'timezone'	=> "");
		}
		return $jUserData;
	}

	public function getUserSettings($userid) {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName('#__opensim_usersettings.im2email'));
		$query->select($db->quoteName('#__opensim_usersettings.visible'));
		$query->select($db->quoteName('#__opensim_usersettings.timezone'));
		$query->from($db->quoteName('#__opensim_usersettings'));
		$query->where($db->quoteName('#__opensim_usersettings.uuid').' = '.$db->quote($userid));
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows() == 0) { // we need to avoid notices for fresh created users
			$settings				= array();
			$settings['im2email']	= null;
			$settings['visible']	= null;
			$settings['timezone']	= null;
		} else {
			$settings = $db->loadAssoc();
		}
		return $settings;
	}

	public function prepareUserdata($data) {
		$data['jopensim_usersetting_flag3'] = (!array_key_exists("jopensim_usersetting_flag3",$data)) ? 0:4; // avoid php notices where possible
		$data['jopensim_usersetting_flag4'] = (!array_key_exists("jopensim_usersetting_flag4",$data)) ? 0:8;
		$data['jopensim_usersetting_flag5'] = (!array_key_exists("jopensim_usersetting_flag5",$data)) ? 0:16;
		$data['jopensim_usersetting_flag6'] = (!array_key_exists("jopensim_usersetting_flag6",$data)) ? 0:32;
		if(!array_key_exists("jopensim_usersetting_accounttype",$data)) $data['jopensim_usersetting_accounttype'] = 0;

		$data['UserFlags']	= intval($data['jopensim_usersetting_flag3'])
							+ intval($data['jopensim_usersetting_flag4'])
							+ intval($data['jopensim_usersetting_flag5'])
							+ intval($data['jopensim_usersetting_flag6'])
							+ intval($data['jopensim_usersetting_accounttype']);

		if(array_key_exists("Homeregion",$data)) {
			$homeregion	= explode("|",$data['Homeregion']);
			$data['GridUser']['HomeRegionID']	= $homeregion[0];
			if(array_key_exists("userhomeposition_locX",$data) &&
			   array_key_exists("userhomeposition_locY",$data) &&
			   floatval($data['userhomeposition_locX']) > 0 &&
			   floatval($data['userhomeposition_locY']) > 0) {
			   	$data['GridUser']['HomePosition']	= "<".floatval($data['userhomeposition_locX']).",".floatval($data['userhomeposition_locY']).",".floatval($data['userhomeposition_locZ']).">";
			} else { // no landing point specified, get the default landing point of the region
				$mapinfo	= $this->getRegionDetails($data['GridUser']['HomeRegionID']);
				if(is_array($mapinfo) && array_key_exists("defaultpos",$mapinfo)) {
					$data['GridUser']['HomePosition']	= $mapinfo['defaultpos'];
				} else { // absolutely last possibility as fallback
					$data['GridUser']['HomePosition']	= "<128,128,21>";
				}
			}
		} else {
			$data['GridUser']['HomeRegionID']	= $this->settingsData['jopensim_userhome_region'];
			$mapinfo	= $this->getRegionDetails($data['GridUser']['HomeRegionID']);
			if(is_array($mapinfo) && array_key_exists("defaultpos",$mapinfo)) {
				$data['GridUser']['HomePosition']	= $mapinfo['defaultpos'];
			} else { // absolutely last possibility as fallback
				$data['GridUser']['HomePosition']	= "<".$this->settingsData['jopensim_userhome_x'].",".$this->settingsData['jopensim_userhome_y'].",".$this->settingsData['jopensim_userhome_z'].">";
			}
		}
		$data['GridUser']['HomeLookAt']	= "<0,0,0>";

		if(array_key_exists("Password",$data) && array_key_exists("Password2",$data) && $data['Password'] == $data['Password2']) {
			$data['password']	= $data['Password'];
		}
		if(array_key_exists("userlevel",$data)) {
			$data['UserLevel']	= $data['userlevel'];
		} else {
			$data['UserLevel']	= $this->settingsData['jopensim_defaultuserlevel'];
		}
		return $data;
	}

	public function updateUsersettings($data) {
		$db		= Factory::getDBO();

		// check if existing
		$query	= $db->getQuery(true);
		$query->select($db->quoteName('#__opensim_usersettings.uuid'));
		$query->from($db->quoteName('#__opensim_usersettings'));
		$query->where($db->quoteName('#__opensim_usersettings.uuid').' = '.$db->quote($data['uuid']));
		$db->setQuery($query);
		$db->execute();
		$founduuid = $db->getNumRows();

		$im2email	= (array_key_exists("jopensim_usersetting_im2email",$data)) ? 1:0;
		$visible	= (array_key_exists("jopensim_usersetting_profilevisible",$data)) ? 1:0;
		$timezone	= (array_key_exists("jopensim_usersetting_timezone",$data) && $data['jopensim_usersetting_timezone']) ? $data['jopensim_usersetting_timezone']:null;

		$updateNulls	= true;

		$usersettings			= new \stdClass();
		$usersettings->uuid		= $data['uuid'];
		$usersettings->im2email	= $im2email;
		$usersettings->visible	= $visible;
		$usersettings->timezone	= $timezone;
		
		$debug	= var_export($usersettings,TRUE);
		if(JDEBUG) Log::add('helper usersettings: '.$debug, Log::DEBUG, 'jopensim-debug');

		if($founduuid > 0) {
			$db->updateObject('#__opensim_usersettings',$usersettings,'uuid',$updateNulls);
		} else {
			$db->insertObject('#__opensim_usersettings',$usersettings);
		}
	}

	public function updateUserprofile($data) {
		$db		= Factory::getDBO();

		// check if existing
		$query	= $db->getQuery(true);
		$query->select($db->quoteName('#__opensim_userprofile.avatar_id'));
		$query->from($db->quoteName('#__opensim_userprofile'));
		$query->where($db->quoteName('#__opensim_userprofile.avatar_id').' = '.$db->quote($data['avatar_id']));
		$db->setQuery($query);
		$db->execute();
		$founduuid = $db->getNumRows();

		$allowpublish	= (array_key_exists("allowpublish",$data)) ? 1:0;
		$show1stimage	= (array_key_exists("show1stimage",$data)) ? 1:0;

		$updateNulls	= true;

		$userprofile	= new \stdClass();
		$userprofile->avatar_id				= $data['avatar_id'];
		$userprofile->aboutText				= $data['aboutText'];
		$userprofile->allowPublish			= $allowpublish;
		$userprofile->maturePublish			= $data['maturepublish'];
		$userprofile->url					= $data['web'];
		$userprofile->wantmask				= $data['wantmask'];
		$userprofile->wanttext				= $data['wanttext'];
		$userprofile->skillsmask			= $data['skillsmask'];
		$userprofile->skillstext			= $data['skillstext'];
		$userprofile->languages				= $data['languages'];
		$userprofile->firstLifeImagePublish	= $show1stimage;
		$userprofile->firstLifeText			= $data['text1st'];

		if($founduuid > 0) {
			$db->updateObject('#__opensim_userprofile',$userprofile,'avatar_id',$updateNulls);
		} else {
			$db->insertObject('#__opensim_userprofile',$userprofile);
		}
	}

	public function getClientInfo($uuid) {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query->select('#__opensim_clientinfo.*');
		$query->from('#__opensim_clientinfo');
		$query->where('#__opensim_clientinfo.PrincipalID = '.$db->quote($uuid));
		$db->setQuery($query);
		$clientinfo = $db->loadAssoc();
		return $clientinfo;
	}

	public function moveusers($fromlevel,$tolevel) {
		$db			= $this->gridDB;
		$query		= $db->getQuery(true);
		$fields		= array(
					$db->quoteName('UserLevel').' = '.$db->quote($tolevel),
		);
		$conditions = array(
			$db->quoteName('UserLevel').' = '.$db->quote($fromlevel)
		);
		$query->update($db->quoteName('UserAccounts'))->set($fields)->where($conditions);


		$db->setQuery($query);
		$db->execute();
		return $db->getAffectedRows();
	}

	public function getUserState($state) {
		return $this->getState($state);
	}

	public function getEventList($limit = 0, $searchterm = null) {
		$timestamp			= time();
		$user				= Factory::getUser();
//		if(JDEBUG) Log::add('jOpenSimHelper getEventList user: '.var_export($user,TRUE), Log::DEBUG, 'jopensim-debug');
		$inworlduser		= $this->opensimRelation($user->id);
//		if(JDEBUG) Log::add('jOpenSimHelper getEventList inworlduser: '.var_export($inworlduser,TRUE), Log::DEBUG, 'jopensim-debug');
		$db					= Factory::getDBO();
		$query				= $db->getQuery(true);
		$dateTimeZoneUTC	= new \DateTimeZone('UTC');
		$dateTimeUTC		= new \DateTime(date("Y-m-d H:i:s"), $dateTimeZoneUTC);

//		if(JDEBUG) Log::add('jOpenSimHelper getEventList eventtimedefault: '.$this->settingsData['eventtimedefault'], Log::DEBUG, 'jopensim-debug');
		$jsettings			= $this->getJuserData($inworlduser);
//		if(JDEBUG) Log::add('jOpenSimHelper getEventList jsettings: '.var_export($jsettings,TRUE), Log::DEBUG, 'jopensim-debug');
		if(!array_key_exists("timezone",$jsettings) || !$jsettings['timezone']) $usertimezone = $this->settingsData['eventtimedefault'];
		else $usertimezone	= $jsettings['timezone'];
//		if(JDEBUG) Log::add('jOpenSimHelper getEventList usertimezone: '.$usertimezone, Log::DEBUG, 'jopensim-debug');

		$dateTimeZoneUser	= new \DateTimeZone($usertimezone);
		$userTimeOffset		= $dateTimeZoneUser->getOffset($dateTimeUTC);

		$dateTimeZoneServer	= new \DateTimeZone(date_default_timezone_get());
		$dateTimeServer		= new \DateTime(date("Y-m-d H:i:s"), $dateTimeZoneServer);

		$serveroffset		= $dateTimeServer->getOffset();

		$serverconverted	= $timestamp - $serveroffset;
		$userconverted		= $timestamp + $userTimeOffset;

		$timestampUTC		= $dateTimeServer->format("U") - $serveroffset;
		if($inworlduser) {
			$query->select("IF(".$db->quoteName('#__opensim_search_events.creatoruuid')." = ".$db->quote($inworlduser).",'true','false') AS isadmin");
		} else {
			$query->select($db->quote("false")." AS isadmin");
		}
		$query->select($db->quoteName('#__opensim_search_events').".*");
		$query->select($timestampUTC." AS currentUTC");
		$query->select($db->quote($usertimezone)." AS usedTimeZone");
		$query->from($db->quoteName('#__opensim_search_events'));
		$query->where($db->quoteName('#__opensim_search_events.dateUTC')." >= ".$db->quote($timestampUTC));

		if($searchterm) {
			$search		= $db->quote('%' . str_replace(' ','%',$db->escape(trim($searchterm),true).'%'));
			$query->where("(".$db->quoteName('#__opensim_search_events.name')." LIKE ".$search." OR ".$db->quoteName('#__opensim_search_events.description')." LIKE ".$search.")");
		}

		$query->order($db->quoteName('#__opensim_search_events.dateUTC'),'asc');
		if($limit > 0) {
			$query->setLimit($limit);
		}
		$debug	= $query->dump();
//		if(JDEBUG) Log::add('jOpenSimHelper eventlist query: '.strip_tags($debug), Log::DEBUG, 'jopensim-debug');
		$db->setQuery($query);
		$events			= $db->loadObjectList();
		if(is_array($events) && count($events) > 0) {
			$eventcategories	= self::getEventCategories();
			foreach($events AS $id => $event) {
				$events[$id]->ownername			= $this->opensim->getUserName($event->owneruuid,"full");
				$events[$id]->categoryname		= $eventcategories[$event->category];
				$events[$id]->eventTimeStamp	= $event->dateUTC + $userTimeOffset;
				$events[$id]->userdate			= date(Text::_('JOPENSIM_EVENT_DATE_FORMAT'),$event->dateUTC + $userTimeOffset);
				$events[$id]->usertime			= date(Text::_('JOPENSIM_EVENT_TIME_FORMAT'),$event->dateUTC + $userTimeOffset);
				$events[$id]->surl				= $event->simname."/".$event->landingpoint;
			}
		}
		return $events;
	}

	public function getVersion() {
//		if(JDEBUG) Log::add('jOpenSimHelper getVersion path: '.JPATH_ADMINISTRATOR, Log::DEBUG, 'jopensim-debug');
		$manifest				= simplexml_load_file(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_opensim'.DIRECTORY_SEPARATOR.'opensim.xml');
		$this->jopensimversion	= (string) $manifest->version;
		return $this->jopensimversion;
	}

	public function getRecentVersion() {
		$jopensimxml		= @simplexml_load_file("https://update.jopensim.com/components/com_opensim.xml");
		if(!is_object($jopensimxml)) return Text::_('UPDATEINFONOTAVAILABLE');
		$jversion			= new Version();
		$joomlaversion		= $jversion->getShortVersion();
		$targetversion		= substr($joomlaversion,0,2);
		$jopensimversion	= "";
		$jopensimchangelog	= "";
		foreach($jopensimxml->update AS $update) {
			if($update->targetplatform['version'] == $targetversion) {
				$jopensimversion	= $update->version;
				$jopensimchangelog	= $update->changelog;
			}
		}
		if(!$jopensimversion) {
			return Text::_('UPDATEINFONOTAVAILABLE');
		} else {
			$versioncheck	= version_compare($this->getVersion(),trim($jopensimversion));
			if($versioncheck < 0) {
				return Text::sprintf('UPDATEVERSION',$jopensimversion)."<br />".Text::sprintf('UPDATECHANGELOG',$jopensimchangelog);
			} elseif($versioncheck > 0) {
				return "<i class='icon-warning-circle' style='color:orange;'></i>PreRelease?";
			} else {
				return "<i class='icon-ok jopensimversion-up2date'></i>".Text::_('UP2DATE');
			}
		}
//		return $jopensimlist;
	}

	public function checkPluginStatus($element,$folder) {
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('#__extensions.*');
		$query->select($db->quoteName('#__viewlevels.title')." AS leveltitle");
		$query->from($db->quoteName('#__extensions'));
		$query->join('LEFT', '#__viewlevels ON #__viewlevels.id = #__extensions.access');
		$query->where($db->quoteName('#__extensions.element').' = '.$db->quote($element));
		$query->where($db->quoteName('#__extensions.folder').' = '.$db->quote($folder));
		$db->setQuery($query);
		$db->execute();
		$foundplugin = $db->getNumRows();
		if($foundplugin == 1) {
			return $db->loadAssoc();
		} else {
			return FALSE;
		}
	}

	public function moneyEnabled() {
		if($this->settingsData['addons_currency']) return TRUE;
		else return FALSE;
	}

	public static function getTransactionTypes($full = FALSE) {
		$transactiontypes	= jOpenSimMoneyHelper::MoneyTransactionType();
		if($full === TRUE) return $transactiontypes;
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('DISTINCT('.$db->quoteName('#__opensim_moneytransactions.type').') AS type');
		$query->from($db->quoteName('#__opensim_moneytransactions'));
		$query->order($db->quoteName('#__opensim_moneytransactions.type')." ASC");
		$db->setQuery($query);
		$types	= $db->loadObjectList();
		$retval	= array();
		if(count($types) > 0) {
			foreach($types AS $key => $type) {
				$retval[$type->type]	= (array_key_exists($type->type,$transactiontypes)) ? $transactiontypes[$type->type]:Text::_('JOPENSIMUNKNOWN')." ".$type->type;
			}
		}
		return $retval;
	}

	public static function getUserLevels($minlevel = FALSE) {
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName('#__opensim_userlevels.userlevel')." AS userlevel");
		$query->select($db->quoteName('#__opensim_userlevels.description')." AS userlevelname");
		$query->from($db->quoteName('#__opensim_userlevels'));
		if($minlevel !== FALSE) {
			$query->where($db->quoteName('#__opensim_userlevels.userlevel')." >= ".$db->quote($minlevel));
		}
		$query->order($db->quoteName('#__opensim_userlevels.userlevel')." ASC");
		$db->setQuery($query);
		return $db->loadAssocList();
	}

	public static function getUserAvatars() {
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName('#__opensim_useravatars.userid')." AS userid");
		$query->select($db->quoteName('#__opensim_useravatars.avatarname')." AS avatarname");
		$query->select($db->quoteName('#__opensim_useravatars.avatarimage')." AS avatarimage");
		$query->from($db->quoteName('#__opensim_useravatars'));
		$query->order($db->quoteName('#__opensim_useravatars.ordering')." ASC");
		$db->setQuery($query);#joomlaImage

		$avatarlist	= $db->loadObjectList();
		foreach($avatarlist AS $key => $avatar) {
			$pos	= strpos($avatar->avatarimage,"#joomlaImage");
			if($pos > 0) {
				$avatarlist[$key]->avatarimage = substr($avatar->avatarimage,0,$pos);
			}
		}
		return $avatarlist;
	}

	public static function getEventCategories() {
		$categories	= array(
							27 => Text::_('JOPENSIM_EVENTCATEGORY_ART'),
							28 => Text::_('JOPENSIM_EVENTCATEGORY_CHARITY'),
							22 => Text::_('JOPENSIM_EVENTCATEGORY_COMMERCIAL'),
							18 => Text::_('JOPENSIM_EVENTCATEGORY_DISCUSSION'),
							26 => Text::_('JOPENSIM_EVENTCATEGORY_EDUCATION'),
							24 => Text::_('JOPENSIM_EVENTCATEGORY_GAMES'),
							20 => Text::_('JOPENSIM_EVENTCATEGORY_MUSIC'),
							29 => Text::_('JOPENSIM_EVENTCATEGORY_MISC'),
							23 => Text::_('JOPENSIM_EVENTCATEGORY_NIGHTLIFE'),
							25 => Text::_('JOPENSIM_EVENTCATEGORY_PAGEANT'),
							19 => Text::_('JOPENSIM_EVENTCATEGORY_SPORT'),
		);
		return $categories;
	}

	public function addLastName($name) {
		if(JDEBUG) Log::add('jOpenSimHelper addLastName1: '.var_export($this->settingsData['lastnames'],TRUE), Log::DEBUG, 'jopensim-debug');
		if(JDEBUG) Log::add('jOpenSimHelper addLastName: '.$name, Log::DEBUG, 'jopensim-debug');
		$this->settingsData['lastnames'][] = trim($name);
		if(JDEBUG) Log::add('jOpenSimHelper addLastName2: '.var_export($this->settingsData['lastnames'],TRUE), Log::DEBUG, 'jopensim-debug');
	}

	public function getLastnames() {
		if(JDEBUG) Log::add('jOpenSimHelper getLastnames: '.var_export($this->settingsData['lastnames'],TRUE), Log::DEBUG, 'jopensim-debug');
		return $this->settingsData['lastnames'];
	}

	public function checkBrokenPresence() {
		$query	= $this->gridDB->getQuery(true);
		$query
			->select($this->gridDB->quoteName('Presence').".*")
			->from($this->gridDB->quoteName('Presence'))
			->where($this->gridDB->quoteName('Presence.RegionID')." = ".$this->gridDB->quote($this->zerouid));
		$this->gridDB->setQuery($query);
		$this->gridDB->execute();
		$foundbrokenpresence = $this->gridDB->getNumRows();
		if($foundbrokenpresence > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function selectableavatars($uuid,$type) {
		$db			= Factory::getDBO();
		switch($type) {
			case "insert":

				$avatarname	= $this->opensim->getUserName($uuid,"full");

				$query = $db->getQuery(true);
				$columns = array('userid', 'avatarname','ordering');
				$values = array($db->quote($uuid), $db->quote($avatarname), $db->quote(999));
				$query
					->insert($db->quoteName('#__opensim_useravatars'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				$query .= ' ON DUPLICATE KEY UPDATE ' . $db->quoteName('avatarname') . ' = ' . $db->quote($avatarname);
				$db->setQuery($query);
				$db->execute();
			break;
			case "delete":
				$query		= $db->getQuery(true);
				$condition	= array(
								$db->quoteName('userid')." = ".$db->quote($uuid)
							);
				$query->delete($db->quoteName('#__opensim_useravatars'));
				$query->where($condition);
				$db->setQuery($query);
				$db->execute();
			break;
		}
	}

	public function repopulateavatars() {
		$userlist = array();
		if(!$this->gridDB) return FALSE;
		$filter['usertable_field_UserLevel'] = -3;
		$opensim	= $this->opensim;
		$query		= $opensim->getUserListQuery($filter);

		$this->gridDB->setQuery($query);

		$userlist	= $this->gridDB->loadAssocList();

		$db			= Factory::getDBO();
		$query		= "TRUNCATE TABLE #__opensim_useravatars;";
		$db->setQuery($query);
		$db->execute();

		if(is_array($userlist) && count($userlist) > 0) {
			foreach($userlist AS $useravatar) {
				if($useravatar['UserTitle']) $avatarname = $useravatar['UserTitle'];
				else $avatarname = $useravatar['FirstName']." ".$useravatar['LastName'];
				$query = sprintf("INSERT INTO #__opensim_useravatars (userid,avatarname) VALUES ('%s','%s')",
					$useravatar['PrincipalID'],
					$avatarname);
				$db->setQuery($query);
				$db->execute();
			}
		}
		return $userlist;
	}

	public function onlineVisitors($days = 1) {
		$tage = sprintf("%d", $days);
		$jetzt = time();
		$lastloggedin = $jetzt - 60*60*24*$tage;
		$this->gridDB->setQuery("SELECT COUNT(*) FROM GridUser WHERE Login > ".$this->gridDB->quote($lastloggedin)." OR Logout > ".$this->gridDB->quote($lastloggedin));
		$onlineVisitors = $this->gridDB->loadResult();
		return $onlineVisitors;
	}

	public function totalResidents() {
		return $this->opensim->countActiveUsers();
	}

	public function currentVisitors($hg = FALSE) {
		if($hg === FALSE) $query	= "SELECT COUNT(*) FROM Presence WHERE RegionID != ".$this->gridDB->quote($this->zerouid);
		else $query = "SELECT COUNT(*) FROM Presence LEFT JOIN UserAccounts ON CONVERT(UserAccounts.PrincipalID USING utf8) COLLATE utf8_general_ci = CONVERT(Presence.UserID USING utf8) COLLATE utf8_general_ci WHERE CONVERT(Presence.RegionID USING utf8) COLLATE utf8_general_ci != ".$this->gridDB->quote($this->zerouid)." AND UserAccounts.PrincipalID IS NULL";
		$this->gridDB->setQuery($query);
		$onlineVisitors	= $this->gridDB->loadResult();
		return $onlineVisitors;
	}

	public function getHGlinks() {
		if($this->settingsData['hg_override'] == 1) {
			$hghost	= ($this->settingsData['robust_hghost']) ? $this->settingsData['robust_hghost']:$this->settingsData['oshost'];
			$hgport	= ($this->settingsData['robust_hgport']) ? $this->settingsData['robust_hgport']:$this->settingsData['robust_port'];
		} else {
			$hghost	= $this->settingsData['oshost'];
			$hgport	= $this->settingsData['robust_port'];
		}
		$hglink['hg']	= "secondlife:/"."/".$hghost.":".$hgport.":";
		$hglink['hgv3']	= "secondlife:/"."/http|!!".$hghost."|".$hgport."+";
		$hglink['hop']	= "hop:/"."/".$hghost.":".$hgport.":";
		return $hglink;
	}

	public function getRegionAtLocation($posX,$posY,$absolute = FALSE) {
		if($absolute === FALSE) {
			$locX	= $posX*256;
			$locY	= $posY*256;
		} else {
			$locX	= $posX;
			$locY	= $posY;
		}
		$db		= $this->gridDB;
		$query	= $db->getQuery(true);
		$query->select("*");
		$query->from($db->quoteName('regions'));
		$query->where($db->quoteName('regions.locX').' = '.$db->quote($locX));
		$query->where($db->quoteName('regions.locY').' = '.$db->quote($locY));
		$db->setQuery($query);
		$debug	= $query->dump();
		if(JDEBUG) Log::add('jOpenSimHelper query: '.strip_tags($debug), Log::DEBUG, 'jopensim-debug');
		$db->execute();
		$found	= $db->getNumRows();
		if($found == 0) {
			$regionatlocation	= FALSE;
		} else {
			$regionatlocation	= $db->loadObject();
		}
		$debug2	= var_export($regionatlocation,TRUE);
		if(JDEBUG) Log::add('jOpenSimHelper regionatlocation: '.strip_tags($debug2), Log::DEBUG, 'jopensim-debug');
		return $regionatlocation;
	}

	public function getRegions($serverURI = null, $hg = FALSE, $hide = FALSE) {
		$regions	= $this->opensim->getRegions($serverURI,$hg);
		foreach($regions AS $key => $region) {
			$mapinfo	= $this->getMapInfo($region['uuid']);
			if($hide === TRUE && $mapinfo['hidemap'] == 1) {
				unset($regions[$key]);
			} else {
				if(array_key_exists("landingpoint",$mapinfo) && $mapinfo['landingpoint']) {
					$landing	= $mapinfo['landingpoint'];
				} else {
					$landing	= "128/128/21"; // kinda fallback ;)
				}
				$regions[$key]['landing']	= $landing;
			}
		}
		return $regions;
	}

	public function countRegions($serverURI = null, $hg = FALSE, $hide = FALSE) {
//		if(JDEBUG) Log::add('jOpenSimHelper countRegions hide:'.$hide, Log::DEBUG, 'jopensim-debug');
		$regions	= $this->getRegions($serverURI,$hg,$hide);
		return count($regions);
	}

	public function getRegionDetails($uuid) {
		$regiondata = $this->opensim->getRegionData($uuid);
		if(is_array($regiondata)) {
			$regiondata['posX']		= intval($regiondata['posX']);
			$regiondata['posY']		= intval($regiondata['posY']);
			$regiondata['maplink']	= str_replace("-","",$uuid);
			$ownerdata = $this->opensim->getUserData($regiondata['owner_uuid']);
			if(array_key_exists("firstname",$ownerdata) && array_key_exists("lastname",$ownerdata)) {
				$regiondata['ownername'] = $ownerdata['firstname']." ".$ownerdata['lastname'];
			} else {
				$regiondata['ownername'] = "n/a";
			}
			$mapinfo = $this->getMapInfo($uuid);
			$regiondata['articleId']	= $mapinfo['articleId'];
			$regiondata['articleTitle']	= $this->getContentTitleFromId($mapinfo['articleId']);
			$regiondata['hidemap']		= $mapinfo['hidemap'];
			$regiondata['landingpoint']	= $mapinfo['landingpoint'];
			$regiondata['defaultpos']	= "<".str_replace("/",",",$mapinfo['landingpoint']).">";
			return $regiondata;
		} else {
			return FALSE;
		}
	}

	public function getRegionImage($regionuuid,$regionname = "", $class = "",$size = 256) {
		$this->mapCacheRefresh($regionuuid);
		if($class) {
			$regionimage	= "<img src='%1\$s' width='%4\$d' height='%4\$d' alt='%2\$s' title='%2\$s' class='%3\$s' />";
		} else {
			$regionimage	= "<img src='%1\$s' width='%4\$d' height='%4\$d' alt='%2\$s' title='%2\$s' />";
		}
		$cacheurl		= URI::root()."images/jopensim/regions/";
		$mapimage		= sprintf($regionimage,$cacheurl.$regionuuid.".jpg",$regionname,$class,$size);
		return $mapimage;
	}

	public function getMapInfo($regionUUID) {
		if(is_array($regionUUID)) {
			$region = $regionUUID[0];
		} else {
			$region = $regionUUID;
		}
		$retval	= array();
		$query	= sprintf("SELECT #__opensim_mapinfo.* FROM #__opensim_mapinfo WHERE regionUUID = '%s'",$region);
		$db		= Factory::getDBO();
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows() == 1) {
			$retval = $db->loadAssoc();
			if($retval['articleId'] && $retval['articleId'] > 0) $retval['articleTitle'] = $this->getContentTitleFromId($retval['articleId']);
			else $retval['articleTitle'] = "";
		} else {
			$retval['regionUUID']	= $region;
			$retval['articleId']	= null;
			$retval['articleTitle'] = "";
			$retval['hidemap']		= 0;
			$retval['public']		= 0;
			$retval['guide']		= 0;
			$retval['landingpoint']	= null;
		}
		return $retval;
	}

	public function getContentTitleFromId($id) {
		$db = Factory::getDBO();
		$query = sprintf("SELECT title FROM #__content WHERE id = '%d'",$id);
		$db->setQuery($query);
		$contentTitle = $db->loadResult();
		if($contentTitle) return $contentTitle;
		else return Text::_('NONE');
	}

	public function checkCacheFolder($dest = "regions") {
		if($dest == "regions") $cachefolder = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'regions';
		else $cachefolder = JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'regions'.DIRECTORY_SEPARATOR.'varregions';
		$retval['path'] = $cachefolder;
		if(is_dir($cachefolder)) {
			$retval['existing'] = TRUE;
			if(is_writable($cachefolder)) {
				$retval['writeable'] = TRUE;
			} else {
				$retval['writeable'] = FALSE;
			}
		} else {
			$retval['existing'] = FALSE;
		}
		return $retval;
	}

	public function mapCacheRefresh($regionUID) {
		$refresh = $this->mapNeedsRefresh($regionUID);
		if($refresh === TRUE) $this->refreshMap($regionUID);
	}

	public function mapNeedsRefresh($regionUID) {
		$chachefolder = $this->checkCacheFolder();
		if($chachefolder['existing'] == FALSE || $chachefolder['writeable'] == FALSE) return FALSE;
		$regiondata = $this->getRegionDetails($regionUID);
		$regionimage = $chachefolder['path'].DIRECTORY_SEPARATOR.$regiondata['uuid'].".jpg";
		if(!is_file($regionimage)) {
			return TRUE;
		} else {
			if($this->settingsData['jopensim_maps_cacheage'] == 0) return FALSE;
			$cachetime = time() - (60*$this->settingsData['jopensim_maps_cacheage']);
			if($cachetime > filemtime($regionimage)) return TRUE;
			else return FALSE;
		}
	}

	public function refreshMap($regionUID) {
		$chachefolder = $this->checkCacheFolder();
		if($chachefolder['existing'] == FALSE || $chachefolder['writeable'] == FALSE) return FALSE;
		$regiondata = $this->getRegionDetails($regionUID);

		$regionimage = $chachefolder['path'].DIRECTORY_SEPARATOR.$regiondata['uuid'].".jpg";
		$os_regionimage = str_replace("-","",$regiondata['uuid']);
		$source = $regiondata['serverURI']."index.php?method=regionImage".$os_regionimage;

		$mapdata = $this->getMapContent($source);
		if(array_key_exists("error",$mapdata)) { // some error occurred, lets copy an error image for it
			$this->maperrorimage($regionimage,$mapdata['error']);
			return FALSE;
		} elseif(array_key_exists("file_content",$mapdata) && $mapdata['file_content']) {
			$fh = fopen($regionimage,"w");
			fwrite($fh,$mapdata['file_content']);
			fclose($fh);
		}
		if($this->settingsData['jopensim_maps_varregions'] == 1 && ($regiondata['sizeX'] > 256 || $regiondata['sizeY'] > 256)) { // we got a varregion here, lets get V2 maptiles
			$cachefolder = $this->checkCacheFolder("varregions");
			$varregionsfolder = $cachefolder['path'];
			if($chachefolder['existing'] == FALSE || $chachefolder['writeable'] == FALSE) return FALSE;
			$mapstartX	= $regiondata['locX'] / 256;
			$mapstartY	= $regiondata['locY'] / 256;
			$mapendX	= $mapstartX + ($regiondata['sizeX'] / 256);
			$mapendY	= $mapstartY + ($regiondata['sizeY'] / 256);
			for($x = $mapstartX; $x < $mapendX; $x++) {
				for($y = $mapstartY; $y < $mapendY; $y++) {
					$mapname = "map-1-".$x."-".$y."-objects.jpg";
					$regionimage = $varregionsfolder.DIRECTORY_SEPARATOR.$mapname;
					$source = $this->settingsData['opensim_host'].":".$this->settingsData['robust_port']."/".$mapname;
//					error_log("mapsource for varregions: ".$source);
					$mapdata = $this->getMapContent($source);
					if(array_key_exists("error",$mapdata)) { // some error occurred, lets copy an error image for it
						$this->maperrorimage($regionimage,$mapdata['error']);
						return FALSE;
					} elseif(array_key_exists("file_content",$mapdata) && $mapdata['file_content']) {
						$fh = fopen($regionimage,"w");
						fwrite($fh,$mapdata['file_content']);
						fclose($fh);
					}
				}
			}
		}
		return TRUE;
	}

	public function getMapContent($source) { // gets image data from external server
//		$start	= hrtime(true);
		// lets check, what possibilities to read outside files is present
		$curl = extension_loaded('curl');
		$fopen = ini_get('allow_url_fopen');
		$retval['file_content'] = "";
//		if(JDEBUG) Log::add('jOpenSimHelper getMapContent source: '.$source, Log::DEBUG, 'jopensim-debug');

		if(!$curl && !$fopen) { // there is no way to read from outside :( at least display an error image
			$retval['error'] = "impossible reading";
		} elseif($curl) {
			ob_start();
			$ch = curl_init($source);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_exec($ch);
			$response = curl_getinfo($ch);
			if($response['http_code'] == 200) {
				$retval['file_content'] = ob_get_contents();
				ob_end_clean();
			} else { // could not open the image with cURL - display error image
				ob_end_clean();
				$retval['error'] = "cURL error ".$response['http_code'];
			}
		} else {
			$fexists = $this->http_test_existance($source);
			if($fexists['status'] == 200) {
				$handle = @fopen($source,'r');
				if($handle) {
					while (!feof($handle)) {
						$retval['file_content'] .= fread($handle,1024);
					}
					fclose($handle);
				} else { // could not open the image with fopen - display error image
					$retval['error'] = "fopen error (unknown)";
				}
			} else {
				$retval['error'] = $source."\nfopen error (status: ".$fexists['status'].")";
			}
		}
//		$end	= hrtime(true);
//		$eta	= $end - $start; // nanoseconds
//		$msec	= $eta / 1e+6;

//		if(JDEBUG) Log::add('jOpenSimHelper getMapContent time: '.$msec."ms", Log::DEBUG, 'jopensim-debug');

		return $retval;
	}

	public function maperrorimage($filename,$errormessage = "") {
		if(!$errormessage) $errormessage = "unknown error";
		$noregionimage = JPATH_SITE.DIRECTORY_SEPARATOR."media".DIRECTORY_SEPARATOR."com_opensim".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."noregion.png";
		$img = imagecreatefrompng($noregionimage);
		$textcolor = ImageColorAllocate ($img, 255, 255, 0);
		ImageString($img,1,20,200, $errormessage, $textcolor);
		imagejpeg($img,$filename);
		imagedestroy($img);
	}

	// Many thanks to Alexander Brock through http://aktuell.de.selfhtml.org/artikel/php/existenz/ for this very useful function :)
	// Edit 2021: now to find at https://wiki.selfhtml.org/wiki/PHP/Tutorials/Link-Checker
	public function http_test_existance($url,$timeout = 10) {
		$timeout = (int)round($timeout/2+0.00000000001);
		$return = array();

		$inf = parse_url($url);

		if(!isset($inf['scheme']) or $inf['scheme'] !== 'http' or $inf['scheme'] !== 'https') return array('status' => -1);
		if(!isset($inf['host'])) return array('status' => -2);
		$host = $inf['host'];

		if(!isset($inf['path'])) return array('status' => -3);
		$path = $inf['path'];
		if(isset($inf['query'])) $path .= '?'.$inf['query'];

		if(isset($inf['port'])) $port = $inf['port'];
		else $port = 80;

		$pointer = fsockopen($host, $port, $errno, $errstr, $timeout);
		if(!$pointer) return array('status' => -4, 'errstr' => $errstr, 'errno' => $errno);
		socket_set_timeout($pointer, $timeout);

		$head =
		  'HEAD '.$path.' HTTP/1.1'."\r\n".
		  'Host: '.$host."\r\n";

		if(isset($inf['user']))
			$head .= 'Authorization: Basic '.
			base64_encode($inf['user'].':'.(isset($inf['pass']) ? $inf['pass'] : ''))."\r\n";
		if(func_num_args() > 2) {
			for($i = 2; $i < func_num_args(); $i++) {
				$arg = func_get_arg($i);
				if(
					strpos($arg, ':') !== false and
					strpos($arg, "\r") === false and
					strpos($arg, "\n") === false
				) {
					$head .= $arg."\r\n";
				}
			}
		}
		else $head .=
			'User-Agent: Selflinkchecker 1.0 ('.$_SERVER['PHP_SELF'].')'."\r\n";

		$head .=
			'Connection: close'."\r\n"."\r\n";

		fputs($pointer, $head);

		$response = '';

		$status = socket_get_status($pointer);
		while(!$status['timed_out'] && !$status['eof']) {
			$response .= fgets($pointer);
			$status = socket_get_status($pointer);
		}
		fclose($pointer);
		if($status['timed_out']) {
			return array('status' => -5, '_request' => $head);
		}

		$res = str_replace("\r\n", "\n", $response);
		$res = str_replace("\r", "\n", $res);
		$res = str_replace("\t", ' ', $res);

		$ares = explode("\n", $res);
		$first_line = explode(' ', array_shift($ares), 3);

		$return['status'] = trim($first_line[1]);
		$return['reason'] = trim($first_line[2]);

		foreach($ares as $line) {
			$temp = explode(':', $line, 2);
			if(isset($temp[0]) and isset($temp[1])) {
				$return[strtolower(trim($temp[0]))] = trim($temp[1]);
			}
		}
		$return['_response'] = $response;
		$return['_request'] = $head;

		return $return;
	}

	public function getUserProfile($userid) {
		$db			= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userprofile').".*")
			->from($db->quoteName('#__opensim_userprofile'))
			->where($db->quoteName('#__opensim_userprofile.avatar_id').' = '.$db->quote($userid));
		$querydebug	= $query->dump();
//		if(JDEBUG) Log::add('jOpenSimHelper getUserProfile query: '.strip_tags($querydebug), Log::DEBUG, 'jopensim-debug');
		$db->setQuery($query);
		$profile = $db->loadAssoc();
		if(!is_array($profile) || count($profile) == 0) { // in case no profile stored yet, fill it with empty values to avoid php notices
			$profile['error']					= Text::_('JOPENSIM_PROFILE_ERROR_NOTFOUND');
			$profile['aboutText']				= "";
			$profile['maturePublish']			= 0;
			$profile['partner']					= null;
			$profile['url']						= "";
			$profile['image']					= "";
			$profile['wantmask']				= 0;
			$profile['wanttext']				= "";
			$profile['skillsmask']				= 0;
			$profile['skillstext']				= "";
			$profile['languages']				= "";
			$profile['firstLifeText']			= "";
			$profile['firstLifeImage']			= "";
			$profile['firstLifeImagetag']		= null;
			$profile['firstLifeImagePublish']	= null;
			$profile['firstname']				= "";
			$profile['lastname']				= "";
			$profile['name']					= "";
			$profile['allowPublish']			= 0;
		} else {
			$namequery = $this->opensim->getUserNameQuery($userid);
			$this->gridDB->setQuery($namequery);
			$name = $this->gridDB->loadAssoc();
			$profile['firstname']		= $name['firstname'];
			$profile['lastname']		= $name['lastname'];
			$profile['name']			= $name['firstname']." ".$name['lastname'];
		}
		if($profile['partner']) {
			$partnernamequery = $this->opensim->getUserNameQuery($profile['partner']);
			$this->gridDB->setQuery($partnernamequery);
			$partner = $this->gridDB->loadAssoc();
			$profile['partnername'] = $partner['firstname']." ".$partner['lastname'];
		} else {
			$profile['partner']		= null;
			$profile['partnername'] = null;
		}

		// generate img tags
		if($profile['image'] && $profile['image'] != $this->zerouid) {
//			if(JDEBUG) Log::add('jOpenSimHelper getTextureImage img: '.$profile['image'], Log::DEBUG, 'jopensim-debug');
			$profile['imagetag'] = $this->getTextureImage($userid,null,"profile");
		} else {
//			if(JDEBUG) Log::add('jOpenSimHelper not present img???', Log::DEBUG, 'jopensim-debug');
			$profile['imagetag'] = null;
		}

		if($profile['firstLifeImage'] && $profile['firstLifeImage'] != $this->zerouid) {
//			if(JDEBUG) Log::add('jOpenSimHelper getTextureImage firstLifeImage: '.$profile['firstLifeImage'], Log::DEBUG, 'jopensim-debug');
			$profile['firstLifeImagetag'] = $this->getTextureImage($userid,null,"firstLifeImage");
		} else {
			$profile['firstLifeImagetag'] = null;
		}

		// Todo:
		// still empty profile if found but:
		// `aboutText` = 'No profile stored' AND 
		// `image` = '00000000-0000-0000-0000-000000000000' AND 
		// `wantmask` = '0' AND 
		// `skillsmask` = '0' AND 
		// `firstLifeImage` = '00000000-0000-0000-0000-000000000000'
		
		return $profile;
	}

	public function getUserFriends($userid) {
		$opensim	= $this->opensim;
		$query		= $opensim->getUserDataQuery($userid);
		$this->gridDB->setQuery($query['friends']);
		$friends	= $this->gridDB->loadObjectList();
		return $friends;
	}

	public function getTextureImage($textureID, $class = null, $type = null) {

		$textureFormat		= "png";
		$fileName;
		switch($type){
			case "profile":
				$fileName = $textureID.".".$textureFormat;
				break;
			case "firstLifeImage":
				$fileName = $textureID."-fli.".$textureFormat;
				break;
			case "group":
				$fileName = $textureID.".".$textureFormat;
				break;
			case "search":
				$fileName = $textureID.".".$textureFormat;
				break;
			case "classified":
				$fileName = $textureID.".".$textureFormat;
				break;
			case "pick":
				$fileName = $textureID.".".$textureFormat;
				break;
			default:
			$fileName = $textureID.".".$textureFormat;
		}

		$filepath	= 'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$fileName;
		if(file_exists($filepath)) {
				
			$attr['title']	= $textureID;
			if($class) {
				$attr['class'] = $class;
			}
			$img = HTMLHelper::image($filepath,$textureID,$attr);
			return $img;
		}
		return null;
	}


	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   0.4.0.0
	 */
	public static function addSubmenu($vName) {
		$canDo	= ContentHelper::getActions('com_opensim');
		$params	= ComponentHelper::getParams('com_opensim');
		if(!$params->get('enableremoteadmin',0) && !$params->get('addons_inworldauth',0)) return;
		if($canDo->get('core.simulators') || $canDo->get('core.remoteadmin') || $canDo->get('core.terminals')) {
			\JHtmlSidebar::addEntry(
				Text::_('JOPENSIM_MISC'),
				'index.php?option=com_opensim&view=misc',
				$vName == 'misc'
			);
			if($params->get('enableremoteadmin',0)) {
				if($canDo->get('core.simulators')) {
					\JHtmlSidebar::addEntry(
						Text::_('JOPENSIM_SIMULATORS'),
						'index.php?option=com_opensim&view=simulators',
						$vName == 'simulators'
					);
				}
				if($canDo->get('core.remoteadmin')) {
					if($params->get('remoteadmin_addregion',0)) {
						\JHtmlSidebar::addEntry(
							Text::_('JOPENSIM_ADDREGION'),
							'index.php?option=com_opensim&view=radmin&layout=addregion',
							$vName == 'radmin.addregion'
						);
					}
					if($params->get('remoteadmin_message',1)) {
						\JHtmlSidebar::addEntry(
							Text::_('JOPENSIM_SENDMESSAGE'),
							'index.php?option=com_opensim&view=radmin&layout=sendmessage',
							$vName == 'radmin.sendmessage'
						);
					}
					if($params->get('remoteadmin_version',1)) {
						\JHtmlSidebar::addEntry(
							Text::_('GETOPENSIMVERSION'),
							'index.php?option=com_opensim&view=radmin&layout=showversion',
							$vName == 'radmin.showversion'
						);
					}
				}
			}
			if($canDo->get('core.terminals') && $params->get('addons_inworldauth',0)) {
				\JHtmlSidebar::addEntry(
					Text::_('JOPENSIM_TERMINALS'),
					'index.php?option=com_opensim&view=terminals',
					$vName == 'terminals'
				);
			}
		}
	}

	/**
	 * Method to get connected Joomla user for OpenSim user.
	 *
	 * @param   string $uuid
	 *
	 * @return  int Joomla user.
	 *
	 * @since	0.4.0.0
	 */
	public function getJoomlaUser($uuid) {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userrelation.joomlaID'))
			->from($db->quoteName('#__opensim_userrelation'))
			->where($db->quoteName('#__opensim_userrelation.opensimID').' = '.$db->quote($uuid));
		$db->setQuery($query);
		$joomlauser	= $db->loadResult();
		return $joomlauser;
	}

	public function countUsersInLevel($level) {
		$db		= $this->gridDB;
		$query	= $db->getQuery(true);

		$query->select("COUNT(*)");
		$query->from($db->quoteName('UserAccounts'));
		$query->where($db->quoteName('UserAccounts.UserLevel')." = ".$db->quote($level));
		$db->setQuery($query);
		$usercount	= $db->loadResult();
		return $usercount;
		
//		return -1;
	}

	public function getSimulators() {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);

		$query->select("*");
		$query->from($db->quoteName('#__opensim_simulators'));
		$db->setQuery($query);
		$simulators	= $db->loadObjectList();
		if(is_array($simulators) && count($simulators) > 0) {
			$connectedSimulators = $this->opensim->opensimGetConnectedSimulators();
			foreach($simulators AS $key => $simulator) {
				$simulators[$key]->regions			= null;
				$simulators[$key]->currentregions	= 0;
				$simulators[$key]->connected		= FALSE;
				if(is_array($connectedSimulators) && count($connectedSimulators) > 0) {
					foreach($connectedSimulators AS $connectedsimulator) {
						if($connectedsimulator['serverURI'] == $simulator->simulator) {
							$simulators[$key]->regions			= $connectedsimulator['regions'];
							$simulators[$key]->currentregions	= count(explode(",",$connectedsimulator['regions']));
							$simulators[$key]->connected		= TRUE;
							break;
						}
					}
				}
			}
		}
		return $simulators;
	}

	public function getEstates() {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);

		$query->select("DISTINCT(".$db->quoteName('#__opensim_search_regions.estatename').")");
		$query->from($db->quoteName('#__opensim_search_regions'));
		$query->where($db->quoteName('#__opensim_search_regions.estatename').' != ""');
		$query->where($db->quoteName('#__opensim_search_regions.estatename').' IS NOT NULL');
		$db->setQuery($query);
		$estates	= $db->loadColumn();
		return $estates;
	}

	public function clientInfo($parameter, $source = "?") {
		if(!array_key_exists("agentName",$parameter) && !array_key_exists("agentIP",$parameter) && !array_key_exists("agentID",$parameter)) return; // no params, what should we save?
		$agent	= (array_key_exists("agentName",$parameter)) ? $parameter['agentName']:"unknown";
		$userip	= (array_key_exists("agentIP",$parameter)) ? $parameter['agentIP']:"127.0.0.3";
		$uuid	= (array_key_exists("agentID",$parameter)) ? $parameter['agentID']:$this->uuidZero;
		if(strstr($agent,"@") !== FALSE) {
			$lastpos	= strlen($agent) - strlen(strrchr($agent,"@"));
			$hoststring	= "https://".substr(strrchr($agent,"@"),1);
			$hostarray	= parse_url($hoststring);
			if(array_key_exists("host",$hostarray) && $hostarray['host']) {
				// den Hostnamen aus URL holen
				preg_match('@^(?:https://)?([^/]+)@i',$hostarray['host'], $treffer);
				$host = $treffer[1];
				// die letzten beiden Segmente aus Hostnamen holen
				preg_match('/[^.]+\.[^.]+$/', $host, $treffer);
				if(is_array($treffer) && count($treffer) > 0 && $treffer[0]) {
					$username	= substr($agent,0,$lastpos);
					$host		= $hostarray['host'];
				} else {
					$username	= $agent;
					$host		= "local";
				}
			} else {
				$username	= $agent;
				$host		= "local";
			}
		} else {
			$username	= $agent;
			$host		= "local";
		}
		$db = Factory::getDBO();
		$query = sprintf("INSERT INTO #__opensim_clientinfo (PrincipalID,userName,grid,remoteip,lastseen,`from`) VALUES (%1\$s,%2\$s,%3\$s,%4\$s,NOW(),%5\$s)
							ON DUPLICATE KEY UPDATE userName = %2\$s, grid = %3\$s, remoteip = %4\$s, lastseen = NOW(), `from`= %5\$s",
			$db->quote($uuid),
			$db->quote(trim($username)),
			$db->quote($host),
			$db->quote($userip),
			$db->quote($source));
		$db->setQuery($query);
		$db->execute();
	}

	public function getUUID() {
		$db = Factory::getDBO();
		$query = "SELECT UUID()";
		$db->setQuery($query);
		$uuid = $db->loadResult();
		return $uuid;
	}

	public function opensimRelation($uuid) {
		$db		= Factory::getDBO();
		$query	= "SELECT opensimID FROM #__opensim_userrelation WHERE joomlaID = ".$db->quote($uuid);
		$db->setQuery($query);
		$uuid = $db->loadResult();
		if(!$uuid) return FALSE;
		else return $uuid;
	}

	public function opensimRelationReverse($uuid) {
		$db		= Factory::getDBO();
		$query	= "SELECT joomlaID FROM #__opensim_userrelation WHERE opensimID = ".$db->quote($uuid);
		$db->setQuery($query);
		$uuid = $db->loadResult();
		if(!$uuid) return FALSE;
		else return $uuid;
	}

	public function updateOsPwd($newpassword,$osid) {
		$opensim				= $this->opensim;
		$update					= $opensim->getOsTableField('passwordHash');
		$osdata					= $this->getUserData($osid);
		$passwordSalt			= md5(time());
		$update['fieldvalue']	= md5(md5($newpassword).":".$passwordSalt);
		$update['osid']			= $osid;
		$this->updateOSValues($update);
		$update					= $opensim->getOsTableField('passwordSalt');
		$update['fieldvalue']	= $passwordSalt;
		$update['osid']			= $osid;
		$this->updateOSValues($update);
	}

	public function updateOsEmail($newemail,$osid) {
		$opensim				= $this->opensim;
		$update					= $opensim->getOsTableField('email');
		$update['fieldvalue']	= $newemail;
		$update['osid']			= $osid;
		$this->updateOSValues($update);
	}

	public function updateOsField($fieldname,$fieldvalue,$osid) {
		$opensim				= $this->opensim;
		$update					= $opensim->getOsTableField($fieldname);
		$update['fieldvalue']	= $fieldvalue;
		$update['osid']			= $osid;
		$this->updateOSValues($update);
	}

	public function updateOSValues($data) {
		$query = sprintf("UPDATE %s SET %s = '%s' WHERE %s = '%s'",
							$data['table'],
							$data['field'],
							$data['fieldvalue'],
							$data['userid'],
							$data['osid']);
		$this->gridDB->setQuery($query);
		$debug[] = $query;
		$result = $this->gridDB->execute();
		if($data['field'] == "passwordHash") return $query;
		else return $result;
	}

	public function getGroupName($groupID) {
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query->select($db->quoteName('#__opensim_group.Name'));
		$query->from($db->quoteName('#__opensim_group'));
		$query->where($db->quoteName('#__opensim_group.GroupID')." = ".$db->quote($groupID));
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getAccountableGroupMembers($groupID) {
		$grouphelper		= new InterfaceGroupsHelper();

		$params['GroupID']	= $groupID;
		$this->grouproles	= $grouphelper->getGroupRoles($params);
		$this->powers		= $grouphelper->getGroupPowers();
		$accountable		= $this->powers['Accountable'];
		$accountmembers		= array();
		foreach($this->grouproles AS $key => $role) {
			if((bool)((int)$role['Powers'] & (int)$accountable) === TRUE) {
				$groupmembers	= $this->getMembersInRole($groupID,$role['RoleID']);
				foreach($groupmembers AS $groupmember) {
					$accountmembers[$groupmember->AgentID]['uuid']	= $groupmember->AgentID;
					$accountmembers[$groupmember->AgentID]['name']	= $this->opensim->getUserName($groupmember->AgentID,"full");
				}
			}
		}
		return $accountmembers;
	}

	public function getMembersInRole($groupID,$roleID) {
		$db = Factory::getDBO();
		$query	= $db->getQuery(true);
		$query->select($db->quoteName('#__opensim_grouprolemembership.AgentID'));
		$query->from($db->quoteName('#__opensim_grouprolemembership'));
		$query->where($db->quoteName('#__opensim_grouprolemembership.GroupID')." = ".$db->quote($groupID));
		$query->where($db->quoteName('#__opensim_grouprolemembership.RoleID')." = ".$db->quote($roleID));
		$db->setQuery($query);
		$roleMembers = $db->loadObjectList();
		return $roleMembers;
	}

	public function emptyUserData() {
		$retval = array();
		$retval['uuid']			= null;
		$retval['firstname']	= null;
		$retval['lastname']		= null;
		$retval['name']			= null;
		$retval['email']		= null;
		$retval['userlevel']	= null;
		$retval['userflags']	= null;
		$retval['usertitle']	= null;
		$retval['born']			= null;
		return $retval;
	}

	public function emptyGridData() {
		$retval = array();
		$retval['last_login']	= null;
		$retval['last_logout']	= null;
		return $retval;
	}

	public function emptyAuthData() {
		$retval = array();
		$retval['passwordSalt']	= null;
		return $retval;
	}

	public function setDebug() {
		$this->jdebug['access']		= $this->settingsData['jopensim_debug_access'];
		$this->jdebug['input']		= $this->settingsData['jopensim_debug_input'];
		$this->jdebug['profile']	= $this->settingsData['jopensim_debug_profile'];
		$this->jdebug['groups']		= $this->settingsData['jopensim_debug_groups'];
		$this->jdebug['search']		= $this->settingsData['jopensim_debug_search'];
		$this->jdebug['messages']	= $this->settingsData['jopensim_debug_messages'];
		$this->jdebug['currency']	= $this->settingsData['jopensim_debug_currency'];
		$this->jdebug['terminal']	= $this->settingsData['jopensim_debug_terminal'];
		$this->jdebug['other']		= $this->settingsData['jopensim_debug_other'];
		$this->jdebug['any']		= $this->jdebug['access']
									+ $this->jdebug['input']
									+ $this->jdebug['profile']
									+ $this->jdebug['groups']
									+ $this->jdebug['search']
									+ $this->jdebug['messages']
									+ $this->jdebug['currency']
									+ $this->jdebug['other'];
		$this->setDebugLogfile();
	}

	public function setDebugLogfile() {
		$this->debuglogfile = $this->settingsData['jopensim_debug_path'].'interface.log';
	}

	public function addLogger() {
		Log::addLogger(
			array(
				'text_file' => 'jopensim-debug.php.log'
			),
			Log::ALL,
			array('jopensim-debug')
		);
	}

	public function debuglog($zeile,$function = "") {
		$logfile	= $this->debuglogfile;

		if(!$function) $zeit = "\n\n########## ".date("d.m.Y H:i:s")." ##########\n";
		else $zeit = "\n\n########## ".date("d.m.Y H:i:s")." ########## ".$function." ##########\n";
		$zeile = var_export($zeile,TRUE);
		$handle = fopen($logfile,"a+");
		$logzeile = $zeit.$zeile."\n\n";
		fputs($handle,$logzeile);
		fclose($handle);
	}

	public static function varexport($var) {
		$retval = var_export($var,TRUE);
		return $retval;
	}

	public function __destruct() {
	}
}