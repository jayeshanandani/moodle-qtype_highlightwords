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
 * The editing form code for this question type.
 *
 * @package    qtype
 * @subpackage highlightwords
 * @copyright  2014 Jayesh Anandani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/question/type/edit_question_form.php');

defined('MOODLE_INTERNAL') || die();

/**
 * highlightwords editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_highlightwords_edit_form extends question_edit_form {

    public $answer;
    public $delimitchars;

    protected function definition_inner($mform) {
        $mform->addElement('hidden', 'reload', 1);
        $mform->setType('reload', PARAM_RAW);
        $mform->removeelement('generalfeedback');

        // Default mark will be set to 1 * number of fields.
        $mform->removeelement('defaultmark');

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question')
                , array('rows' => 10), $this->editoroptions);

        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
        $mform->addElement('header', 'feedbackheader', get_string('moreoptions', 'qtype_highlightwords'));

        // The delimiting characters around fields.
        $delimitchars = array("[]" => "[ ]", "{}" => "{ }", "##" => "##", "@@" => "@ @");
        $mform->addElement('select', 'delimitchars', get_string('delimitchars', 'qtype_highlightwords'), $delimitchars);
        $mform->addHelpButton('delimitchars', 'delimitchars', 'qtype_highlightwords');

        // To add combined feedback (correct, partial and incorrect).
        $this->add_combined_feedback_fields(true);

        // Adds hinting features.
        $this->add_interactive_settings(true, true);
    }


    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        $question = $this->data_preprocessing_hints($question, true, true);

        return $question;
    }

    public function validation($fromform, $data) {
        $errors = array();
        $left = substr($fromform['delimitchars'], 0, 1);
        $right = substr($fromform['delimitchars'], 1, 1);

        $fieldregex = '/\\' . $left . '(.*?)\\' . $right . '/';
        preg_match_all($fieldregex, $fromform['questiontext']['text'], $matches);
        if (count($matches[0]) == 0) {
            $errors['questiontext'] = get_string('questionsmissing', 'qtype_highlightwords_options');
        }

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    public function qtype() {
        return 'highlightwords';
    }

}