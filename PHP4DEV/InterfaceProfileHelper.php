<?php
/*
 * @component jOpenSim
 * @copyright Copyright (C) 2021 FoTo50 https://www.jopensim.com/
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace jOpenSim\Component\OpenSim\Site\Helper;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use jOpenSim\Component\OpenSim\Administrator\Helper\jOpenSimHelper;

class InterfaceProfileHelper {

	public $debug	= FALSE;
	public $jdebug	= array();

	public function __construct() {
		$this->jOpenSimHelper	= new jOpenSimHelper();
		$this->jOpenSimHelper->setDebug();
		$this->uuidZero			= $this->jOpenSimHelper->zerouid;
		$this->settings			= $this->jOpenSimHelper->settingsData;
	}

	public function setDebug($jdebug) {
		if(!empty($jdebug['profile'])) $this->debug	= TRUE;
	}

	public function avatar_properties_request($parameter) {
		if(JDEBUG) Log::add('InterfaceProfileHelper avatar_properties_request: '.var_export($parameter,TRUE), Log::DEBUG, 'jopensim-debug');
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		if(!array_key_exists("avatar_id",$parameter)) {
			$retval['success'] = FALSE;
			$retval['errorMessage']	= "No avatar_id specified";
			return $retval;
		}

		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userprofile.partner')." AS Partner")
			->select($db->quoteName('#__opensim_userprofile.url')." AS ProfileUrl")
			->select($db->quoteName('#__opensim_userprofile.wantmask')." AS wantmask")
			->select($db->quoteName('#__opensim_userprofile.wanttext')." AS wanttext")
			->select($db->quoteName('#__opensim_userprofile.skillsmask')." AS skillsmask")
			->select($db->quoteName('#__opensim_userprofile.skillstext')." AS skillstext")
			->select($db->quoteName('#__opensim_userprofile.languages')." AS languages")
			->select($db->quoteName('#__opensim_userprofile.firstLifeImage')." AS FirstLifeImage")
			->select($db->quoteName('#__opensim_userprofile.firstLifeText')." AS FirstLifeAboutText")
			->select($db->quoteName('#__opensim_userprofile.image')." AS Image")
			->select($db->quoteName('#__opensim_userprofile.aboutText')." AS AboutText")
			->select($db->quoteName('#__opensim_userprofile.allowPublish')." AS userFlags")
			->from($db->quoteName('#__opensim_userprofile'))
			->where($db->quoteName('#__opensim_userprofile.avatar_id')." = ".$db->quote($parameter['avatar_id']));
		$db->setQuery($query);
		$profile			= $db->loadAssocList();
		$retval['success']	= TRUE;
		if(count($profile) == 1) {
			// decode first all during storing encoded fields
			$retval['data'][0]['Partner']				= $profile[0]['Partner'];
			$retval['data'][0]['ProfileUrl']			= utf8_decode($profile[0]['ProfileUrl']);
			$retval['data'][0]['wantmask']				= $profile[0]['wantmask'];
			$retval['data'][0]['wanttext']				= utf8_decode($profile[0]['wanttext']);
			$retval['data'][0]['skillsmask']			= $profile[0]['skillsmask'];
			$retval['data'][0]['skillstext']			= utf8_decode($profile[0]['skillstext']);
			$retval['data'][0]['languages']				= utf8_decode($profile[0]['languages']);
			$retval['data'][0]['FirstLifeImage']		= $profile[0]['FirstLifeImage'];
			$retval['data'][0]['FirstLifeAboutText']	= utf8_decode($profile[0]['FirstLifeAboutText']);
			$retval['data'][0]['Image']					= $profile[0]['Image'];
			$retval['data'][0]['AboutText']				= utf8_decode($profile[0]['AboutText']);
			$retval['data'][0]['userFlags']				= (string)$profile[0]['userFlags'];
		} else {
			$retval['data'][0]['Partner']				= null;
			$retval['data'][0]['ProfileUrl']			= null;
			$retval['data'][0]['wantmask']				= 0;
			$retval['data'][0]['wanttext']				= "";
			$retval['data'][0]['skillsmask']			= 0;
			$retval['data'][0]['skillstext']			= "";
			$retval['data'][0]['languages']				= "";
			$retval['data'][0]['FirstLifeImage']		= null;
			$retval['data'][0]['FirstLifeAboutText']	= "";
			$retval['data'][0]['Image']					= null;
			$retval['data'][0]['AboutText'] 			= Text::_('NOPROFILEAVAILABLE');
			$retval['data'][0]['userFlags']				= 0;
		}
		return $retval;
	}

	public function user_preferences_request($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_usersettings.im2email'))
			->select($db->quoteName('#__opensim_usersettings.visible'))
			->from($db->quoteName('#__opensim_usersettings'))
			->where($db->quoteName('#__opensim_usersettings.uuid')." = ".$db->quote($parameter['avatar_id']));
		$db->setQuery($query);
		$db->execute();
		$num_rows = $db->getNumRows();

		if($num_rows == 0) {
			$newSetting	= new \stdClass();
			$newSetting->uuid		= $parameter['avatar_id'];
			$newSetting->im2email	= 0;
			$newSetting->visible	= 0;

			$result = $db->insertObject('#__opensim_usersettings', $newSetting);

			if($result !== TRUE) {
				return array('error' => "Error while creating new usersettings for User ".$params["avatar_id"]);
			}
		} else {
			$usersettings = $db->loadAssoc();
		}
		$user['imviaemail']	= (isset($usersettings['im2email']) && $usersettings['im2email'] == 1) ? "true":"false";
		$user['visible']	= (isset($usersettings['visible']) && $usersettings['visible'] == 1) ? "true":"false";

		$usersettings2		= $this->opensim->getUserData($parameter['avatar_id']);

		$data[] = array('imviaemail' => $user['imviaemail'],
						'visible' 	 => $user['visible'],
						'email' 	 => $usersettings2['email'],
						'userFlags'	 => 65535);

		$retval['success']		= TRUE;
		$retval['errorMessage']	= "";
		$retval['data']			= $data;
		return $retval;
	}

	public function user_preferences_update($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$im2email	= (strtolower($parameter['imViaEmail']) == "true") ? "1":"0";
		$visible	= (strtolower($parameter['visible']) == "true") ? "1":"0";

		$db			= Factory::getDBO();
		$query		= $db->getQuery(true);
		$fields		= array(
					$db->quoteName('uuid').' = '.$db->quote($parameter['avatar_id'])
				);
		$conditions = array(
			$db->quoteName('im2email').' = '.$db->quote($im2email), 
			$db->quoteName('visible').' = '.$db->quote($visible)
		);
		$query->update($db->quoteName('#__opensim_usersettings'))->set($fields)->where($conditions);


		$db->setQuery($query);
		$db->execute();
		$retval['success']		= TRUE;
		$retval['errorMessage']	= "";
		return $retval;
	}

	public function avatar_properties_update($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db = Factory::getDBO();
		$query = sprintf("INSERT INTO #__opensim_userprofile
								(avatar_id,aboutText,allowPublish,firstLifeImage,image,url,firstLifeText)
							VALUES
								(%1\$s,%2\$s,%3\$s,%4\$s,%5\$s,%6\$s,%7\$s)
							ON DUPLICATE KEY UPDATE
								aboutText		= %2\$s,
								allowPublish	= %3\$s,
								firstLifeImage	= %4\$s,
								image			= %5\$s,
								url				= %6\$s,
								firstLifeText	= %7\$s",
					$db->quote($parameter['avatar_id']),
					$db->quote(utf8_encode($parameter['AboutText'])),
					$db->quote(utf8_encode($parameter['userFlags'])),
					$db->quote($parameter['FirstLifeImage']),
					$db->quote($parameter['Image']),
					$db->quote(utf8_encode($parameter['ProfileUrl'])),
					$db->quote(utf8_encode($parameter['FirstLifeAboutText'])));
		$db->setQuery($query);
		$result = $db->execute();
		if($result) { 
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";

			if($parameter['Image'] != $this->uuidZero && array_key_exists("ImageBase64", $parameter))
			{
				file_put_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['avatar_id'].'.png', base64_decode($parameter['ImageBase64']));
			} 
			else {
				unlink(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['avatar_id'].'.png');
			}

			if($parameter['FirstLifeImage'] != $this->uuidZero && array_key_exists("FirstLifeImageBase64", $parameter))
			{
				file_put_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['avatar_id'].'-fli.png', base64_decode($parameter['FirstLifeImageBase64']));
			} 
			else {
				unlink(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['avatar_id']."-fli.png");
			}
			
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATEPROFILE');
		}
		return $retval;
	}

	public function avatar_interests_update($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db = Factory::getDBO();
		$query = sprintf("INSERT INTO #__opensim_userprofile
									(avatar_id,skillstext,languages,wantmask,skillsmask,wanttext)
								VALUES
									(%1\$s,%2\$s,%3\$s,%4\$s,%5\$s,%6\$s)
								ON DUPLICATE KEY UPDATE
									skillstext	= %2\$s,
									languages	= %3\$s,
									wantmask	= %4\$s,
									skillsmask	= %5\$s,
									wanttext	= %6\$s",
						$db->quote($parameter['avatar_id']),
						$db->quote(utf8_encode($parameter['skillstext'])),
						$db->quote(utf8_encode($parameter['languages'])),
						$db->quote($parameter['wantmask']),
						$db->quote($parameter['skillsmask']),
						$db->quote(utf8_encode($parameter['wanttext'])));
		$db->setQuery($query);
		$result = $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATEPROFILE');
		}
		return $retval;
	}

	public function avatarnotesrequest($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_usernotes').".*")
			->from($db->quoteName('#__opensim_usernotes'))
			->where($db->quoteName('#__opensim_usernotes.avatar_id')." = ".$db->quote($parameter['avatar_id']))
			->where($db->quoteName('#__opensim_usernotes.target_id')." = ".$db->quote($parameter['uuid']));
		$db->setQuery($query);
		$notes = $db->loadAssocList();
		if(count($notes) == 0) {
			$retval['data'][0]['notes'] = null;
		} else {
			$retval['data'][0]['notes'] 	= utf8_decode($notes[0]['notes']);
		}
		$retval['data'][0]['targetid']	= $parameter['uuid'];
		$retval['success']				= TRUE;
		$retval['errorMessage']			= '';
		return $retval;
	}

	public function avatar_notes_update($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$avatar_id	= $parameter['avatar_id'];
		$target_uid	= $parameter['target_id'];
		$notes		= utf8_encode($parameter['notes']);
		$db			= Factory::getDBO();
		$query		= sprintf("INSERT INTO #__opensim_usernotes (avatar_id,target_id,notes) VALUES (%1\$s,%2\$s,%3\$s)
									ON DUPLICATE KEY UPDATE notes = %3\$s",
							$db->quote($avatar_id),
							$db->quote($target_uid),
							$db->quote($notes));
		$db->setQuery($query);
		$result = $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATENOTES');
		}
		return $retval;
	}

	public function avatarpicksrequest($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userpicks').".*")
			->from($db->quoteName('#__opensim_userpicks'))
			->where($db->quoteName('#__opensim_userpicks.creatoruuid')." = ".$db->quote($parameter['uuid']));
		$db->setQuery($query);
		$picks	= $db->loadAssocList();
		$data	= array();
		foreach($picks AS $pick) {
			$name = $pick['name'];
			$data[] = array('pickid' => $pick['pickuuid'],
							'name' 	 => $name );
		}
		$retval = array('success'		=> True,
						'errorMessage'	=> '',
						'data'			=> $data);
		return $retval;
	}

	public function pickinforequest($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userpicks').".*")
			->from($db->quoteName('#__opensim_userpicks'))
			->where($db->quoteName('#__opensim_userpicks.creatoruuid')." = ".$db->quote($parameter['avatar_id']))
			->where($db->quoteName('#__opensim_userpicks.pickuuid')." = ".$db->quote($parameter['pick_id']));
		$db->setQuery($query);
		$pick = $db->loadAssocList();
		$returnpick = $pick[0];
		$returnpick['description'] = $returnpick['description'];
		$returnpick['name'] = $returnpick['name'];
		$data[] = array(
				'pickuuid' 		=> $returnpick['pickuuid'],
				'creatoruuid' 	=> $returnpick['creatoruuid'],
				'toppick' 		=> $returnpick['toppick'],
				'parceluuid' 	=> $returnpick['parceluuid'],
				'name' 			=> utf8_decode($returnpick['name']),
				'description' 	=> utf8_decode($returnpick['description']),
				'snapshotuuid' 	=> $returnpick['snapshotuuid'],
				'user' 			=> utf8_decode($returnpick['user']),
				'originalname' 	=> utf8_decode($returnpick['originalname']),
				'simname' 		=> utf8_decode($returnpick['simname']),
				'posglobal' 	=> $returnpick['posglobal'],
				'sortorder'		=> $returnpick['sortorder'],
				'enabled' 		=> $returnpick['enabled']);
		$retval = array('success'		=> True,
						'errorMessage'	=> '',
						'data'			=> $data);
		return $retval;
	}

	public function picks_update($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$pickuuid		= $parameter['pick_id'];
		$creator		= $parameter['creator_id'];
		$toppick		= $parameter['top_pick'];
		$parceluuid		= $parameter['parcel_uuid'];
		$name			= $parameter['name'];
		$description	= $parameter['desc'];
		$snapshotuuid	= $parameter['snapshot_id']; 
		$user			= $parameter['user'];
		$original		= $parameter['name'];
		$simname		= $parameter['sim_name'];
		$posglobal		= $parameter['pos_global'];
		$sortorder		= $parameter['sort_order'];
		$enabled		= $parameter['enabled'];

		if ($parceluuid=='')  $parceluuid  = $this->uuidZero;
		if ($description=='') $description = Text::_('NO_DESCRIPTION');
		if ($user=='') 		  $user 	   = Text::_('UNKNOWN');
		if ($original=='') 	  $original    = Text::_('UNKNOWN');

		$db = Factory::getDBO();
		// remove old image from cache to avoid duplicates
		$query = $db->getQuery(true)
		->select('snapshotuuid')
		->from($db->quoteName('#__opensim_userpicks'))
		->where($db->quoteName('pickuuid').' = '.$db->quote($parameter['pick_id']));
		$db->setQuery($query);
		$pickImage = $db->loadResult();
		unlink(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$pickImage.'.png');
		
		$query = $db->getQuery(true);
		$query = sprintf("INSERT INTO #__opensim_userpicks
													(pickuuid,creatoruuid,toppick,parceluuid,name,description,snapshotuuid,user,originalname,simname,posglobal,sortorder,enabled)
												VALUES
													(%1\$s,%2\$s,%3\$s,%4\$s,%5\$s,%6\$s,%7\$s,%8\$s,%9\$s,%10\$s,%11\$s,%12\$s,%13\$s)
												ON DUPLICATE KEY UPDATE
													creatoruuid		= %2\$s,
													toppick			= %3\$s,
													parceluuid		= %4\$s,
													name			= %5\$s,
													description		= %6\$s,
													snapshotuuid	= %7\$s,
													user			= %8\$s,
													simname			= %10\$s,
													posglobal		= %11\$s,
													sortorder		= %12\$s,
													enabled			= %13\$s",
								$db->quote($pickuuid),
								$db->quote($creator),
								$db->quote($toppick),
								$db->quote($parceluuid),
								$db->quote($name),
								$db->quote(utf8_encode($description)),
								$db->quote($snapshotuuid),
								$db->quote($user),
								$db->quote($original),
								$db->quote($simname),
								$db->quote($posglobal),
								$db->quote($sortorder),
								$db->quote($enabled));
		$db->setQuery($query);
		$result = $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
			if($snapshotuuid != $this->uuidZero && array_key_exists("snapshot_Base64", $parameter)){
				file_put_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$snapshotuuid.'.png', base64_decode($parameter['snapshot_Base64']));
			}
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATEPICKS');
		}
		return $retval;
	}

	public function picks_delete($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db			= Factory::getDBO();
		$query = $db->getQuery(true)
		->select('snapshotuuid')
		->from($db->quoteName('#__opensim_userpicks'))
		->where($db->quoteName('pickuuid').' = '.$db->quote($parameter['pick_id']));
		$db->setQuery($query);
		$pickImage = $db->loadResult();

		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('pickuuid').' = '.$db->quote($parameter['pick_id'])
		);
		$query->delete($db->quoteName('#__opensim_userpicks'));
		$query->where($conditions);

		$db->setQuery($query);
		$result		= $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
			unlink(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$pickImage.".png");
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATEPICKS');
		}
		return $retval;
	}

	public function avatarclassifiedsrequest($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$classifieds = array();
		$data	= array();
		$db		= Factory::getDBO();
		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userclassifieds').".*")
			->from($db->quoteName('#__opensim_userclassifieds'))
			->where($db->quoteName('#__opensim_userclassifieds.creatoruuid')." = ".$db->quote($parameter['uuid']));
		$db->setQuery($query);
		$classifieds = $db->loadAssocList();
		foreach($classifieds AS $classified) {
			$name = $classified['name'];
			$data[] = array('classifiedid' => $classified['classifieduuid'],
							'name' 	 => $name );
		}
		$retval = array('success'		=> True,
						'errorMessage'	=> '',
						'data'			=> $data);
		return $retval;
	}

	public function classifiedinforequest($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db		= Factory::getDBO();

		$query	= $db->getQuery(true);
		$query
			->select($db->quoteName('#__opensim_userclassifieds').".*")
			->from($db->quoteName('#__opensim_userclassifieds'))
			->where($db->quoteName('#__opensim_userclassifieds.creatoruuid')." = ".$db->quote($parameter['avatar_id']))
			->where($db->quoteName('#__opensim_userclassifieds.classifieduuid')." = ".$db->quote($parameter['classified_id']));

		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows() > 0) {
			$classified = $db->loadAssocList();
			$returnclassified = $classified[0];
			$returnclassified['description'] = $returnclassified['description'];
			$returnclassified['name']		 = $returnclassified['name'];
			// for some reason OpenSim 0.9.1.1 can not handle the 32nd bit (of UInt32) for these fields
//			if(JDEBUG) Log::add('InterfaceProfileHelper classifiedinforequest '.$returnclassified['classifieduuid'].' expirationdate: '.$returnclassified['expirationdate'], Log::DEBUG, 'jopensim-debug');
			if($returnclassified['creationdate'] > 2147483647) $returnclassified['creationdate'] = 2147483647;
			if($returnclassified['expirationdate'] > 2147483647) $returnclassified['expirationdate'] = 2147483647;

			$data[] = array(
					'classifieduuid'	=> $returnclassified['classifieduuid'],
					'creatoruuid' 		=> $returnclassified['creatoruuid'],
					'creationdate'		=> $returnclassified['creationdate'],
					'expirationdate' 	=> $returnclassified['expirationdate'],
					'category' 			=> $returnclassified['category'],
					'name' 				=> $returnclassified['name'],
					'description' 		=> utf8_decode($returnclassified['description']),
					'parceluuid' 		=> $returnclassified['parceluuid'],
					'parentestate' 		=> $returnclassified['parentestate'],
					'snapshotuuid' 		=> $returnclassified['snapshotuuid'],
					'simname' 			=> $returnclassified['simname'],
					'posglobal' 		=> $returnclassified['posglobal'],
					'parcelname' 		=> $returnclassified['parcelname'],
					'classifiedflags' 	=> $returnclassified['classifiedflags'],
					'priceforlisting' 	=> $returnclassified['priceforlisting']);
			$retval = array('success'		=> True,
							'errorMessage'	=> '',
							'data'			=> $data);
		} else {
			$retval = array('success'		=> True,
							'errorMessage'	=> 'Nothing found in function classifiedinforequest for avatar_id '.$parameter['avatar_id']." and classified_id ".$parameter['classified_id'],
							'data'			=> array());
		}
		return $retval;
	}

	public function classified_update($parameter) {
		if(JDEBUG) Log::add('InterfaceProfileHelper classified_update parameter: '.var_export($parameter,TRUE), Log::DEBUG, 'jopensim-debug');
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$classifieduuid = $parameter['classifiedUUID'];
		$creator		= $parameter['creatorUUID'];
		$category		= $parameter['category'];
		$name			= $parameter['name'];
		$description	= utf8_encode($parameter['description']);
		$parceluuid		= (array_key_exists("parcel_uuid",$parameter)) ? $parameter['parcel_uuid']:null;
		$parentestate	= $parameter['parentestate'];
		$snapshotuuid	= $parameter['snapshotUUID'];
		$simname		= $parameter['sim_name'];
		$globalpos		= $parameter['globalpos'];
		$parcelname		= (array_key_exists("parcelname",$parameter)) ? $parameter['parcelname']:null;
		$classifiedflag = $parameter['classifiedFlags'];
		$priceforlist	= $parameter['classifiedPrice'];

		if ($parcelname=='')  $parcelname  = Text::_('UNKNOWN');
		if ($parceluuid=='')  $parceluuid  = $this->uuidZero;
		if ($description=='') $description = Text::_('NO_DESCRIPTION');

		$creationdate   = time();
		$settings = $this->settings;

		if($settings['classified_hide'] == -1) { //hide never
			$expirationdate = 2147483647; // this is the max positive integer value that a timestamp field / OpenSim can take
		} else {
			$expirationdate = time() + $settings['classified_hide'];
		}

		$db = Factory::getDBO();
		$query = sprintf("INSERT INTO #__opensim_userclassifieds
												(classifieduuid,creatoruuid,creationdate,expirationdate,category,name,description,parceluuid,parentestate,snapshotuuid,simname,posglobal,parcelname,classifiedflags,priceforlisting)
											VALUES
												(%1\$s,%2\$s,%3\$s,%4\$s,%5\$s,%6\$s,%7\$s,%8\$s,%9\$s,%10\$s,%11\$s,%12\$s,%13\$s,%14\$s,%15\$s)
											ON DUPLICATE KEY UPDATE
												creatoruuid		= %2\$s,
												category		= %5\$s,
												name			= %6\$s,
												description		= %7\$s,
												parceluuid		= %8\$s,
												parentestate	= %9\$s,
												snapshotuuid	= %10\$s,
												simname			= %11\$s,
												posglobal		= %12\$s,
												parcelname		= %13\$s,
												classifiedflags	= %14\$s,
												priceforlisting	= %15\$s",
								$db->quote($classifieduuid),
								$db->quote($creator),
								$db->quote($creationdate),
								$db->quote($expirationdate),
								$db->quote($category),
								$db->quote($name),
								$db->quote($description),
								$db->quote($parceluuid),
								$db->quote($parentestate),
								$db->quote($snapshotuuid),
								$db->quote($simname),
								$db->quote($globalpos),
								$db->quote($parcelname),
								$db->quote($classifiedflag),
								$db->quote($priceforlist));

		if(JDEBUG) Log::add('InterfaceProfileHelper classified_update query: '.$query, Log::DEBUG, 'jopensim-debug');


		$db->setQuery($query);
		$result = $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
			if(array_key_exists("snapshotBase64", $parameter))
			{
				file_put_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['classifiedUUID'].'.png', base64_decode($parameter['snapshotBase64']));
			}
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATECLASSIFIED');
		}
		return $retval;
	}

	public function classified_delete($parameter) {
		if($this->debug === TRUE) $this->jOpenSimHelper->debuglog($parameter,"Parameter for ".__FUNCTION__);
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('classifieduuid').' = '.$db->quote($parameter['classifiedID'])
		);
		$query->delete($db->quoteName('#__opensim_userclassifieds'));
		$query->where($conditions);

		$db->setQuery($query);
		$result = $db->execute();
		if($result) {
			$retval['success']		= TRUE;
			$retval['errorMessage']	= "";
			unlink(JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'jopensim'.DIRECTORY_SEPARATOR.'texturecache'.DIRECTORY_SEPARATOR.$parameter['classifiedID'].".png");
		} else {
			$retval['success']		= FALSE;
			$retval['errorMessage']	= Text::_('ERROR_UPDATECLASSIFIED');
		}
		return $retval;
	}

	public function clientInfo($parameter, $source = "P") {
		$this->jOpenSimHelper->clientInfo($parameter, $source);
	}

	public function addon_disabled($parameter) {
		$retval['success'] = FALSE;
		$retval['error'] = "Profile Addon disabled in jOpenSim";
		$retval['params'] = $parameter;
		return $retval;
	}
}
?>