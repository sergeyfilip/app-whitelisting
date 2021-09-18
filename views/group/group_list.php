<?php

/**
 *  Group List view.
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
	    <li class='active'><a href='/app/whitelisting/group_list'><i class='fa fa-users'></i> ".lang('whitelisting_group_mang')."</a></li>
	    <li><a href='/app/whitelisting/tools_monitor'><i class='fa fa-eye'></i> ".lang('whitelisting_tools')."</a></li>
	    <li><a href='/app/whitelisting/rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_rules')."</a></li>
        <li><a href='/app/whitelisting/individual_rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_individual_rules')."</a></li>
	</ul>
     </div>";

///////////////////////////////////////////////////////////////////////////////
// Rules summary table
///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    'Group Name',
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($groups_list['groups'] as $key => $group) {
    $item['anchors'] = button_set(
        array(
                anchor_custom('/app/whitelisting/group_list/group_members/'.$key, 'Edit Members'),
                anchor_custom('/app/whitelisting/group_list/add_edit_group/edit/'.$key, 'Edit'),
                anchor_custom('/app/whitelisting/group_list/delete_group/'.$key.'/'.$group['name'], 'Delete', 'low')
                )
    );


    if ($group['name'] == 'default') {
        $item['anchors'] = anchor_custom('/app/whitelisting/group_list/group_members/'.$key, 'View', 'low');
    }

    $item['details'] = array(
               $group['name']
            );
    $items[] = $item;
}

$options = array(
    'default_rows' => 5,
    'sort-default-col' => 0,
);

$buttons = anchor_custom('/app/whitelisting/group_list/add_edit_group/add/', 'Add Group');

///////////////////////////////////////////////////////////////////////////////
// rules summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    'Groups',
    $buttons,
    $headers,
    $items,
    $options
);

echo "<style>
tr td:last-child {
min-width:160px;

}
</style>";
