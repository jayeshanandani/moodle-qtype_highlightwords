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


class qtype_highlightwords_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $PAGE;
        
        $question = $qa->get_question();
        $left = substr($question->delimitchars, 0, 1);
        $right = substr($question->delimitchars, 1, 1);
        $data = str_replace(array($left,$right),"",$qa->get_question()->format_questiontext($qa));
        $output = $data;

        $params = array(
        	'data' => $data
        );

        $PAGE->requires->yui_module('moodle-qtype_highlightwords-highlight',
                'M.qtype_highlightwords.highlight.init', array($params));

        return $output;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

}
