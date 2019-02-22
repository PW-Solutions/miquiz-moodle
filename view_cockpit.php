<?php

defined('MOODLE_INTERNAL') || die();
require_once("lib.php");

class cockpit
{
    public function __construct($is_manager, $miquiz)
    {
        $this->is_manager = $is_manager;
        $this->miquiz = $miquiz;
    }

    private function format_date($a_date){
        $date = new DateTime("", core_date::get_server_timezone_object());
        $date->setTimestamp($a_date);
        return date_format($date, 'd.m.Y H:i');
    }

    public function print_status($start_date, $prod_date, $end_date)
    {
        echo '<div id="timeline" style="padding-left:10px"></div>';
        echo "<script type='text/javascript'>
        //config
        dot_pos = [20, 70, 120]
        circle_size = 20
        dot_attr = {fill: '#eee',
                    stroke: '#111',
                    'stroke-width': 2}
        dot_over_fill = '#aaa'

        tooltip_conf = \"{content: {message}, placement: 'left-center', classes: ['info'], targetClasses: ['it-has-a-tooltip'], offset: 1, delay: { show: 100, hide: 200}}\"
        tooltip_messages = {
          cstart_message: '".$this->format_date($start_date)."',
          cprod_message: '".$this->format_date($prod_date)."',
          cend_message: '".$this->format_date($end_date)."'
        }

        line_offset = 10
        line_text = ['".get_string('miquiz_status_training', 'miquiz')."', '".get_string('miquiz_status_productive', 'miquiz')."']
        line_stroke_attr = {color: '#111',
                            width: 3,
                            linecap: 'round'}
        line_attr = {
          'stroke-dasharray': [1, 5],
          'stroke-dashoffset': 2
        }

        text_font = {size: 16,
                    family: 'Menlo, sans-serif',
                    anchor: 'start',
                    fill: '#000'}

        polyline_stroke_attr = {color: '#111',
                                width: 2,
                                linecap: 'round'}
        polyline_stroke_offet1 = 4
        polyline_stroke_offet2 = 12

        setactive = function(dot1, dot2, line, text, text_poly) {
          active_color = '#0c0'
          line.attr({'stroke-dasharray': [0, 0]}).stroke({color: active_color})
          dot1.attr({stroke: active_color})
          dot2.attr({stroke: active_color})
          text = text || 0;
          if(text != 0){
            text.font({fill: active_color})
          }
          text_poly = text_poly || 0;
          if(text_poly != 0){
            text_poly.attr({stroke: active_color})
          }
        }

        //render
        svg_size = [(line_offset+circle_size/2)*2+text_font.size*10, dot_pos[2]+circle_size+dot_pos[0]]
        var draw = SVG('timeline').size(svg_size[0],svg_size[1])

        lxoffset = line_offset+circle_size/2
        var lines = draw.line(lxoffset, 0, lxoffset, dot_pos[0]).stroke(line_stroke_attr).attr(line_attr)
        var linet = draw.line(lxoffset, dot_pos[0]+circle_size, lxoffset, dot_pos[1]).stroke(line_stroke_attr).attr(line_attr)
        var linep = draw.line(lxoffset, dot_pos[1]+circle_size, lxoffset, dot_pos[2]).stroke(line_stroke_attr).attr(line_attr)
        var linee = draw.line(lxoffset, dot_pos[2]+circle_size, lxoffset, svg_size[1]).stroke(line_stroke_attr).attr(line_attr)

        var polylinet = draw.polyline([[lxoffset+circle_size/2+polyline_stroke_offet1,dot_pos[0]+circle_size/2+polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet2,dot_pos[0]+circle_size/2+polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet2,dot_pos[1]+circle_size/2-polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet1,dot_pos[1]+circle_size/2-polyline_stroke_attr.width]]).attr({fill: 'none'}).stroke(polyline_stroke_attr)

        var polylinep = draw.polyline([[lxoffset+circle_size/2+polyline_stroke_offet1,dot_pos[1]+circle_size/2+polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet2,dot_pos[1]+circle_size/2+polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet2,dot_pos[2]+circle_size/2-polyline_stroke_attr.width],
                                       [lxoffset+circle_size/2+polyline_stroke_offet1,dot_pos[2]+circle_size/2-polyline_stroke_attr.width]]).attr({fill: 'none'}).stroke(polyline_stroke_attr)

        text_t = draw.text(line_text[0]).font(text_font).move(lxoffset+circle_size/2+polyline_stroke_offet2+polyline_stroke_offet1, dot_pos[0]+(dot_pos[1]-dot_pos[0])/2)
        text_p = draw.text(line_text[1]).font(text_font).move(lxoffset+circle_size/2+polyline_stroke_offet2+polyline_stroke_offet1, dot_pos[1]+(dot_pos[2]-dot_pos[1])/2)

        var cstart = draw.circle(circle_size).move(line_offset, dot_pos[0]).attr(dot_attr)
        var cprod = draw.circle(circle_size).move(line_offset, dot_pos[1]).attr(dot_attr)
        var cend = draw.circle(circle_size).move(line_offset, dot_pos[2]).attr(dot_attr)

        cstart.attr({'v-tooltip': tooltip_conf.replace('{message}', 'cstart_message'), 'v-on:mouseover': \"dotover('\"+cstart.id()+\"')\", 'v-on:mouseout': \"dotout('\"+cstart.id()+\"')\"})
        cprod.attr({'v-tooltip': tooltip_conf.replace('{message}', 'cprod_message'), 'v-on:mouseover': \"dotover('\"+cprod.id()+\"')\", 'v-on:mouseout': \"dotout('\"+cprod.id()+\"')\"})
        cend.attr({'v-tooltip': tooltip_conf.replace('{message}', 'cend_message'), 'v-on:mouseover': \"dotover('\"+cend.id()+\"')\", 'v-on:mouseout': \"dotout('\"+cend.id()+\"')\"})

        dotover = function(objid) {
          document.getElementById(objid).setAttribute(\"fill\", dot_over_fill)
        }
        dotout = function(objid) {
          document.getElementById(objid).setAttribute(\"fill\", dot_attr.fill)
        }
        ";

        //set active part
        $now = new DateTime("now");
        if($start_date < $now){
            echo "setactive(cstart, cstart, lines)";
        } elseif($prod_date < $now){
            echo "setactive(cstart, cprod, linet, text_t, polylinet)";
        } elseif($end_date < $now){
            echo "setactive(cprod, cend, linep, text_p, polylinep)";
        } else{
            echo "setactive(cend, cend, linee)";
        }

        echo "
        Vue.use(VTooltip)
        var app = new Vue({
          el: '#timeline',
          data: tooltip_messages
        })

        </script>";
    }

    public function print_js()
    {
      if($this->is_manager){
        $score_training = 0;
        $score_duel = 0;
        $score_training_correct = 0;
        $score_duel_correct = 0;
        $user_stats = miquiz::api_get("api/categories/" . $this->miquiz->miquizcategoryid . "/user-stats");
        $user_obj = miquiz::api_get("api/users");

        $resp = miquiz::api_get("api/categories/" . $this->miquiz->miquizcategoryid . "/stats");
        $answeredQuestions_training_total = $resp["answeredQuestions"]["training"]["total"];
        $answeredQuestions_training_correct = $resp["answeredQuestions"]["training"]["correct"];
        $answeredQuestions_duel_total = $resp["answeredQuestions"]["duel"]["total"];
        $answeredQuestions_duel_correct = $resp["answeredQuestions"]["duel"]["correct"];

        $answeredQuestions_total = number_format($answeredQuestions_training_total+$answeredQuestions_duel_total, 0);
        $answeredQuestions_correct = number_format($answeredQuestions_training_correct+$answeredQuestions_duel_correct, 0);
        $answeredQuestions_wrong = number_format($answeredQuestions_total-$answeredQuestions_correct, 0);

        $eps = pow(10000000, -1);
        $rel_answeredQuestions_total = number_format($answeredQuestions_total/($answeredQuestions_total+$eps), 2);
        $rel_answeredQuestions_correct = number_format($answeredQuestions_correct/($answeredQuestions_total+$eps), 2);
        $rel_answeredQuestions_wrong = number_format($answeredQuestions_wrong/($answeredQuestions_total+$eps), 2);

        $answered_abs = "(".$answeredQuestions_total."/".$answeredQuestions_correct."/".$answeredQuestions_wrong.")";
        $answered_rel = "(".$rel_answeredQuestions_total."/".$rel_answeredQuestions_correct."/".$rel_answeredQuestions_wrong.")";

        //TODO display relative answered questions
        echo '<script type="text/javascript">
        var data = [{
					"label": "'.get_string('miquiz_cockpit_correct', 'miquiz').'",
			        "value" : '.$answeredQuestions_correct.',
			        "color": "green"
		    	},{
					"label": "'.get_string('miquiz_cockpit_incorrect', 'miquiz').'",
					"value": '.$answeredQuestions_wrong.',
					"color": "red"
				}];
        var chart = nv.models.pieChart()
          .x(function(d) { return d.label })
		      .y(function(d) { return d.value })
		      .color(function(d) { return d.color })
          .showLabels(true)
		      .showLegend(false);
        d3.select("#piechart")
          .datum(data)
          .call(chart);
        nv.utils.windowResize(function() {
            chart.update();
          });
        </script>';
      }
    }

    public function print_header()
    {
        $dependencies = [
            'https://cdnjs.cloudflare.com/ajax/libs/svg.js/3.0.11/svg.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.6/vue.min.js',
            'https://unpkg.com/v-tooltip',
        ];
        foreach ($dependencies as $js) {
            echo '<script type="text/javascript" src="' . $js . '"></script>';
        }
        echo '<style>
        .tooltip {
          display: block !important;
          z-index: 10000;
        }

        .tooltip .tooltip-inner {
          background: black;
          color: white;
          border-radius: 16px;
          padding: 5px 10px 4px;
        }

        .tooltip .tooltip-arrow {
          width: 0;
          height: 0;
          border-style: solid;
          position: absolute;
          margin: 5px;
          border-color: black;
          z-index: 1;
        }

        .tooltip[x-placement^="top"] {
          margin-bottom: 5px;
        }

        .tooltip[x-placement^="top"] .tooltip-arrow {
          border-width: 5px 5px 0 5px;
          border-left-color: transparent !important;
          border-right-color: transparent !important;
          border-bottom-color: transparent !important;
          bottom: -5px;
          left: calc(50% - 5px);
          margin-top: 0;
          margin-bottom: 0;
        }

        .tooltip[x-placement^="bottom"] {
          margin-top: 5px;
        }

        .tooltip[x-placement^="bottom"] .tooltip-arrow {
          border-width: 0 5px 5px 5px;
          border-left-color: transparent !important;
          border-right-color: transparent !important;
          border-top-color: transparent !important;
          top: -5px;
          left: calc(50% - 5px);
          margin-top: 0;
          margin-bottom: 0;
        }

        .tooltip[x-placement^="right"] {
          margin-left: 5px;
        }

        .tooltip[x-placement^="right"] .tooltip-arrow {
          border-width: 5px 5px 5px 0;
          border-left-color: transparent !important;
          border-top-color: transparent !important;
          border-bottom-color: transparent !important;
          left: -5px;
          top: calc(50% - 5px);
          margin-left: 0;
          margin-right: 0;
        }

        .tooltip[x-placement^="left"] {
          margin-right: 5px;
        }

        .tooltip[x-placement^="left"] .tooltip-arrow {
          border-width: 5px 0 5px 5px;
          border-top-color: transparent !important;
          border-right-color: transparent !important;
          border-bottom-color: transparent !important;
          right: -5px;
          top: calc(50% - 5px);
          margin-left: 0;
          margin-right: 0;
        }

        .tooltip.popover .popover-inner {
          background: #f9f9f9;
          color: black;
          padding: 24px;
          border-radius: 5px;
          box-shadow: 0 5px 30px rgba(black, .1);
        }

        .tooltip.popover .popover-arrow {
          border-color: #f9f9f9;
        }

        .tooltip[aria-hidden="true"] {
          visibility: hidden;
          opacity: 0;
          transition: opacity .15s, visibility .15s;
        }

        .tooltip[aria-hidden="false"] {
          visibility: visible;
          opacity: 1;
          transition: opacity .15s;
        }
        </style>';

        if ($this->is_manager) {

            $dependencies = [
                'https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.2/d3.min.js',
                'https://nvd3-community.github.io/nvd3/build/nv.d3.js',
            ];
            foreach ($dependencies as $js) {
                echo '<script type="text/javascript" src="' . $js . '"></script>';
            }
            echo '<link href="https://nvd3-community.github.io/nvd3/build/nv.d3.css" rel="stylesheet" type="text/css">';
        }
    }
}
