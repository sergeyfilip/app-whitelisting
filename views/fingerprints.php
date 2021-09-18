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

$buttons =  array(
    anchor_custom('/app/whitelisting/whitelist_all_rules', 'Generate/Regenerate group rules', 'high')
    
);

if ($whitelisting_status['status'] == 'running') {
    $buttons[] = anchor_custom('/app/whitelisting/stop_whitelisting', 'Stop Whitelisting', 'high');
} else {
    echo infobox_info(
        'Info',
        lang('whitelisting_info_msg'),
        array('id' => 'whitelisting_start_msg')
    );
    $buttons[] = anchor_custom('/app/whitelisting/start_whitelisting', 'Start/Restart Whitelisting', 'high');
}
$buttons[] = anchor_custom('/app/whitelisting/make_update', 'Check updates', 'high');

echo "<div class='panel-tab clearfix app_header_tab'>
	<ul class='tab-bar'>
	    <li class='active'><a href='/app/whitelisting'><i class='fa fa-inbox'></i> ".lang('whitelisting_fingerprint')."</a></li>
	    <li><a href='/app/whitelisting/policy_list'><i class='fa fa-exclamation'></i> ".lang('whitelisting_policy_mang')."</a></li>
	    <li><a href='/app/whitelisting/group_list'><i class='fa fa-users'></i> ".lang('whitelisting_group_mang')."</a></li>
	    <li><a href='/app/whitelisting/tools_monitor'><i class='fa fa-eye'></i> ".lang('whitelisting_tools')."</a></li>
	    <li><a href='/app/whitelisting/rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_rules')."</a></li>
        <li><a href='/app/whitelisting/individual_rules'><i class='fa fa-exclamation'></i> ".lang('whitelisting_individual_rules')."</a></li>
	</ul>
     </div>";

///////////////////////////////////////////////////////////////////////////////
// Chart
///////////////////////////////////////////////////////////////////////////////

$drop_opt = array('ip'=>'IP', 'name'=>'Name', 'network interface'=>'Network Interface');

$options['action'] = button_set($buttons);

$options['drop_down'] = array(
                "id"=>"gw_filterby_drp",
                "opt"=>$drop_opt,
                "selected"=>"ip",
                "name"=>"Iddevices",
                "style"=>"width:calc(50% - 1px);float:left;margin-left:1px;",
                "add_html"=>"<input type='text' id='sort_gw_devices' name='sort_gw_devices' value='' class='form-control theme-dropdown bounceIn animation-delay2' style='width:calc(50% - 1px);float:left;margin-right:1px;'>",
            );

echo chart_container('Devices', 'gwdevices', $options);

echo '
<style>
input[type="radio"]:checked + .custom-radio:before {
	background-color: #2196F3;
}
</style>
';
//scripts
echo "<script type='text/javascript' src='".crystaleye_theme_url('CrystalEye-Admin')."/js/networkmap/vivagraph.js'></script>";
echo "<script src='".crystaleye_theme_url('CrystalEye-Admin')."/js/networkmap/index.js'></script>";
