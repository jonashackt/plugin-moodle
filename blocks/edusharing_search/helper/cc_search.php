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

/*
 - called from the /blocks/cc_search block
 - auth against alfresco repos. (ticket handshake / user sync)
 - opens external edu-sharingSearch in iFrame
 */
 
/**
 * @package    block
 * @subpackage edusharing_search
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

global $DB;
global $CFG;
global $SESSION;

require_once('../../../mod/edusharing/lib/cclib.php');
require_once('../../../mod/edusharing/lib.php');

$appProperties = json_decode(get_config('edusharing', 'appProperties'));

$id = optional_param('id', 0, PARAM_INT);
if ( ! $id ) {
    print_error("None or invalid course-id given.");
    exit();
}

$PAGE->set_url('/blocks/edusharing_search/helper/cc_search.php',array('id' => $id,'search'=>'test'));

$course = $DB->get_record('course', array('id' => $id));
if ( ! $course ) {
    print_error("Course not found.");
    exit();
}

require_login($course->id);
echo $OUTPUT->header();

$ccauth = new mod_edusharing_web_service_factory();
$ticket = $ccauth->mod_edusharing_authentication_get_ticket($appProperties -> appid);
if ( ! $ticket ) {
    exit();
}

if ( empty($appProperties -> cc_gui_url) )
{
    trigger_error('No "cc_gui_url" configured.', E_ERROR);
}

$link = $appProperties -> cc_gui_url; // link to the external cc-search
$link .= '?mode=0';
$user = mod_edusharing_get_auth_key();
$link .= '&user='.urlencode($user);
$link .= '&ticket='.urlencode($ticket);

$_my_lang = mod_edusharing_get_current_users_language_code();
$link .= '&locale=' . urlencode($_my_lang);

$link .= '&p_startsearch=1';   ////////// 0=suche  / 1=workspace

$search = trim(optional_param('search', '', PARAM_NOTAGS)); // query for the external cc-search
if (!empty($search)) {
    $link .= '&p_searchtext='.urlencode($search);
}

// -------------------------------------------------------------------------
//  open the external edu-sharingSearch page in iframe
// -------------------------------------------------------------------------

?>



<div id="esContent" style="position: fixed; top: 0; left: 0; z-index: 5000;"></div>
<script src="<?php echo $CFG->wwwroot?>/mod/edusharing/js/jquery.min.js"></script>
<script>
    $('html, body').css('overflow', 'hidden');
    $('#esContent').width($(document).width());
    $('#esContent').height($(document).height());
    $('#esContent').height($(document).height());
    $('#esContent').html("<div id='closer' style='font-size: 1em; padding: 5px 20px 5px 20px; cursor: pointer; color: #000; background: #eee; '>◄&nbsp;&nbsp;Zur&uuml;ck zu &nbsp;\"<?php echo $COURSE->fullname?>\"</div><iframe id='childFrame' name='mainContent' src='<?php echo htmlentities($link)?>' width='100%' height='100%' scrolling='yes'  marginwidth='0' marginheight='0' frameborder='0'>&nbsp;</iframe>");
    $('#closer').click(function(){window.location.href='<?php echo $_SERVER["HTTP_REFERER"]?>';})
</script>
<?php

// -------------------------------------------------------------------------

$OUTPUT->footer();
exit();
