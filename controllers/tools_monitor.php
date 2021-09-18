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
 * Tools Monitor controller.
 *
 * @category   apps
 * @package    whitelisting
 * @subpackage controllers
 * @author     RedPiranha <support@redpiranha.net>
 * @copyright  2011 RedPiranha
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.redpiranha.net/docs/developer/apps/whitelisting/tools_monitor
*/

class Tools_Monitor extends CrystalEye_Controller
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
        $this->load->library('home_reports/Report_Driver');

        try {
            // load view data
            $data['range'] = 'last24hours';
            $data['ranges'] = $this->report_driver->get_date_ranges();
            $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;

        $this->page->view_form('whitelisting/tools_monitor', $data, lang('whitelisting_tools'), $options);
    }

    // return json data
    public function get_monitor_report($timerange = 'last7')
    {
        // Load libraries
        //---------------

        $this->lang->load('whitelisting');
        $this->load->library('whitelisting/Whitelisting');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // get scan alerts
        try {
       // TODO make it dynamic
            /*   $data['data'][] = array('2019-08-20','10.95.1.4','10.95.1.5','India','shdjc hjd sd','dns');
               $data['data'][] = array('2019-08-30','10.95.1.6','10.95.1.8','Australia','shddfv jc hjd sd','http');

               $data['header'] = array('timestamp', 'src_ip', 'dest_ip', 'country', 'signature', 'category');
               $data['code'] = 1;
               $data['msg'] = 'Successfully retrieve report.'; */
            $data['data'] = array();
            //$f = $this->voip_monitor->update_ai_with_elastic($timerange);
            $details = $this->whitelisting->update_alerts_with_elastic($timerange);

            foreach ($details['alerts'] as $key => $row) {
                $row_details = json_decode($row);
                if ($row_details->event_type != 'alert') {
                    continue;
                }

                $timestamp = $row_details->timestamp ? $row_details->timestamp : '';
                $src_ip = $row_details->src_ip ? $row_details->src_ip : '';
                $dest_ip = $row_details->dest_ip ? $row_details->dest_ip : '';
                $country = $row_details->country ? $row_details->country : '';
                $signature = $row_details->alert->signature ? $row_details->alert->signature : '';
                $category = $row_details->alert->category ? $row_details->alert->category : '';
                $signature_id = $row_details->signature_id ? $row_details->signature_id : '';
//          $rule_type = $row_details->rule_type ? $row_details->rule_type : '';

                $data['data'][] = array(
                $timestamp,
                $src_ip,
                $dest_ip,
                $country,
                $signature,
                $category,
                $signature_id
//                $rule_type
                );
            }

            $data['header'] = array('timestamp', 'src_ip', 'dest_ip', 'country', 'signature', 'category', 'signature_id');
            $data['code'] = 1;
            $data['msg'] = 'Successfully retrieve alerts.';

            //	   echo json_encode($data);
        } catch (Exception $e) {
            crystaleye_log('whitelisting', 'Exception: '.$e);
            echo json_encode(array('data'=>array(), 'code' => -1, 'msg' => 'Failed to load report.'));
            return;
        }
    }
}
