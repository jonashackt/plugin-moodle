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
 * This file keeps track of upgrades to the edusharing module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installtion to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in
 * lib/ddllib.php
 *
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_edusharing_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_edusharing_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    $result = true;

    if ($result && $oldversion < 2016011401) {

        //usage2
        try {
            $xmldb_table = new xmldb_table('edusharing');       
            $sql = 'UPDATE {edusharing} SET object_version = 0 WHERE window_versionshow = 1';
            $DB->execute($sql);
            $sql = 'UPDATE {edusharing} SET object_version = window_version WHERE window_versionshow = 0';
            $DB->execute($sql);
            $xmldb_field = new xmldb_field('window_versionshow');
            $dbman ->drop_field($xmldb_table, $xmldb_field);
            $xmldb_field = new xmldb_field('window_version');
            $dbman ->drop_field($xmldb_table, $xmldb_field);
        } catch(Exception $e) {
            error_log($e);
        }
        
        $homeConf = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'homeApplication.properties.xml';
        if(file_exists($homeConf)) {
            $app = new DOMDocument();
            $app->load($homeConf);
            $app->preserveWhiteSpace = false;
            $entrys = $app->getElementsByTagName('entry');
            foreach ($entrys as $entry) {
                $homeAppProperties[$entry -> getAttribute('key')] = $entry -> nodeValue;
            }
            
            $homeAppProperties['blowfishkey'] = 'thetestkey';
            $homeAppProperties['blowfishiv'] = 'initvect';
            
            set_config('appProperties', json_encode($homeAppProperties), 'edusharing');   
        }

        $repoConf = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'esmain'.DIRECTORY_SEPARATOR.'app-'. $homeAppProperties['homerepid'] .'.properties.xml';
        if(file_exists($repoConf)) {
            $app = new DOMDocument();
            $app->load($repoConf);
            $app->preserveWhiteSpace = false;
            $entrys = $app->getElementsByTagName('entry');
            foreach ($entrys as $entry) {
                $repoProperties[$entry -> getAttribute('key')] = $entry -> nodeValue;
            }
            
            $repoProperties['authenticationwebservice'] = str_replace('authentication', 'authbyapp', $repoProperties['authenticationwebservice']);
            $repoProperties['authenticationwebservice_wsdl'] = str_replace('authentication', 'authbyapp', $repoProperties['authenticationwebservice_wsdl']);
            if(mb_substr($repoProperties['usagewebservice'], -1) != '2')
                $repoProperties['usagewebservice'] = $repoProperties['usagewebservice'] . '2';
            $repoProperties['usagewebservice_wsdl'] = str_replace('usage?wsdl', 'usage2?wsdl', $repoProperties['usagewebservice_wsdl']);
            $repoProperties['contenturl'] = $repoProperties['clientprotocol'] . '://' . $repoProperties['domain'] . ':' . $repoProperties['clientport'] . '/edu-sharing/renderingproxy';
            
            set_config('repProperties', json_encode($repoProperties), 'edusharing');   
        }

        try {
            
            include(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'cs_conf.php');

            set_config('EDU_AUTH_KEY', EDU_AUTH_KEY, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_USERID', EDU_AUTH_PARAM_NAME_USERID, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_LASTNAME', EDU_AUTH_PARAM_NAME_LASTNAME, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', EDU_AUTH_PARAM_NAME_FIRSTNAME, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_EMAIL', EDU_AUTH_PARAM_NAME_EMAIL, 'edusharing');
        
        } catch(Exception $e) {
            error_log($e);
        }

    }
    return $result;
}
