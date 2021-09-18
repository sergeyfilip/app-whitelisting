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
 * Rules controller.
 *
 * @category   apps
 * @package    rules
 * @subpackage controllers
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/rules/
 */

class Rules extends CrystalEye_Controller
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
            $data['rules'] = $this->whitelisting->get_rules();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;

        $this->page->view_form('whitelisting/rules', $data, lang('whitelisting_rules'), $options);
    }

    // get local rule status
    public function toggle_status($sid)
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle delete
        //--------------
        
        try {
            $ret = $this->whitelisting->toggle_whitelist_rule($sid);
            
            if ($ret) {
                $msg_type = 'info';
                $msg = 'Rule ('.$sid.') status changed successfully.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to change rule ('.$sid.') status.';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting/rules');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    // Conver rules to drop, alert, reject, pass
    public function edit_rule($sid, $action)
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle edit
        //------------
        
        try {
            $ret = $this->whitelisting->edit_whitelist_rule($sid, $action);
            if ($ret) {
                $msg_type = 'info';
                $msg = 'Rule ('.$sid.') status changed successfully.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to change rule ('.$sid.') status.';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting/rules');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
