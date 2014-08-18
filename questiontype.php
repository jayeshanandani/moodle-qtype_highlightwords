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
 * The question type class for the highlight words question type.
 *
 * @package   qtype_highlightwords
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 * The highlightwords question class
 * Load from database, and initialise class
 */
class qtype_highlightwords extends question_type {

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->textfragments = qtype_highlightwords_parser::parse($question->questiontext);
        $this->initialise_combined_feedback($question, $questiondata);
    }

    public function save_question_options($question) {

        $answerwords = $this->get_highlightwords($question, $question->questiontext);
        global $DB;

        $context = $question->context;

        $options = $DB->get_record('qtype_highlightwords_options', array('questionid' => $question->id));
        $this->update_qtype_highlightwords_options($question, $options, $context);
        $this->save_hints($question, true);
        return true;
    }

    public function update_qtype_highlightwords_options($question, $options, $context) {
        global $DB;
        
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_highlightwords_options', $options);
        }

        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_highlightwords_options', $options);
    }

    public function get_highlightwords($question, $questiontext) {
        $fieldregex = '/(\*\w+)/';
        $matches = array();
        preg_match_all($fieldregex, $questiontext, $matches);
        return $matches[1];
    }

    /* populates fields such as combined feedback in the editing form */

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_highlightwords_options', array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_highlightwords_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    public function questionid_column_name() {
        return 'questionid';
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'highlightwords') {
            return false;
        }
        $question = parent::import_from_xml($data, $question, $format, null);
        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false, $format->get_format($question->questiontextformat));
        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = parent::export_to_xml($question, $format);
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }

}