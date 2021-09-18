<?php

/**
 *  Whitelisting view.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage views
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('whitelisting');

///////////////////////////////////////////////////////////////////////////////
// Add Fingerprint form
///////////////////////////////////////////////////////////////////////////////

echo form_open('whitelisting/add_fingerprint');
echo form_header(lang('whitelisting_add_fingerprint'));

echo field_input('ja3hash', '', lang('whitelisting_ja3hash'));
echo field_input('category', '', lang('whitelisting_category'));
echo field_input('description', '', lang('whitelisting_description'));

echo field_button_set(
    array(
        form_submit_add('submit_fingerprint', 'high'),
        anchor_cancel('/app/whitelisting')
    )
);

echo form_footer();
echo form_close();
