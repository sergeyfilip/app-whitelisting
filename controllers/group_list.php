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
 * Group List controller.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage controllers
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/group_list
*/

class Group_List extends CrystalEye_Controller
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

        try {
            // load view data
            $data = $this->whitelisting->get_group_list();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/group/group_list', $data, lang('whitelisting_group_mang'), $options);
    }

    /**
     *  Add/Edit group.
     *
     * @return view
     */

    public function add_edit_group($form_type, $group_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('group_name', 'whitelisting/Whitelisting', 'validate_group', true);
        $form_ok = $this->form_validation->run();

        try {
            // Handle form submit
            //-------------------

            if ($this->input->post('submit') && $form_ok) {
                if ($form_type === 'edit') {
                    $status = $this->whitelisting->update_group_name($this->input->post('group_name'), $group_no);

                    $msg = 'Failed to updated group.';
                    if ($status == true) {
                        $msg = 'Group updated successfully.';
                    }
                } else {
                    $status = $this->whitelisting->add_new_group($this->input->post('group_name'));

                    $msg = 'Failed to add group.';
                    if ($status == true) {
                        $msg = 'Group added successfully.';
                    }
                }

                $box_type = 'warning';
                if ($status == true) {
                    $box_type = 'info';
                }

                $this->page->set_message($msg, $box_type);
                redirect('/whitelisting/group_list');
            }

            // load view data
            $data = array();
            $data['form_type'] = $form_type;
            if ($form_type == 'edit') {
                if ($group_no == 0) { // avoid default
                    redirect('/whitelisting/group_list');
                }
                $data['group_no'] = $group_no;
                $data['group'] = $this->whitelisting->get_group_details($group_no);
            }
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $this->whitelisting->sync_logic();
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/group/add_edit_group', $data, lang('whitelisting_group_mang'), $options);
    }

    /**
     * Confirm to Delete Group view.
     *
     * @return view
     */

    public function delete_group($group_no, $group_name)
    {
        // Load libraries
        //---------------

        $this->lang->load('whitelisting');

        // Show confirm
        //-------------
        $confirm_uri = '/app/whitelisting/group_list/destroy_group/' . $group_no;
        $cancel_uri = '/app/whitelisting/group_list';
        $items = array($group_name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys group.
     *
     * @param int $group_no group no
     *
     * @return view
     */
    public function destroy_group($group_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        try {
            // load view data
        
            $status = $this->whitelisting->delete_group($group_no);

            $msg = 'Failed to deleted group.';
            $box_type = 'warning';
            if ($status == true) {
                $box_type = 'info';
                $msg = 'Group deleted successfully.';
            }
            $this->whitelisting->sync_logic();
            $this->page->set_message($msg, $box_type);
            redirect('/whitelisting/group_list');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
    /**
     *  Show and update members in a group.
     *
     * @return view
     */

    public function group_members($group_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        try {
            // Handle form submit
            //-------------------

            if ($this->input->post('submit')) {
                $group_name = $this->input->post('group_name');
                $devices = $this->input->post('devices');
                $selected_devices = array();
                foreach ($devices as $ip_str => $state) {
                    $selected_ip = str_replace('_', '.', $ip_str);
                    $selected_devices[$selected_ip] = $group_name;
                }

                $status = $this->whitelisting->add_members_to_group($selected_devices, $group_name);

                $msg = 'Failed to update member list.';
                $box_type = 'warning';
                if ($status == true) {
                    $box_type = 'info';
                    $msg = 'Member list updated successfully.';
                }
                $this->page->set_message($msg, $box_type);
                redirect('/whitelisting/group_list');
            }

            // load view data
            $data = array();
            $members = array();
            $data['group_no'] = $group_no;
            $group_detail = $this->whitelisting->get_group_details($group_no);
            $data['group_name'] = $group_detail['name'];
            foreach ($group_detail['devices'] as $details) {
                $members[$details['ip']] = $group_detail['name'];
            }
        
            $data['assigned_members'] = $members;
            $data['devices'] = $this->whitelisting->get_all_wa_devices();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $this->whitelisting->sync_logic();
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/group/group_members', $data, lang('whitelisting_group_mang'), $options);
    }
}
