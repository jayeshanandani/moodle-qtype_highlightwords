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
 * @package    qtype
 * @subpackage highlightwords
 * @copyright  2014 Jayesh Anandani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

    /*
     *  Called when previewing a question or when displayed in a quiz
     */

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata);
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     * 
     */
    public function save_question_options($question) {
        /* Save the extra data to your database tables from the $question object */

        /* answerwords are the text within delimeters */
        $answerwords = $this->get_highlightwords($question, $question->delimitchars, $question->questiontext);
        $answerfields = $this->get_answer_fields($answerwords, $question);
        global $DB;

        $context = $question->context;
        // Fetch old answer ids so that we can reuse them.
        $this->update_question_answers($question, $answerfields);

        $options = $DB->get_record('qtype_highlightwords_options', array('questionid' => $question->id));
        $this->update_qtype_highlightwords_options($question, $options, $context);
        $this->save_hints($question, true);
        return true;
    }

    public function update_qtype_highlightwords_options($question, $options, $context) {
        global $DB;

        $options = $DB->get_record('qtype_highlightwords_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->delimitchars = '';
            $options->id = $DB->insert_record('qtype_highlightwords_options', $options);
        }

        $options->delimitchars = $question->delimitchars;

        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_highlightwords_options', $options);
    }

    public function update_question_answers($question, array $answerfields) {
        global $DB;
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');

        // Insert all the new answers.
        foreach ($answerfields as $field) {
            // Save the true answer - update an existing answer if possible.
            if ($answer = array_shift($oldanswers)) {
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->fraction = $field['fraction'];
                $DB->update_record('question_answers', $answer);
            } else {
                // Insert a blank record.
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->correctfeedback = '';
                $answer->partiallycorrectfeedback = '';
                $answer->incorrectfeedback = '';
                $answer->fraction = $field['fraction'];
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
        }
        // Delete old answer records.
        foreach ($oldanswers as $oa) {
            $DB->delete_records('question_answers', array('id' => $oa->id));
        }
    }

    public function get_highlightwords($question, $delimitchars, $questiontext) {
        /* left for left delimiter right for right delimiter
         * defaults to []
         */
        $left = substr($delimitchars, 0, 1);
        $right = substr($delimitchars, 1, 1);
        $fieldregex = '/.*?\\' . $left . '(.*?)\\' . $right . '/';
        $matches = array();
        preg_match_all($fieldregex, $questiontext, $matches);
        return $matches[1];
    }

    /**
     * Set up all the answer fields with respective fraction (mark values)
     * This is used to update the question_answers table. Answerwords has
     * been pulled from within the delimitchars e.g. the cat within [cat]
     * 
     * @param array $answerwords
     * @param type $question
     * @return type array
     */
    public function get_answer_fields(array $answerwords, $question) {
        $answerfields = array();
        /* this next block runs when importing from xml */
        if (property_exists($question, 'answer')) {
            foreach ($question->answer as $key => $value) {
                if ($question->fraction[$key] == 0) {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 0;
                } else {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 1;
                }
            }
        }

        /* the rest of this function runs when saving from edit form */
        if (!property_exists($question, 'answer')) {
            foreach ($answerwords as $key => $value) {
                $answerfields[$key]['value'] = $value;
                $answerfields[$key]['fraction'] = 1;
            }
        }
        return $answerfields;
    }

    /* data used by export_to_xml */

    public function extra_question_fields() {
        return array('qtype_highlightwords_options', 'delimitchars');
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
        $output .= '    <delimitchars>' . $question->options->delimitchars .
                "</delimitchars>\n";
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }

}