<?php
// This file is part of edu-sharing created by metaVentis GmbH — http://metaventis.com
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The main edusharing configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot.'/mod/edusharing/locallib.php');

class mod_edusharing_mod_form extends moodleform_mod
{

	/**
	 * (non-PHPdoc)
	 * @see lib/moodleform::definition()
	 */
	function definition()
	{
		global $CFG;
		global $COURSE;

		$appProperties = json_decode(get_config('edusharing', 'appProperties'));

		try
		{
			// @TODO make dynamic
			$ccauth = new mod_edusharing_web_service_factory();
			$ticket = $ccauth->mod_edusharing_authentication_get_ticket($appProperties -> appid);
		}
		catch(Exception $e)
		{
			error_log( print_r($e, true) );

			print_error($e -> getMessage());
			print_footer(" ");

			return false;
		}

		$mform =& $this->_form;

//-------------------------------------------------------------------------------
	/// Adding the "general" fieldset, where all the common settings are showed
		$mform->addElement('header', 'general', get_string('general', 'form'));

	/// Adding the standard "name" field
		$mform->addElement('text', 'name', get_string('edusharingname', EDUSHARING_MODULE_NAME), array('size'=>'64'));
		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEAN);
		}

		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

	/// Adding the standard "intro" and "introformat" fields
		//$this->add_intro_editor();
	if (method_exists($this,'standard_intro_elements')) {
      	$this->standard_intro_elements() ;
     }else {
		$this->add_intro_editor();
	 }
	 
//-------------------------------------------------------------------------------
	/// object-section
		$mform->addElement('header', 'object_url_fieldset', get_string('object_url_fieldset', EDUSHARING_MODULE_NAME));

		// object-uri
		$mform->addElement('text', 'object_url', get_string('object_url', EDUSHARING_MODULE_NAME), array('readonly' => 'true'));
		$mform->setType('object_url', PARAM_RAW_TRIMMED);
		$mform->addRule('object_url', null, 'required', null, 'client');
		$mform->addRule('object_url', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
		$mform->addHelpButton('object_url', 'object_url', EDUSHARING_MODULE_NAME);

		$_my_lang = mod_edusharing_get_current_users_language_code();

		$ccresource_search  = $appProperties -> cc_gui_url;
		$ccresource_search .= "?mode=0";
		$ccresource_search .= "&user=".urlencode(mod_edusharing_get_auth_key());
		$ccresource_search .= "&locale=".$_my_lang;
		$ccresource_search .= "&ticket=".$ticket;
		$ccresource_search .= "&reurl=".urlencode($CFG->wwwroot."/mod/edusharing/makelink.php");

		$searchbutton = $mform->addElement('button', 'searchbutton', get_string('searchrec', EDUSHARING_MODULE_NAME).'...');
		$buttonattributes = array('title'=>get_string('searchrec', EDUSHARING_MODULE_NAME), 'onclick'=>"return window.open('"
						  . "$ccresource_search', '_blank', 'menubar=0,location=0,directories=0,toolbar=0,"
						  . "scrollbars,resizable,width=1000,height=580');");
		$searchbutton->updateAttributes($buttonattributes);



		$ccresource_upload  = $appProperties -> cc_gui_url;
		$ccresource_upload .= "?mode=2";
		$ccresource_search .= "&user=".urlencode(mod_edusharing_get_auth_key());
		$ccresource_upload .= "&locale=".$_my_lang;
		$ccresource_upload .= "&ticket=".$ticket;
		$ccresource_upload .= "&reurl=".urlencode($CFG->wwwroot."/mod/edusharing/makelink.php");

		$uploadbutton = $mform->addElement('button', 'uploadbutton', get_string('uploadrec', EDUSHARING_MODULE_NAME).'...');
		$buttonattributes = array('title'=>get_string('uploadrec', EDUSHARING_MODULE_NAME), 'onclick'=>"return window.open('"
						  . "$ccresource_upload', '_blank', 'menubar=0,location=0,directories=0,toolbar=0,"
						  . "scrollbars,resizable,width=1000,height=580');");
		$uploadbutton->updateAttributes($buttonattributes);

//-------------------------------------------------------------------------------
	/// version-section
		$mform->addElement('header', 'version_fieldset', get_string('object_version_fieldset', EDUSHARING_MODULE_NAME));

		$radioGroup=array();
		$radioGroup[] = $mform->createElement('radio', 'object_version', '', get_string('object_version_use_latest', EDUSHARING_MODULE_NAME), 0, array());
		$radioGroup[] = $mform->createElement('radio', 'object_version', '', get_string('object_version_use_exact', EDUSHARING_MODULE_NAME), 1, array());

		$mform->addGroup($radioGroup, 'object_version', get_string('object_version', EDUSHARING_MODULE_NAME), array(' '), false);
		$mform->setDefault('object_version', 0);

		$mform->addHelpButton('object_version', 'object_version', EDUSHARING_MODULE_NAME);

//-------------------------------------------------------------------------------
	/// display-section
		$mform->addElement('header', 'object_display_fieldset', get_string('object_display_fieldset', EDUSHARING_MODULE_NAME));
		$window_options = array(0 => get_string('pagewindow', EDUSHARING_MODULE_NAME), 1 => get_string('newwindow', EDUSHARING_MODULE_NAME));
		$mform->addElement('select', 'popup_window', get_string('display', EDUSHARING_MODULE_NAME), $window_options);
		$mform->setDefault('popup_window', !empty($CFG->resource_popup));


//-------------------------------------------------------------------------------
		// add standard elements, common to all modules
		$this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
		// add standard buttons, common to all modules
		//$this->add_action_buttons();
		
		
        $submit2label = get_string('savechangesandreturntocourse');
        $mform = $this->_form;
        $buttonarray = array();
        if ($submit2label !== false && $this->courseformat->has_view_page()) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
	}

	/**
	 * Preprocess valus to be stored in database.
	 *
	 * (non-PHPdoc)
	 * @see course/moodleform_mod::data_preprocessing()
	 */
	function data_preprocessing(&$values){
		parent::data_preprocessing($values);
	}

	/**
	 * (non-PHPdoc)
	 * @see course/moodleform_mod::set_data()
	 */
	function set_data($default_values) {
		parent::set_data($default_values);
	}

}
