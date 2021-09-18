<?php

/**
 *  Whitelisting class.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage libraries
 * @author     RedPiranha <staff@redpiranha.net>
 * @copyright  2020 RedPiranha
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace crystaleye\apps\whitelisting;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CRYSTALEYE_BOOTSTRAP') ? getenv('CRYSTALEYE_BOOTSTRAP') : '/usr/crystaleye/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

crystaleye_load_language('base');
crystaleye_load_language('whitelisting');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////
use DateTime as DateTime;
use \crystaleye\apps\reports\Report_Engine as Report_Engine;
use \crystaleye\apps\base\Shell as Shell;
use \crystaleye\apps\base\File as File;
use \crystaleye\apps\intrusion_detection\Suricata as Suricata;

crystaleye_load_library('reports/Report_Engine');
crystaleye_load_library('date/Time');

crystaleye_load_library('base/File');
crystaleye_load_library('intrusion_detection/Suricata');

// Exceptions
//-----------

use \crystaleye\apps\base\Engine_Exception as Engine_Exception;

crystaleye_load_library('base/Engine_Exception');
crystaleye_load_library('base/Shell');
///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 *  Whitelisting class.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage libraries
 * @author     RedPiranha <staff@redpiranha.net>
 * @copyright  2013 RedPiranha
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/
 */

