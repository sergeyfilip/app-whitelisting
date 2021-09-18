<?php

/**
 *  Tools Monitor view.
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

echo "<div class='panel-tab clearfix app_header_tab'>
	<ul class='tab-bar'>
	    <li><a href='/app/whitelisting'><i class='fa fa-inbox'></i> ".lang('whitelisting_fingerprint')."</a></li>
	    <li><a href='/app/whitelisting/policy_list'><i class='fa fa-exclamation'></i> ".lang('whitelisting_policy_mang')."</a></li>
	    <li><a href='/app/whitelisting/group_list'><i class='fa fa-users'></i> ".lang('whitelisting_group_mang')."</a></li>
	    <li class='active'><a href='/app/whitelisting/tools_monitor'><i class='fa fa-eye'></i> ".lang('whitelisting_tools')."</a></li>
	    <li><a href='/app/whitelisting/rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_rules')."</a></li>
        <li><a href='/app/whitelisting/individual_rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_individual_rules')."</a></li>
	</ul>
     </div>";

///////////////////////////////////////////////////////////////////////////////
// alert msg box
///////////////////////////////////////////////////////////////////////////////

echo infobox_info(
    'info',
    '',
    array('id' => 'whitelisting_app_info_box','hidden'=>true,)
);

echo infobox_warning(
    'Warning',
    '',
    array('id' => 'whitelisting_app_info_box','hidden'=>true,)
);

///////////////////////////////////////////////////////////////////////////////
// Interval form
///////////////////////////////////////////////////////////////////////////////

echo form_open('whitelisting/tools_monitor/');
echo form_header('Monitor Report By Time');

echo field_dropdown('timeRange', $ranges, $range, 'Select Report', false);

echo form_footer();
echo form_close();

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    'Timestamp',
    'Source IP',
    'Destination IP',
    'Country',
    'Description',
    'Rule Type'
);

///////////////////////////////////////////////////////////////////////////////
// Options
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'tools_monitor_tbl',
    'no_action' => false
);

echo ajax_summary_table(
    lang('whitelisting_tools'),
    $buttons,
    $headers,
    $options
);

// css to style buttons in table row
echo "<style>
tr td:last-child {
min-width:160px;

}
</style>";
