<?php

/**
 * whitelisting javascript helper.
 *
 * @category   apps
 * @package    network-map
 * @subpackage javascript
 * @author     RedPiranha <staff@redpiranha.net>
 * @copyright  2012 RedPiranha
 * @license    http://www.redpiranha.net/app_license RedPiranha license
 * @link       http://www.redpiranha.net/support/documentation/crystaleye/network_map/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CRYSTALEYE_BOOTSTRAP') ? getenv('CRYSTALEYE_BOOTSTRAP') : '/usr/crystaleye/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

crystaleye_load_language('whitelisting');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type:application/x-javascript');
?>
var whitelisting_monitor_report_table;

$(document).ready(function() {
  //$('#network_map_selection').insertAfter('#theme-sidebar-container');

  if ($('#gwdevices').length != 0) {
     var filterby = $('#gw_filterby_drp').val();
     var filterstring = $('#sort_gw_devices').val();
     gw_devicesmap_getData(filterby, filterstring);
  }

  $("#gw_filterby_drp").on('change', function() { 
        var filterby = $('#gw_filterby_drp').val();
        var filterstring = $('#sort_gw_devices').val();
        $('#gwdevices > svg').remove();
        gw_devicesmap_getData(filterby, filterstring);
  });   

  $('#sort_gw_devices').keypress(function (e) {
       var enterkey = e.which;
       if(enterkey == 13)  // the enter key code
       {
          var filterby = $('#gw_filterby_drp').val();
          var filterstring = $('#sort_gw_devices').val();
          $('#gwdevices > svg').remove();
          gw_devicesmap_getData(filterby, filterstring);  
       }
  });

  // Monitor Report
  if ($('#tools_monitor_tbl').length != 0)
  {
      // On load table
      var timeRange = $('#timeRange').val();
      show_monitor_report_table(timeRange);

      // On time interval click
      $("#timeRange").on('change', function() { 

            timeRange = $('#timeRange').val();
            $('#tools_monitor_tbl').DataTable().ajax.url("/app/whitelisting/tools_monitor/get_monitor_report/"+timeRange).load();
      });
  }

});


// get devices/group details and load networkmap
///////
function gw_devicesmap_getData(filterby, filterstring)
{ 
    filterstring = encodeURIComponent(filterstring);
    filterby = encodeURIComponent(filterby);
    var n_url = '/app/whitelisting/get_gw_devices/'+filterby+'/'+filterstring;

    $.ajax({
        type: 'GET',
        url: n_url,
        dataType: 'json',
        context: this,
        contentType: 'application/json; charset=utf-8',
        success : function(jsonData) {
            gwonLoad(jsonData);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            
        }

    });
}
// show networkmap devices groups
///////
function show_gwd_map(node_value)
{
    if(node_value !== 'CrystalEyeGateway')
    {
           view_device_fingerprint(node_value);    
    }
}

// add to whitelist
////////
function add_to_whitelistd(node_value)
{
    // node_value = ip
    $.ajax({
      type: 'GET',
      url: '/app/whitelisting/add_to_whitelist_reject/'+node_value,
      dataType: 'json',
      contentType: 'application/json; charset=utf-8',
      success: function(jsonData) {
               document.location.href = '/app/whitelisting';
      },
      error: function() {
        
      }
   }); // ajaz closed

}
////////
function add_to_whitelista(node_value)
{
    // node_value = ip
    $.ajax({
        type: 'GET',
        url: '/app/whitelisting/add_to_whitelist_reject/'+node_value,
        dataType: 'json',
        contentType: 'application/json; charset=utf-8',
        success: function(jsonData) {
            document.location.href = '/app/whitelisting';
        },
        error: function() {

        }
    }); // ajaz closed

}

// show edit/map devices
////////
function view_device_fingerprint(node_value)
{
    // node_value = ip
    $.ajax({
      type: 'GET',
      url: '/app/whitelisting/get_devices_fingerprint/'+node_value,
      dataType: 'json',
      contentType: 'application/json; charset=utf-8',
      success: function(jsonData) {
        var options = new Object();
        options.type = 'info';
        options.id = 'networkmap_action';
        var fingerprint_count = 0;
        //JSON.stringify(jsonData)
        message = '<div class="device-fingerprint-table" style="max-height: 275px;min-height: 100px; overflow-y: auto;"><table class="table responsive table-striped theme-summary-table-large dataTable dtr-inline"><thead><tr><th>Category</th><th>Hash</th><th>Type</th><th>Action</th></tr></thead><tbody>';
        for (var key2 in jsonData) {
            message += '<tr><td>'+jsonData[key2]['category']+'</td><td>'+jsonData[key2]['hash']+'</td><td>'+jsonData[key2]['description']+'</td><td><label class="label-checkbox"><input style="display:none;" type="radio" value="'+jsonData[key2]['hash']+'" name="hash"><span class="custom-radio"></span></label></td></tr>';
            fingerprint_count++;
        }
        message += '</tbody></table></div><div class="form-group" style="margin: 10px 0px 0px 0px;width: 100%;text-align: right;"><div class="col-lg-10 col-lg-offset-2"><input type="hidden" name="fingerprint_ip" id="fingerprint_ip" value="'+node_value+'"><input type="submit" name="resolve" value="Resolve" id="fingerprint_resolve" class="btn theme-form-submit-update  btn-primary"><input type="submit" name="allow" value="Allow" id="fingerprint_allow" class="btn theme-form-submit-update  btn-primary"><input type="submit" name="block" value="Block" id="fingerprint_block" class="btn theme-form-submit-update  btn-primary"><button type="button" id ="networkmap_modal_close" class="btn btn-default">Close</button></div></div><script>$("#networkmap_editpolicy1").click(function(){$(".bootstrap-dialog-close-button").click();add_to_whitelista(node_value); });$("#networkmap_modal_close").click(function(){$(".bootstrap-dialog-close-button").click();}); $("#fingerprint_resolve").click(function(){validate_form("resolve"); });$("#fingerprint_tag_to_investigate").click(function(){validate_form("tag_to_investigate"); });$("#fingerprint_block").click(function(){validate_form("block"); });$("#fingerprint_allow").click(function(){validate_form("allow"); });</script>';

        crystaleye_dialog_box_action('error', node_value + ' Device Fingerprints ('+fingerprint_count+')', message, options);
    
      },
      error: function() {
        
      }
   }); // ajax closed

}

// validate inputs and submit form
function validate_form(action_type) {
        var selected_hash = $('input[name="hash"]:checked').val();
        var fingerprint_ip = $("#fingerprint_ip").val();

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '/app/whitelisting/edit',
            data: {selected_hash: selected_hash, fingerprint_ip: fingerprint_ip, action_type:action_type, ci_csrf_token:$.cookie('ci_csrf_token')},
            beforeSend:function() {
                $('#fingerprint_resolve').attr('disabled', 'disabled');
                $('#fingerprint_tag_to_investigate').attr('disabled', 'disabled');
                $('#fingerprint_block').attr('disabled', 'disabled');
                $('#fingerprint_allow').attr('disabled', 'disabled');
            },
            success:function(data) {
                if(data.status == true)
                {
                    show_message_box('success', data.message);
                } else {
                    show_message_box('danger', data.message);
                }

                $('#fingerprint_resolve').attr('disabled', false);
                $('#fingerprint_tag_to_investigate').attr('disabled', false);
                $('#fingerprint_block').attr('disabled', false);
                $('#fingerprint_allow').attr('disabled', false);
                
            }
        });
} 

function show_message_box(type, message) { 
    $( ".alert-success" ).hide();
    $( ".alert-danger" ).hide();
    $(".bootstrap-dialog-header").after('<div class="alert alert-'+type+'" style = "position: absolute; top: 40px; right: 0px;max-width: 370px;"><strong>Success </strong>'+message+'</div><script>$( ".alert-success" ).fadeOut(10000);</script>');
}

function gwonLoad(jsonData) {
  var graphics = Viva.Graph.View.svgGraphics();

  // we will use SVG patterns to fill circle with image brush:
  // http://stackoverflow.com/questions/11496734/add-a-background-image-png-to-a-svg-circle-shape
  var defs = Viva.Graph.svg('defs');
  graphics.getSvgRoot().append(defs);

  graphics.node(createNodeWithImage)
    .placeNode(placeNodeWithTransform);

  var graph = gwconstructGraph(jsonData);
  var renderer = Viva.Graph.View.renderer(graph, {
    graphics: graphics,
    container: document.getElementById('gwdevices') // to change graph position in the box see graphCenterChanged function at line 4851 in vivagraph.js
  });

  renderer.run();

  function createNodeWithImage(node) {
    var radius = 12;
    var font_familty = 'FontAwesome';
    var color = '#33cccc';
    // First, we create a fill pattern and add it to SVG's defs:
    var svgText = Viva.Graph.svg('text')
      .attr('x', '20px')
      .attr('y', '15px')
      .attr('fill', '#ececec')
      .attr('font-size', '10px')
      .attr('cursor', 'pointer')
      .text(node.id);
  
    if(node.data.url == '\ue611')
    {
       font_familty = 'ci';
       color = '#cd1518';
    }

    var icon = Viva.Graph.svg('text')
      .attr('x', '0')
      .attr('y', '20')
      .attr('fill', color)
      .attr("font-family",font_familty)
      .attr('font-size', '20px')
      .attr('cursor', 'pointer')
      .text(node.data.url);

    // now create actual node and reference created fill pattern:
    var ui = Viva.Graph.svg('g');
    ui.append(svgText);
    ui.append(icon);
    //ui.append(circle);
    //on node click...............
    $(ui).click(function(){

      node_value = $(this).children('text').text().split(" ",1);
      show_gwd_map(node_value[0]);

    }); // on click ui closed
//.............................
    return ui;
  }

  function placeNodeWithTransform(nodeUI, pos) {
    // Shift image to let links go to the center:
    nodeUI.attr('transform', 'translate(' + (pos.x - 12) + ',' + (pos.y - 12) + ')');
  }
}

function gwconstructGraph(jsonData) {
  var graph = Viva.Graph.graph();
  var root_node = '';
  var node_details = '';
  var edge_details = '';
  var node_count = 0;

  for (var key in jsonData) {

    node_count++;
    var icon_code = '';
    node_details = jsonData[key]['ip']+' ';// + '-(' + jsonData[key]['fingureprintkey'] + ')'; // node_details string and edge_details string must be same
    if(jsonData[key]['type'] == 'device')
    {
       icon_code = '\uf10c';
    } else if(jsonData[key]['type'] == 'rootnode')
    {
       icon_code = '\ue611';
    } else 
    {
       icon_code = '\uf008';
    }

    if(jsonData[key]['type'] == 'rootnode'){ 
       root_node = node_details;
    }

    graph.addNode(node_details, {
            url: icon_code
    });
  }
  for (var key2 in jsonData) {
    if(jsonData[key2]['type'] == 'rootnode'){
        continue;
    }

    edge_details = jsonData[key2]['ip']+' ';// + '-(' + jsonData[key2]['fingureprintkey'] + ')'; // node_details string and edge_details string must be same
    graph.addLink(root_node, edge_details);
  } 

  if(node_count < 80)
  {
      $('#gwdevices').css("height", '300px');
  } else if ((node_count > 80) && (node_count < 180)) {
      $('#gwdevices').css("height", '400px');
  } else if (node_count > 180) {
      $('#gwdevices').css("height", '500px');
  }
  return graph;
}

// Drow Monitor report table
function show_monitor_report_table (timerange)
{
    // Main
    //-----

    var loading_error = '<?php echo "Failed to load." ?>';

    whitelisting_monitor_report_table = $('#tools_monitor_tbl').DataTable( {
        
        ajax: {
            url: '/app/whitelisting/tools_monitor/get_monitor_report/'+timerange,
            error: function (jqXHR, textStatus, errorThrown) {
                $('#tools_monitor_tbl_processing').hide();
                $('.dataTables_empty').text(loading_error);
            }
        },
        "deferRender": true,    
        "order": [[ 0, "asc" ]],
        'processing': true,
        'language': {
            'processing': '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="position:absolute;margin-left:-50%;margin-top:9%;color: #09e1fdb3;"></i>'
        },
        "aoColumns": [      // to add button in action column
            null,
            null,
            null,
            null,
            null,
            null,
            {
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, rule) {

                    var schedule_action = '<a href="#" class="btn btn-sm btn-primary">View</a>';

                    //if(rule[9] == 'Running')
                    
                    return '<div class="btn-group">'+schedule_action+'</div>';
                }
            }
        ]
    });

    // uncomment these lines to auto refresh table after 20 secs
    setInterval( function () {
        whitelisting_monitor_report_table.ajax.reload();
    }, 10000 );

}
// vim: ts=4 syntax=javascript