class Whitelisting extends Engine_Exception
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    //const FINGUREPRINTS_JSON_FILE = '/etc/ja3parser/full.json';
    // const RULES_FILE = '/etc/suricata/rules/white_list.rules';
    const RULES_FOLDER = '/etc/suricata.d/rules/redpiranha/';
    const FINGUREPRINTS_JSON_FILE = '/etc/jparser/full.json';
    const RULES_FILE = '/etc/suricata.d/rules/redpiranha/white_list.rules';
    const WHITE_LIST_JSON_FILE = '/var/lib/jparser/white_list_report.json';
    const NICKNAMES_FILE ='/var/crystaleye/network_map/nicknames.dat';

    const WHITE_LIST_DATA = '/var/lib/jparser/white_list_data.txt';
    const WHITE_LIST_GROUPS_JSON_FILE = '/var/lib/jparser/groups.json';
    const WHITE_LIST_POLICIES_JSON_FILE = '/var/lib/jparser/policies.json';
    const WHITE_LIST_DEVICES_TXT_FILE = '/var/lib/jparser/devices.txt';
    const WHITE_LIST_CATEGORIES_FILE = '/etc/jparser/categories.txt';
    const AWL_ALERT_TEXT = 'whitelisting';
    const UPDATE_URL = 'https://updates.redpiranha.net/userver/ja3.tar.gz';
    const INDIVIDUAL_RULES_FILE = '/etc/suricata.d/rules/redpiranha/white_list_ind.rules';
    protected $is_loaded = false;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     *  whitelisting constructor.
     */

    public function __construct()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        $this->_load_config();
    }

    /**
     * Load config files configuration.
     * set is_loaded
     * @throws File_Exception
     */
    protected function _load_config()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $fingureprint_file = new File(self::FINGUREPRINTS_JSON_FILE);
        if (!$fingureprint_file->exists()) {
            $fingureprint_file->create('root', 'root', '0755');
        }

        $rules_file = new File(self::RULES_FILE);
        if (!$rules_file->exists()) {
            $rules_file->create('root', 'root', '0755');
        }

        $individual_rules_file = new File(self::INDIVIDUAL_RULES_FILE);
        if (!$individual_rules_file->exists()) {
            $individual_rules_file->create('root', 'root', '0755');
        }

        $this->is_loaded = true;
    }

    /**
     * get fingureprints records.
     * return array
     * @throws File_Exception
     */
    public function get_fingureprints()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded) {
            throw new Engine_Exception('Fingureprint File not loaded.');
        }


        $fingureprint_json_file = new File(self::FINGUREPRINTS_JSON_FILE);
        $line_arr = $fingureprint_json_file->get_contents_as_array();

        return $line_arr;
    }

    /**
     * get rules records.
     * return array
     * @throws File_Exception
     */
    public function get_rules($type = null)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if ($type == 'individual') {
            $fileName = self::INDIVIDUAL_RULES_FILE;
        } else {
            $fileName = self::RULES_FILE;
        }

        if (!$this->is_loaded) {
            throw new Engine_Exception('Rules File not loaded.');
        }


        $rules_file = new File($fileName);
        $line_arr = $rules_file->get_contents_as_array();

        return $line_arr;
    }

    // toggle  whitelist rule
    // return boolen true
    public function toggle_whitelist_rule($sid, $type = null)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if ($type == 'individual') {
            $fileName = self::INDIVIDUAL_RULES_FILE;
        } else {
            $fileName = self::RULES_FILE;
        }

        $file = new File($fileName);
        $lines = $file->get_contents_as_array();

        $new_list = array();

        foreach ($lines as $line) {
            $pos = strpos($line, $sid);
            if ($pos ===false) {
                $new_list[] = $line;
            } else {
                if (preg_match('/^#/', $line)) {
                    $new_list[] = ltrim($line, '#');
                } else {
                    $new_list[] = '#'.$line;
                }
            }
        }
        $file->delete();
        $file->create('root', 'root', '0644');
        $file->dump_contents_from_array($new_list);
        return true;
    }

    // edit  whitelist rule
    // return boolen true
    public function edit_whitelist_rule($sid, $action, $type = null)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if ($type == 'individual') {
            $fileName = self::INDIVIDUAL_RULES_FILE;
        } else {
            $fileName = self::RULES_FILE;
        }

        $file = new File($fileName);
        $lines = $file->get_contents_as_array();
        $new_list = array();

        foreach ($lines as $line) {
            $pos = strpos($line, $sid);
            if ($pos ===false) {
                $new_list[] = $line;
            } else {
                if (preg_match('/drop/', $line)) {
                    $new_list[] = str_replace("drop", $action, $line);
                } elseif (preg_match('/alert/', $line)) {
                    $new_list[] = str_replace("alert", $action, $line);
                } elseif (preg_match('/reject/', $line)) {
                    $new_list[] = str_replace("reject", $action, $line);
                } elseif (preg_match('/pass/', $line)) {
                    $new_list[] = str_replace("pass", $action, $line);
                } else {
                    $new_list[] = $line;
                }
            }
        }

        $file->delete();
        $file->create('root', 'root', '0644');
        $file->dump_contents_from_array($new_list);
        return true;
    }

    // add fingerprint rule
    // return boolen true
    public function add_fingerprint_rule($js3_hash, $category, $desc)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $file = new File(self::FINGUREPRINTS_JSON_FILE);

        $line = $file->get_tail(1);
        $line_arr = json_decode($line[0], true);
        $new_id = $line_arr['#']+1;
        $add_new_line_arr = array(
            "#"=>$new_id,
            "Ja3_hash"=>$js3_hash,
            "category"=>$category,
            "desc"=>$desc
        );

        $add_new_line_str = json_encode($add_new_line_arr);
        $file->add_lines($add_new_line_str);

        return true;
    }

    // delete fingerprint rule
    // return boolen true
    public function remove_fingerprint_rule($rule)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $file = new File(self::FINGUREPRINTS_JSON_FILE);

        $lines = $file->get_contents_as_array();
        $new_list = array();

        foreach ($lines as $line) {
            $rule_details = json_decode($line, true);

            if ($rule_details['#'] != $rule) {
                $new_list[] = $line;
            }
        }
        $file->delete();
        $file->create('root', 'root', '0644');
        $file->dump_contents_from_array($new_list);
        return true;
    }

    // get devices details from white_list_report.json file
    public function get_devices_list($filterby, $filterstring)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $devices = array();
        try {
            $whitelist_json_file = new File(self::WHITE_LIST_JSON_FILE);
            $line_arr = $whitelist_json_file->read_json();

            $devices[] = array(
            'ip' => 'CrystalEyeGateway',
            'name' => 'ceserver',
            'network_interface' => '',
            'fingureprintkey' => 'ceserver',
            'type' => 'rootnode'
              );

            foreach ($line_arr['devices'] as $key => $value) {
                if ($key % 2 !== 0) { //if even
                    continue;
                }

                if (preg_match('/^'.$filterstring.'.*$/', $value[$filterby])) {
                    $devices[] = array(
                    'ip' => $value['ip'],
                    'name' => $value['name'],
                    'networkinterface' => $value['network interface'],
                    'fingureprintkey' => $key+1,
                    'type' => 'device'
                    );
                }
            }

            return $devices;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return $devices;
        }
    }

    // get device fingure print details from white_list_report.json file
    public function get_fingerprint_by_ip($ip)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $fingerprints = array();
        try {
            $whitelist_json_file = new File(self::WHITE_LIST_JSON_FILE);
            $line_arr = $whitelist_json_file->read_json();

            foreach ($line_arr['devices'] as $key => $value) {
                if ($value['ip'] == $ip) {
                    $fingerprints = $line_arr['devices'][$key+1]['fingerprints'];
                    break;
                }
            }

            return $fingerprints;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return $fingerprints;
        }
    }

    ////////////
    public function make_analysis()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();
            $o = 0;
            $retval = $shell->execute("/usr/bin/jmapper ", "/var/lib/jparser ", true, 'log');

            if ($retval === 0) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }
    ////////////
    public function make_report()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();
            $d = "d";
            $o = 0;
            $retval = $shell->execute("/usr/bin/jreport", "/var/lib/jparser ", true, 'log');

            if ($retval === 0) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }
    ////////////

    public function generate_group_rules()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jgenerator ", self::RULES_FOLDER, true, 'log');

            if ($retval === 0) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }

    ////////////

    public function stop_whitelisting()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();
            $d = "d";
            $o = 0;
            $retval = $shell->execute("/usr/bin/stop_whitelisting", " ", true, 'log');

            if ($retval === 0) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }

    ////////////
    public function export_network_map()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/sbin/export-network-map", " ", true, 'log');

            if ($retval === 0) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }

    // find whitelisting status
    // return array whith status running/stopped
    public function whitelisting_running_status()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $white_list_json_file = new File(self::WHITE_LIST_JSON_FILE);
            $white_list_data = new File(self::WHITE_LIST_DATA);

            if (!$white_list_json_file->exists() || !$white_list_data->exists()) {
                return array('status'=>'stopped', 'code'=>1, 'msg'=>'Successfully retrieved status.');
            }
            return array('status'=>'running', 'code'=>1, 'msg'=>'Successfully retrieved status.');
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return array('status'=>'stopped', 'code'=>-1, 'msg'=>'Failed to get status.');
        }
    }

    // Get groups detail
    // return array
    public function get_group_list()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);

            if (!$groups_json_file->exists()) {
                $groups_json_file->create('root', 'root', '766');
                $default_group["groups"][] = array(
                        'name'=>"default",
                        'policy'=>"default",
                        'devices'=>array()
                    );
                $groups_json_file->write_json($default_group);
            }

            $data = $groups_json_file->read_json();
            return array('groups_list'=>$data, 'code'=>1, 'msg'=>'Successfully retrieved groups.');
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return array('groups_list'=>array(), 'code'=>-1, 'msg'=>'Failed to retriev groups.');
        }
    }

    // Get Group details
    // return array
    public function get_group_details($group_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $data = $this->get_group_list();

        return $data['groups_list']['groups'][$group_no];
    }

    // Add Group
    // return boolean TRUE/FALSE
    public function add_new_group($group_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);

            if ($groups_json_file->exists()) {
                $data = $groups_json_file->read_json();
            }

            $data['groups'][]= array(
                "name"=>$group_name,
                "policy"=>"default",
                "devices"=>array()
                  );

            $groups_json_file->delete();
            $groups_json_file->create('root', 'root', '766');
            $groups_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Update Group
    // return boolean TRUE/FALSE
    public function update_group_name($group_name, $group_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);
            if (!$groups_json_file->exists()) {
                return false;
            }

            $data = $groups_json_file->read_json();

            foreach ($data['groups'] as $key => $group_details) {
                if ($key == $group_no) {
                    $group_details['name'] = $group_name;
                }
        
                $data['groups'][$key] = $group_details;
            }
        
            $groups_json_file->delete();
            $groups_json_file->create('root', 'root', '766');
            $groups_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Assign policy to Group
    // return boolean TRUE/FALSE
    public function update_group_policy($policy_name, $group_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);
            if (!$groups_json_file->exists()) {
                return false;
            }

            $data = $groups_json_file->read_json();

            foreach ($data['groups'] as $key => $group_details) {
                if ($key == $group_no) {
                    $group_details['policy'] = $policy_name;
                }
        
                $data['groups'][$key] = $group_details;
            }
        
            $groups_json_file->delete();
            $groups_json_file->create('root', 'root', '766');
            $groups_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Delete Group
    // return boolean TRUE/FALSE
    public function delete_group($group_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            if ($group_no == 0) {
                return false;
            }
        
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);
            if (!$groups_json_file->exists()) {
                return false;
            }

            $data = $groups_json_file->read_json();
        
            if (isset($data['groups'][$group_no])) {
                unset($data['groups'][$group_no]);
            }

            $groups_json_file->delete();
            $groups_json_file->create('root', 'root', '766');
            $groups_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // assign group to devices
    public function add_members_to_group($selected_devices, $group_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
        // get devices from groups.json file
            $ip_with_grpname = $new_list = array(); // already assigned ip with group name
            $grps_list = $this->get_group_list();
            foreach ($grps_list['groups_list']['groups'] as $key => $grp) {
                foreach ($grp['devices'] as $details) {
                    if ($group_name == $grp['name']) { // set default group for other devices in selected group.
                        $grp['name'] = 'default';
                    }

                    $ip_with_grpname[$details['ip']] = $grp['name'];
                }
            }

            $new_list = array_merge($ip_with_grpname, $selected_devices);

            ////////////////////////////////////////////////////////////////// TODO
            // get all connected devices list
            $all_conn_devices = $this->get_all_wa_devices();
            $updated_grp_devices = array();
            foreach ($all_conn_devices as $ip => $ip_details) {
                if (isset($new_list[$ip])) {
                    $updated_grp_devices[$new_list[$ip]][] = array('name'=>$ip_details['name'],'ip'=>$ip);
                } else {
                    $updated_grp_devices['default'][] = array('name'=>$ip_details['name'],'ip'=>$ip);
                }
            }

            ///////////////////////////////////////////////////////////////////
            $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);
            if (!$groups_json_file->exists()) {
                return false;
            }
        
            $data = $groups_json_file->read_json();
            foreach ($data['groups'] as $key => $group_details) {
                if (isset($updated_grp_devices[$group_details['name']])) {
                    $group_details['devices'] = $updated_grp_devices[$group_details['name']];
                } else {
                    $group_details['devices'] = array();
                }
                $data['groups'][$key] = $group_details;
            }
        
            $groups_json_file->delete();
            $groups_json_file->create('root', 'root', '766');
            $groups_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Get Policies list
    // return array
    public function get_policy_list()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $policies_json_file = new File(self::WHITE_LIST_POLICIES_JSON_FILE);

            if (!$policies_json_file->exists()) {
                $policies_json_file->create('root', 'root', '644');
                $default_policy["policies"][] = array(
                        'name'=>"default",
                        'categories'=>array()
                    );
                $policies_json_file->write_json($default_policy);
            }

            $data = $policies_json_file->read_json();
            return array('policies_list'=>$data, 'code'=>1, 'msg'=>'Successfully retrieved policies.');
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return array('policies_list'=>array(), 'code'=>-1, 'msg'=>'Failed to retriev policies.');
        }
    }

    // Get Policy details
    // return array
    public function get_policy_details($policy_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $data = $this->get_policy_list();

        return $data['policies_list']['policies'][$policy_no];
    }

    // Add New Policy
    // return boolean TRUE/FALSE
    public function add_new_policy($policy_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $policies_json_file = new File(self::WHITE_LIST_POLICIES_JSON_FILE);

            if ($policies_json_file->exists()) {
                $data = $policies_json_file->read_json();
            }

            $data['policies'][]= array(
                "name"=>$policy_name,
                "categories"=>array()
                  );

            $policies_json_file->delete();
            $policies_json_file->create('root', 'root', '644');
            $policies_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Update Policy name
    // return boolean TRUE/FALSE
    public function update_policy_name($policy_name, $policy_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $policies_json_file = new File(self::WHITE_LIST_POLICIES_JSON_FILE);
            if (!$policies_json_file->exists()) {
                return false;
            }

            $data = $policies_json_file->read_json();
            $policy_old_name = '';
            foreach ($data['policies'] as $key => $policy_details) {
                if ($key == $policy_no) {
                    $policy_old_name = $policy_details['name'];
                    $policy_details['name'] = $policy_name;
                }
                $data['policies'][$key] = $policy_details;
            }
        
            $policies_json_file->delete();
            $policies_json_file->create('root', 'root', '644');
            $policies_json_file->write_json($data);

            // Update policy name in groups if assigned
            $groups = $this->get_group_list();
            foreach ($groups['groups_list']['groups'] as $grp_no => $grp_detail) {
                if ($grp_detail['policy'] == $policy_old_name) {
                    $status = $this->update_group_policy($policy_name, $grp_no);
                }
            }
            ///////////////////////////////////////////

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // Delete Policy
    // return boolean TRUE/FALSE
    public function delete_policy($policy_no)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            if ($policy_no == 0) {
                return false;
            }

            $policies_json_file = new File(self::WHITE_LIST_POLICIES_JSON_FILE);
            if (!$policies_json_file->exists()) {
                return false;
            }

            $data = $policies_json_file->read_json();

            if (isset($data['policies'][$policy_no])) {
                unset($data['policies'][$policy_no]);
            }
        
            $policies_json_file->delete();
            $policies_json_file->create('root', 'root', '644');
            $policies_json_file->write_json($data);

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // configure policy
    public function configure_policy($selected_categories, $policy_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
        // get categories (from policies.json) and set default policy to categories who are in selected policy
            $category_with_policy = array(); // array('category name'=>'policy name')
            $grps_list = $this->get_policy_list();
            foreach ($grps_list['policies_list']['policies'] as $key => $policy_details) {
                foreach ($policy_details['categories'] as $key2 => $ctg) {
                    if ($policy_name == $policy_details['name']) { // set default policy devices in selected policy.
                        $policy_details['name'] = 'default';
                    }

                    $category_with_policy[$ctg] = $policy_details['name'];
                }
            }
            ////////////////////////////////////////////////////////////////////

            ////////////////////////////////////////////////////////////////////
            // get all category list from white_list_report.json file and
            // assign categories to policy (new category exist in default policy)
            $new_selected_categories = $new_list = array();
            $all_conn_categories = $this->get_all_wa_categories();
            foreach ($selected_categories as $ctg_no => $ctg_state) {
                $new_selected_categories[$all_conn_categories[$ctg_no]['category']] = $policy_name;
            }
        
            // change policy for selected categories
            $new_list = array_merge($category_with_policy, $new_selected_categories);

            $updated_policy_category = array();
            foreach ($all_conn_categories as $category_no => $category_details) {
                if (isset($new_list[$category_details['category']])) {
                    $updated_policy_category[$new_list[$category_details['category']]][] = $category_details['category'];
                } else {
                    $updated_policy_category['default'][] = $category_details['category'];
                }
            }
            ///////////////////////////////////////////////////////////////////

            ///////////////////////////////////////////////////////////////////
            // dump data in policies.json file
            $data['policies'] = array();
            $policies_json_file = new File(self::WHITE_LIST_POLICIES_JSON_FILE);
            if ($policies_json_file->exists()) {
                $data = $policies_json_file->read_json();
            }

            foreach ($data['policies'] as $key => $policy_detail) {
                if (isset($updated_policy_category[$policy_detail['name']])) {
                    $policy_detail['categories'] = $updated_policy_category[$policy_detail['name']];
                } else {
                    $policy_detail['categories'] = array();
                }
                $data['policies'][$key] = $policy_detail;
            }

            $policies_json_file->delete();
            $policies_json_file->create('root', 'root', '644');
            $policies_json_file->write_json($data);
            //////////////////////////////////////////////////////////////////

            return true;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return false;
        }
    }

    // return all connect devices with whitelisting
    public function get_all_wa_devices()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $devices = array();
        try {
            $whitelist_json_file = new File(self::WHITE_LIST_JSON_FILE);
            $content = $whitelist_json_file->read_json();

            foreach ($content['devices'] as $key => $details) {
                if ($key % 2 !== 0) { //if even
                    continue;
                }

                $devices[$details['ip']] = $details;
            }
            return $devices;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return $devices;
        }
    }

    // return all category list
    public function get_all_wa_categories()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $categories = array();
        try {
            $whitelist_json_file = new File(self::WHITE_LIST_CATEGORIES_FILE, true);
            $content = $whitelist_json_file->get_contents_as_array();

            foreach ($content as $line) {
                $line_arr = explode(',', $line);
                if (!isset($line_arr[1])) {
                    continue;
                }

                $categories[$line_arr[0]] = array(
                        'category'=>$line_arr[1],
                        'description'=>$line_arr[2]
                    );
            }
            return $categories;
        } catch (Engine_Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            return $categories;
        }
    }

    /**
     * Validates policy name.
     *
     * @param string $name policy name
     *
     * @return string error message if policy name is invalid
     */

    public function validate_policy_name($policy_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $policy_name)) {
            return 'Invalid policy name. Please enter valid policy name.';
        }

        $all_policies = $this->get_policy_list();
        foreach ($all_policies['policies_list']['policies'] as $key => $policy) {
            if ($policy['name'] == $policy_name) {
                return 'Policy already exist. Please enter valid policy name.';
            }
        }
    }

    /**
     * Validates group name.
     *
     * @param string $group group name
     *
     * @return string error message if group name is invalid
     */

    public function validate_group($group_name)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $group_name)) {
            return 'Invalid group name. Please enter valid group name.';
        }

        $all_groups = $this->get_group_list();
        foreach ($all_groups['groups_list']['groups'] as $key => $group) {
            if ($group['name'] == $group_name) {
                return 'Group already exist. Please enter valid group name.';
            }
        }
    }

    ///////////////////////////////////////////////
    public function update_alerts_with_elastic($timerange = 'last24hours')
    {
        crystaleye_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        // Load libraries
        //---------------

        #        $this->lang->load('voip_monitor');
        #        $this->load->library('voip_monitor/Voip_Monitor');

        #        $this->update_ai_with_elastic('1 hours');

        // get scan alerts
        try {
            // get settime interval value
            $setTimeRange = 'all';//$this->idsipsalerts->engine_time_interval_to_evebox_time_interval($timerange);

            // initialize curl
            $port = "9200";
            $host = "localhost:".$port;
            $arr = array();
            $arr["size"] = 10000;
            $arr["query"]["match"]["event_type"] = "alert";
            $data_json = json_encode($arr);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $host."/_search?pretty");
            curl_setopt($curl, CURLOPT_PORT, $port);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
            $result = curl_exec($curl);

            if (curl_errno($curl)) {
                crystaleye_log('voip_monitor', 'Exception: '.$e->getMessage());
                echo json_encode(array('data'=>array(), 'code' => -1, 'msg' => 'Failed to load inbox alerts.'));
                return;
            }
            curl_close($curl);

            $records = json_decode($result);
            $data['data'] = array();
            $str = '';


            foreach ($records->hits->hits as $key => $record) {
                $event_id = $record->_id ? $record->_id : '';
                if (!isset($event_id) || empty($event_id)) {
                    continue;
                }
                $signature = $record->_source->alert->signature ? $record->_source->alert->signature : '';
                if (stristr($signature, self::AWL_ALERT_TEXT) === false) {
                    continue;
                }


                $timestamp = $record->_source->timestamp ? $record->_source->timestamp : '';
                $myDateTime1 = new DateTime($timestamp);

                $timeran = $this->elastic_time_interval($timerange);

                if ($myDateTime1 < $timeran) {
                    continue;
                }
                $source_ip = $record->_source->src_ip ? $record->_source->src_ip : '';
                $source_port = $record->_source->src_port ? $record->_source->src_port : '';
                $flow_id = $record->_source->flow_id ? $record->_source->flow_id : '';
                $in_iface = $record->_source->in_iface ? $record->_source->in_iface : '';
                $event_type = $record->_source->event_type ? $record->_source->event_type : '';
                $dest_ip = $record->_source->dest_ip ? $record->_source->dest_ip : '';
                $dest_port = $record->_source->dest_port ? $record->_source->dest_port : '';
                $proto = $record->_source->proto ? $record->_source->proto : '';
                $severity = $record->_source->alert->severity ? $record->_source->alert->severity : '';
                $signature = $record->_source->alert->signature ? $record->_source->alert->signature : '';
                $signature_id = $record->_source->alert->signature_id ? $record->_source->alert->signature_id : '';
                $category = $record->_source->alert->category ? $record->_source->alert->category : '';
                $country =  $record->_source->geoip->country_name ? $record->_source->geoip->country_name : '';
                $action = $record->_source->alert->action ? $record->_source->alert->action : '';





                $myDateTime = new DateTime($timestamp);
                $actual_time = $myDateTime->format('Y-m-d H:i:s');

                $data['data'][] = array(
                    $actual_time,
                    $source_ip,
                    $dest_ip,
                    $country,
                    $signature,
                    $category,
                    $signature_id
//                    $event_id
                );
            }

            $data['header'] = array('timestamp', 'src_ip', 'dest_ip', 'country', 'signature', 'category', 'severity', 'event_id');
            $data['code'] = 1;
            $data['msg'] = 'Successfully retrieve inbox alerts.';

            echo json_encode($data);

            return ;
        } catch (Exception $e) {
            crystaleye_log('voip_monitor', 'Exception: '.$e->getMessage());
            echo json_encode(array('data'=>array(), 'code' => -1, 'msg' => 'Failed to load voip_monitor alerts.'));
            return;
        }
    }
    ///////////////////////////////////
    public function engine_time_interval_to_evebox_time_interval($engine_time_interval)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        if (empty($engine_time_interval)) {
            $evebox_time_interval = '';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_HOUR) {
            $evebox_time_interval = 1*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_HOUR) {
            $evebox_time_interval = 3*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_12_HOUR) {
            $evebox_time_interval = 12*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_24_HOUR) {
            $evebox_time_interval = 24*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_DAYS) {
            $evebox_time_interval = 3*24*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_7_DAYS) {
            $evebox_time_interval = 7*24*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_30_DAYS) {
            $evebox_time_interval = 30*24*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_MONTHS) {
            $evebox_time_interval = 3*30*24*3600 . 's';
        } elseif ($engine_time_interval === Report_Engine::RANGE_ALL) {
            $evebox_time_interval = '';
        } else {
            $evebox_time_interval = '';
        }

        return $evebox_time_interval;
    }

    public function elastic_time_interval($engine_time_interval)
    {
        crystaleye_profile(__METHOD__, __LINE__);
        $timeran = new DateTime();
        if (empty($engine_time_interval)) {
            date_sub($timeran, date_interval_create_from_date_string('100 days'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_HOUR) {
            date_sub($timeran, date_interval_create_from_date_string('1 hours'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_HOUR) {
            date_sub($timeran, date_interval_create_from_date_string('3 hours'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_12_HOUR) {
            date_sub($timeran, date_interval_create_from_date_string('12 hours'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_24_HOUR) {
            date_sub($timeran, date_interval_create_from_date_string('24 hours'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_DAYS) {
            date_sub($timeran, date_interval_create_from_date_string('3 days'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_7_DAYS) {
            date_sub($timeran, date_interval_create_from_date_string('7 days'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_30_DAYS) {
            date_sub($timeran, date_interval_create_from_date_string('30 days'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_LAST_3_MONTHS) {
            date_sub($timeran, date_interval_create_from_date_string('90 days'));
        } elseif ($engine_time_interval === Report_Engine::RANGE_ALL) {
            date_sub($timeran, date_interval_create_from_date_string('100 days'));
        } else {
            date_sub($timeran, date_interval_create_from_date_string('100 days'));
        }

        return $timeran;
    }
    public function update_fingerprints()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jupdater ", '', true, 'log');

            if ($retval >= 1) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }
    ///////
    public function mark()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jinteger i ", '', true, 'log');

            if ($retval >= 1) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }
    ///////
    public function unmark()
    {
        crystaleye_profile(__METHOD__, __LINE__);
        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jinteger u ", '', true, 'log');

            if ($retval >= 1) {
                return true;
            } else {
                $output = false;
            }
            return $output;
        } catch (Engine_Exception $e) {
            return false;
        }
    }

    public function resolve_fingerprint($fingerprint_hash)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $z = $this->get_res($fingerprint_hash);
            $status_msg = array(
                    'status' => true,
                    'message' => 'Success:'.$z,   //'Fingerprint cannot be resolved. Connection buzy',
                );

            return $status_msg;
        } catch (Engine_Exception $e) {
            return array('status' => false, 'message' => 'Exception: action Failed. Please try again.');
        }
    }

    public function tag($fingerprint_hash)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $status_msg = array(
                    'status' => true,
                    'message' => 'Fingerprint taged to investigation.',
                );

            return $status_msg;
        } catch (Engine_Exception $e) {
            return array('status' => false, 'message' => 'Exception: action Failed. Please try again.');
        }
    }

    public function block($fingerprint_hash, $device_ip)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jmaker r ".$fingerprint_hash." ".$device_ip, '', true, 'log');
            if ($retval == 2) {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Rule for this fingerprint and device already exists.',
                );

                return $status_msg;
            }
            if ($retval >= 1) {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Fingerprint blocked. Rule generated.',
                );

                return $status_msg;
            } else {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Fingerprint blocking failed.',
                );

                return $status_msg;
            }
        } catch (Engine_Exception $e) {
            return array('status' => false, 'message' => 'Exception: action Failed. Please try again.');
        }
    }

    public function allow($fingerprint_hash, $device_ip)
    {
        crystaleye_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();

            $retval = $shell->execute("/usr/bin/jmaker p ".$fingerprint_hash." ".$device_ip, '', true, 'log');
            if ($retval == 2) {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Rule for this fingerprint and device already exists',
                );

                return $status_msg;
            }
            if ($retval == 1) {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Fingerprint allowed. Rule generated.',
                );

                return $status_msg;
            } else {
                $status_msg = array(
                    'status' => true,
                    'message' => 'Fingerprint blocking failed.',
                );

                return $status_msg;
            }
        } catch (Engine_Exception $e) {
            return array('status' => false, 'message' => 'Exception: action Failed. Please try again.');
        }
    }
    
    ////////////////////////////
    public function get_res($fingerprint)
    {
        $url = 'https://ja3er.com/search/'.$fingerprint;
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla');
            $html = curl_exec($curl);
            curl_close($curl);
        }
        return $html;
    }
    ///////////////////////////////////////////
    public function sync_logic()
    {
        $groups_json_file = new File(self::WHITE_LIST_GROUPS_JSON_FILE);
        if (!$groups_json_file->exists()) {
            crystaleye_log('whitelisting', 'Exception: error openning json groups file');
            return;
        }
        $json_data = $groups_json_file->read_json();
        $json_output = $json_data;

        $white_list_devices_txt_file = new File(self::WHITE_LIST_DEVICES_TXT_FILE);
        if ($white_list_devices_txt_file->exists()) {
            $lines = $white_list_devices_txt_file->get_contents_as_array();
            foreach ($lines as $line) {
                $data = str_getcsv($line, ";");
                $flag = false;

                foreach ($json_data->groups as $ar) {
                    foreach ($ar->devices as $dev) {
                        if ($dev->ip == $data[1]) {
                            $flag = true;
                            break;
                        }
                    }
                }
                if ($flag == false) {
                    /////////////////////////////////
                    foreach ($json_output->groups as $ar) {
                        if ($ar->name == "default") {
                            array_push($ar->devices, (object)[
                                'ip' => $data[1],
                                'name' => $data[2],
                            ]);
                            break;
                        }
                    }
                }
            }
        }

        $groups_json_file->delete();
        $groups_json_file->create('root', 'root', '766');
        $groups_json_file->write_json($json_output);
    }

    /////////////////////////////
    public function make_fingerprints_update()
    {
        crystaleye_profile(__METHOD__, __LINE__);

        $msg = 'AWL: ';

        try {
            $ret = $this->update_fingerprints();

            if ($ret) {
                $msg = $msg .'Fingerprints are up-to-date. Checking rules. ';

                $ret1 = $this->generate_group_rules();

                if ($ret1) {
                    $msg = $msg . 'Rules integrity was checked and regeneration was done. ';
                } else {
                    $msg = $msg . 'Error: Rules integrity checking and generation failed.';
                }
                crystaleye_log('whitelisting', 'Info: '. $msg);
            } else {
                $msg = $msg . 'Cannot check and update fingerprints. ';
                crystaleye_log('whitelisting', 'Info: ' . $msg);
            }
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);

            return;
        }
    }
    ////////////////////////////////////////
    public function reload_suricata()
    {
        $suricata = new Suricata();
        $suricata->restart();
    }
}
