<?php

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

class Individual_Rules extends CrystalEye_Controller
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
            $data['rules'] = $this->whitelisting->get_rules('individual');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;

        $this->page->view_form('whitelisting/individual_rules', $data, lang('whitelisting_individual_rules'), $options);
    }

    // Enable/Disable rule
    public function toggle_status($sid)
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle delete
        //--------------
        
        try {
            $ret = $this->whitelisting->toggle_whitelist_rule($sid, 'individual');
            
            if ($ret) {
                $msg_type = 'info';
                $msg = 'Rule ('.$sid.') status changed successfully.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to change rule ('.$sid.') status.';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting/individual_rules');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    // Convert rules to drop, alert, reject, pass
    public function edit_rule($sid, $action)
    {
        // Load libraries
        //---------------
        
        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');

        // Handle edit
        //------------
        
        try {
            $ret = $this->whitelisting->edit_whitelist_rule($sid, $action, 'individual');
            if ($ret) {
                $msg_type = 'info';
                $msg = 'Rule ('.$sid.') status changed successfully.';
            } else {
                $msg_type = 'warning';
                $msg = 'Error: Fail to change rule ('.$sid.') status.';
            }

            $this->page->set_message($msg, $msg_type);
            redirect('/whitelisting/individual_rules');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
