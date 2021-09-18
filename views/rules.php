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

echo "<div class='panel-tab clearfix app_header_tab'>
	<ul class='tab-bar'>
	    <li><a href='/app/whitelisting'><i class='fa fa-inbox'></i> ".lang('whitelisting_fingerprint')."</a></li>
	    <li><a href='/app/whitelisting/policy_list'><i class='fa fa-exclamation'></i> ".lang('whitelisting_policy_mang')."</a></li>
	    <li><a href='/app/whitelisting/group_list'><i class='fa fa-users'></i> ".lang('whitelisting_group_mang')."</a></li>
	    <li><a href='/app/whitelisting/tools_monitor'><i class='fa fa-eye'></i> ".lang('whitelisting_tools')."</a></li>
	    <li class='active'><a href='/app/whitelisting/rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_rules')."</a></li>
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
    lang('whitelisting_rules'),
);

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($rules as $rule) {
    if (!isset($rule) || empty($rule)) {
        continue;
    }

    $rulestate = (preg_match('/^#/', $rule) ==1)? false:true;

    if (preg_match('/sid:([\d]+);/', $rule, $match)) {
        $sid = $match[1];
    }

    if (preg_match('/^alert/', $rule) ==1 || preg_match('/^#alert/', $rule) ==1) {
        $submenu = array(
              '/app/whitelisting/rules/edit_rule/'.$sid.'/drop'=> 'Convert to Drop',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/reject' => 'Convert to Reject',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/pass' => 'Convert to Pass'
              );
    } elseif (preg_match('/^drop/', $rule) ==1 || preg_match('/^#drop/', $rule) ==1) {
        $submenu = array(
              '/app/whitelisting/rules/edit_rule/'.$sid.'/alert'=> 'Convert to Alert',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/reject' => 'Convert to Reject',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/pass' => 'Convert to Pass'
              );
    } elseif (preg_match('/^reject/', $rule) ==1 || preg_match('/^#reject/', $rule) ==1) {
        $submenu = array(
              '/app/whitelisting/rules/edit_rule/'.$sid.'/alert'=> 'Convert to Alert',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/drop' => 'Convert to Drop',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/pass' => 'Convert to Pass'
              );
    } elseif (preg_match('/^pass/', $rule) ==1 || preg_match('/^#pass/', $rule) ==1) {
        $submenu = array(
              '/app/whitelisting/rules/edit_rule/'.$sid.'/alert'=> 'Convert to Alert',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/drop' => 'Convert to Drop',
              '/app/whitelisting/rules/edit_rule/'.$sid.'/reject' => 'Convert to Reject'
              );
    }

    $state = ($rulestate) ? 'disable' : 'enable';
    $state_anchor = 'anchor_' . $state;   // to set enable disable button

    $item['current_state'] = $rulestate; //for status icon
    $item['anchors'] = button_set(
        array(
                $state_anchor('/app/whitelisting/rules/toggle_status/'.$sid, 'high'),
                anchor_multi($submenu, 'Op', 'high')
                )
    );
    $item['details'] = array(wordwrap($rule, 30, "<wbr>", true));
    $items[] = $item;
}

$options = array(
    'default_rows' => 5,
    'sort-default-col' => 0,
    'row-enable-disable' => true
);

///////////////////////////////////////////////////////////////////////////////
// rules summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('whitelisting_rules'),
    null,
    $headers,
    $items,
    $options
);

echo "<style>
tr td:last-child {
min-width:160px;

}
</style>";
