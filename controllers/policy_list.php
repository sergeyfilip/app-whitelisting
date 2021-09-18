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
 * Policy List controller.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage controllers
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/policy_list
 */

class Policy_List extends CrystalEye_Controller
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
            $policies = $this->whitelisting->get_policy_list();
            $groups = $this->whitelisting->get_group_list();
            $data = array_merge($policies, $groups);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/policy/policy_list', $data, lang('whitelisting_policy_mang'), $options);
    }

    /**
     *  Add/Edit Policy.
     *
     * @return view
     */

    public function add_edit_policy($form_type, $policy_no = '')
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('policy_name', 'whitelisting/Whitelisting', 'validate_policy_name', true);
        $form_ok = $this->form_validation->run();

        try {
            // Handle form submit
            //-------------------

            if ($this->input->post('submit') && $form_ok) {
                if ($form_type === 'edit') {
                    $status = $this->whitelisting->update_policy_name($this->input->post('policy_name'), $policy_no);
        
                    $msg = 'Failed to updated policy.';
                    if ($status == true) {
                        $msg = 'Policy updated successfully.';
                    }
                } else {
                    $status = $this->whitelisting->add_new_policy($this->input->post('policy_name'));

                    $msg = 'Failed to add policy.';
                    if ($status == true) {
                        $msg = 'Policy added successfully.';
                    }
                }

                $box_type = 'warning';
                if ($status == true) {
                    $box_type = 'info';
                }

                $this->page->set_message($msg, $box_type);
                redirect('/whitelisting/policy_list');
            }

            // load view data
            $data = array();
            $data['form_type'] = $form_type;
            if ($form_type == 'edit') {
                if ($policy_no == 0) { // avaid default
                    redirect('/whitelisting/policy_list');
                }
                $data['policy_no'] = $policy_no;
                $data['policy'] = $this->whitelisting->get_policy_details($policy_no);
            }
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/policy/add_edit_policy', $data, lang('whitelisting_policy_mang'), $options);
    }

    /**
     * Confirm to Delete policy view.
     *
     * @return view
     */

    public function delete_policy($policy_no, $policy_name)
    {
        // Load libraries
        //---------------

        $this->lang->load('whitelisting');

        // Show confirm
        //-------------
        $confirm_uri = '/app/whitelisting/policy_list/destroy_policy/' . $policy_no;
        $cancel_uri = '/app/whitelisting/policy_list';
        $items = array($policy_name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys policy.
     *
     * @param int $policy_no policy no
     *
     * @return view
     */

    public function destroy_policy($policy_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        try {
            // load view data
            $status = $this->whitelisting->delete_policy($policy_no);

            $msg = 'Failed to deleted policy.';
            $box_type = 'warning';
            if ($status == true) {
                $box_type = 'info';
                $msg = 'Policy deleted successfully.';
            }
            $this->page->set_message($msg, $box_type);
            redirect('/whitelisting/policy_list');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Assign Policy to group.
     *
     * @return view
     */

    public function assign_policy_to_group($group_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Set validation rules
        //---------------------
        $this->form_validation->set_policy('policy_name', '', '', true);
        $form_ok = $this->form_validation->run();

        try {
            // Handle form submit
            //-------------------
            if ($this->input->post('submit') && $form_ok) {
                $group_no = $this->input->post('group_no');
                $policy_name = $this->input->post('policy_name');
                $status = $this->whitelisting->update_group_policy($policy_name, $group_no);

                $msg = 'Failed to assign policy.';
                $box_type = 'warning';
                if ($status == true) {
                    $box_type = 'info';
                    $msg = 'Assign policy to group successfully.';
                }
                $this->page->set_message($msg, $box_type);
                redirect('/whitelisting/policy_list');
            }

            // load view data
            $data = $this->whitelisting->get_policy_list();
            $data['group_no'] = $group_no;
            $data['group'] = $this->whitelisting->get_group_details($group_no);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/policy/assign_policy_to_group_form', $data, lang('whitelisting_policy_mang'), $options);
    }

    /**
     * Configure Policy.
     *
     * @return view
     */

    public function configure_policy($policy_no)
    {
        // Load dependencies
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        try {
            // Handle form submit
            //-------------------

            if ($this->input->post('submit')) {
                $policy_name = $this->input->post('policy_name');
                $selected_categories = $this->input->post('category');

                $status = $this->whitelisting->configure_policy($selected_categories, $policy_name);

                $msg = 'Failed to updated policy.';
                $box_type = 'warning';
                if ($status == true) {
                    $box_type = 'info';
                    $msg = 'Policy updated successfully.';
                }

                $this->page->set_message($msg, $box_type);
                redirect('/whitelisting/policy_list');
            }

            // load view data
            $data = array();
            $members = array();
            $data['policy_no'] = $policy_no;
            $policy_detail = $this->whitelisting->get_policy_details($policy_no);
            $data['policy_name'] = $policy_detail['name'];
            foreach ($policy_detail['categories'] as $key => $ctg) {
                $members[$ctg] = $ctg;
            }
        
            $data['assigned_category'] = $members;
            $data['categories'] = $this->whitelisting->get_all_wa_categories();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------
        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        $this->page->view_form('whitelisting/policy/configure_policy', $data, lang('whitelisting_policy_mang'), $options);
    }
}
