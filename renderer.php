<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Highlight words question renderer class.
 *
 * @package   qtype_highlightwords
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/highlightwords/classes/parser.php');


class qtype_highlightwords_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $PAGE;
        
        $question = $qa->get_question();
        $text = qtype_highlightwords_parser::parse($question->questiontext);
        $inputname = $qa->get_qt_field_name('answer');
        $data = $this->add_span_tag($inputname,$text);
        $output = $data;

        $params = array(
           'content' => $data
        );

        //$output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'words[]', 'id'=>'words'));

        $PAGE->requires->yui_module('moodle-qtype_highlightwords-highlight',
                'M.qtype_highlightwords.highlight.init', array());

        

        return $output;
    }

    public function add_span_tag($inputname,$text) {
    	$data = array();
    	$counter = 0;
        foreach ($text as $key => $value) {
                if($value['type'] === 'word') {
                	$tag = html_writer::tag('span', $value['text'], array('class'=>'node', 'id' => $inputname.'_'.$counter));
                    $data[$counter] = $tag;
                    $counter = $counter + 1;
                } else {
                	$data[$counter] = $value['text'];
                	$counter = $counter + 1;
                }   
        }
        $content = implode("", $data);
        return $content;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

}
