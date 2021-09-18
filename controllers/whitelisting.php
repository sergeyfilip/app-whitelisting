<?php

/**
 * Whitelisting Controller.
 *
 * @category   apps
 * @package    Whitelisting
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 *  Whitelisting controller.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage controllers
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/
 */

class Whitelisting extends CrystalEye_Controller
{
    /**
     *  Whitelisting default controller.
     *
     * @return view
     */

    public function index()
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Load views
        //-----------

        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;

        $data['whitelisting_status'] = $this->get_status();
        $this->page->view_form('whitelisting/fingerprints', $data, lang('whitelisting_fingerprint'), $options);
    }

    // get local rule status
    public function add_fingerprint()
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle delete
        //--------------
        
        try {
            if ($this->input->post('submit_fingerprint')) {
                $ja3hash = $this->input->post('ja3hash');
                $category = $this->input->post('category');
                $description = $this->input->post('description');

                $ret = $this->whitelisting->add_fingerprint_rule($ja3hash, $category, $description);
        
                if ($ret) {
                    $msg_type = 'info';
                    $msg = 'Fingerprint rule added successfully.';
                } else {
                    $msg_type = 'warning';
                    $msg = 'Error: Fail to add Fingerprint rule.';
                }

                $this->page->set_message($msg, $msg_type);
                redirect('/whitelisting');
            }
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        $this->page->view_form('whitelisting/add_fingerprints', $data, lang('whitelisting_add_fingerprint'));
    }

    // confirm delete rule
    public function delete($rule, $ja3hash)
    {
        $confirm_uri = '/app/whitelisting/destroy/' . $rule;
        $cancel_uri = '/app/whitelisting';
        $items = array($ja3hash);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    // permanently delete fingerprint rule
    public function destroy($rule)
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle delete
        //--------------
        
        try {
            $ret = $this->whitelisting->remove_fingerprint_rule($rule);
        
            if ($ret) {
                $msg_type = 'info';
                $msg = 'Fingerprint rule deleted successfully.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to delete Fingerprint rule.';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    // get devices details for GW graph
    public function get_gw_devices($filterby = 'ip', $filterstring = '')
    {
        // Load libraries
        //---------------

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Fri, 01 Jan 2010 05:00:00 GMT');
        header('Content-type: application/json');

        try {
            $this->lang->load('whitelisting');
            $this->load->library('whitelisting/Whitelisting');

            $devices = $this->whitelisting->get_devices_list($filterby, $filterstring);
            echo json_encode($devices);
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            echo json_encode($devices);
        }
    }

    // get device fingereprint details
    public function get_devices_fingerprint($ip)
    {
        // Load libraries
        //---------------
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Fri, 01 Jan 2010 05:00:00 GMT');
        header('Content-type: application/json');

        try {
            $this->lang->load('whitelisting');
            $this->load->library('whitelisting/Whitelisting');

            // Load view data
            //---------------
            $fingerprints = $this->whitelisting->get_fingerprint_by_ip($ip);
            echo json_encode($fingerprints);
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            echo json_encode($fingerprints);
        }
    }

    public function whitelist_all_rules()
    {
        // Load libraries
        //---------------
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Load view data
        //---------------
        try {
            $ret = $this->whitelisting->generate_group_rules();

            if ($ret) {
                $msg_type = 'info';
                $msg = 'Rules for ALL devices  and groups added';

                $this->whitelisting->mark();
                $this->whitelisting->reload_suricata();

                $msg = $msg . 'AWL configuration included.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to add rules for ALL devices and groups';
            }
            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting');
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            $this->page->view_exception($e);
            return;
        }
    }
    // All device to Whitelist
    public function start_whitelisting()
    {
        // Load libraries
        //---------------
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');
        // $filename = '/etc/suricata/rules/white_list.rules';
        // Load view data
        //---------------


        try {
            $ret = $this->whitelisting->export_network_map();

            if ($ret) {
                $msg_type = 'info';
                $msg = 'Network map updated. ';
            } else {
                $msg_type = 'warning';
                $msg = 'Cannot update network map. ';
            }

            crystaleye_log('whitelisting', 'Info: ' . $msg);
            //  redirect('/whitelisting');


            $ret = $this->whitelisting->make_analysis();


            if ($ret) {
                $msg_type = 'info';
                $msg = $msg . 'Devices analysis was made. ';
                $ret1 = $this->whitelisting->make_report();

                if ($ret1) {
                    $msg = $msg . 'Network report done. ';
                } else {
                    ;
                    $msg = $msg . 'Error: Network report failed.';
                }
            } else {
                $msg_type = 'warning';
                $msg = $msg.'Error: Fail to create analysis for fingerprinting. ';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting');
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            $this->page->view_exception($e);
            return;
        }
    }
    // All device to Whitelist
    public function stop_whitelisting()
    {
        // Load libraries
        //---------------
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Load view data
        //---------------
        try {
            $ret = $this->whitelisting->stop_whitelisting();

            if ($ret) {
                $msg_type = 'info';
                $msg = 'All whitelisting services stopped. Files cleared';

                $this->whitelisting->unmark();
                $this->whitelisting->reload_suricata();

                $msg = $msg . 'Configuration cleared.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to stop fingerprinting';
            }




            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting');
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            $this->page->view_exception($e);
            return;
        }
    }

    // find whiting listing status
    // return array whith status
    public function get_status()
    {
        // Load libraries
        //---------------
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Load view data
        //---------------
        try {
            $status = $this->whitelisting->whitelisting_running_status();
            return $status;
        } catch (Exception $e) {
            return array('code'=>-1,'msg'=>'Failed to get status.');
        }
    }

    public function make_update()
    {
        // Load libraries
        //---------------
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');
        // $filename = '/etc/suricata/rules/white_list.rules';
        // Load view data
        //---------------

        try {
            $ret = $this->whitelisting->update_fingerprints();

            if ($ret) {
                $msg_type = 'info';
                $msg = 'Fingerprints are up-to-date. ';
            } else {
                $msg_type = 'warning';
                $msg = 'Cannot check and update fingerprints. ';
            }

            crystaleye_log('whitelisting', 'Info: ' . $msg);
            //  redirect('/whitelisting');


            if ($ret) {
                $msg_type = 'info';
                $msg = $msg . 'Check rules. ';
                $ret1 = $this->whitelisting->generate_group_rules();

                if ($ret1) {
                    $msg = $msg . 'Rules integrity was checked and regeneration was done. ';
                } else {
                    ;
                    $msg = $msg . 'Error: Rules integrity checking failed.';
                }
            } else {
                $msg_type = 'warning';
                $msg = $msg.'Error: Fail to create update for fingerprinting. ';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting');
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            $this->page->view_exception($e);
            return;
        }
    }
    
    /* Action on fingerprint
    ** return json
    */
    public function edit()
    {
        // Load libraries
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle form submit
        //-------------------

        $fingerprint_hash = $this->input->post('selected_hash');
        if (empty($fingerprint_hash)) {
            $status_msg = array(
                'status' => false,
                'message' => 'Fingerprint Hash value not found. Please try again.',
            );
        }
        $device_ip = $this->input->post('fingerprint_ip');
        if (empty($device_ip)) {
            $status_msg = array(
                'status' => false,
                'message' => 'Device IP not found. Please try again.',
            );
        }

        if ($this->input->post('action_type') =='resolve') {
            //TODO resolve the functionality
            $status_msg = $this->whitelisting->resolve_fingerprint($fingerprint_hash);
        } elseif ($this->input->post('action_type') =='tag_to_investigate') {
            //TODO tag the functionality
            $status_msg = $this->whitelisting->tag($fingerprint_hash);
        } elseif ($this->input->post('action_type') =='block') {
            //TODO block the functionality
            $status_msg = $this->whitelisting->block($fingerprint_hash, $device_ip);
        } elseif ($this->input->post('action_type') =='allow') {
            //TODO allow the functionality
            $status_msg = $this->whitelisting->allow($fingerprint_hash, $device_ip);
        } else {
            $status_msg = array(
                'status' => false,
                'message' => 'Error: Not a valid action.',
            );
        }

        $this->output->set_header('Cache-Control: no-cache, must-revalidate');
        $this->output->set_header("Content-Type: application/json");
        $this->output->set_output(json_encode($status_msg));
    }
}
