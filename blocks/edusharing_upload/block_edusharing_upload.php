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
 * @package    block
 * @subpackage edusharing_upload
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class block_edusharing_upload extends block_base {

	public function init() {
		$this->title   = get_string('block_title', 'block_edusharing_upload');
		$this->version = 2015060901;
	}

	/**
	 * (non-PHPdoc)
	 * @see blocks/block_base::get_content()
	 */
    public function get_content()
    {
		if ($this->content !== NULL)
		{
			return $this->content;
		}

		global $CFG;
		global $COURSE;

		$this->content =  new stdClass;
		$this->content->text = '<form action="'.htmlentities($CFG->wwwroot.'/blocks/edusharing_upload/helper/cc_upload.php').'" method="get"><input type="hidden" name="id" value="'.htmlentities($COURSE->id).'" /><input type="submit" value="'.htmlentities(get_string('button_text', 'block_edusharing_upload')).'" /></form>';

		return $this->content;
	}

}
