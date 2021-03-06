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
 * Highlight words question definition class.
 *
 * @package   qtype_highlightwords
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class qtype_highlightwords_question extends question_graded_automatically_with_countback {

    public $answer;
    public $correctfeedback;
    public $partiallycorrectfeedback = '';
    public $incorrectfeedback = '';
    public $correctfeedbackformat;
    public $partiallycorrectfeedbackformat;
    public $incorrectfeedbackformat;
    public $fraction;

    /** @var array of question_answer. */
    public $answers = array();

    /* the characters indicating word to be marked i.e. *cat
     */
    public $delimitchars = "*";

    /**
     * @var array place number => group number of the places in the question
     * text where choices can be put. Places are numbered from 1.
     */
    public $places = array();

    /**
     * @var array of strings, one longer than $places, which is achieved by
     * indexing from 0. The bits of question text that go between the placeholders.
     */
    public $textfragments;

    /** @var array index of the right choice for each stem. */
    public $rightchoices;
    public $allanswers = array();

    public function get_expected_data() {
        return array('answers' => PARAM_SEQUENCE);
    }

    public function is_complete_response(array $response) {
        return true;
    }

    public function get_validation_error(array $response) {
        if (!$this->is_gradable_response($response)) {
            return '';
        }
    }

    public function summarise_response(array $response) {
        $summary = "";
        foreach ($response as $key => $value) {
             $summary.=" " . $value . " ";
        }
         return $summary;
    }

    public function get_right_choice_for($place) {
        return $this->places[$place];
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        if ($prevresponse == $newresponse) {
            return true;
        } else {
            return false;
        }
    }

    public function get_correct_response() {
        $response = array();
        foreach ($this->places as $place => $answer) {
            $response[$this->field($place)] = $answer;
        }
        return $response;
    }

    public function is_correct_response($answergiven, $rightanswer) {
        if ($this->compare_response_with_answer($answergiven, $rightanswer, $this->casesensitive, $this->disableregex)) {
            return true;
        } else {
            return false;
        }
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && in_array($filearea, array('correctfeedback',
                    'partiallycorrectfeedback', 'incorrectfeedback'))) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea);
        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
        }
    }


    public function get_num_parts_right(array $response) {
        $numright = 0;
        foreach ($this->places as $place => $notused) {
            if (!array_key_exists($this->field($place), $response)) {
                continue;
            }
            $answergiven = $response[$this->field($place)];
            $rightanswer = $this->get_right_choice_for($place);
            if (!$this->casesensitive == 1) {
                $answergiven = strtolower($answergiven);
                $rightanswer = strtolower($rightanswer);
            }
            if ($this->compare_response_with_answer($answergiven, $rightanswer, $this->casesensitive, $this->disableregex)) {
                $numright+=1;
            }
        }
        return array($numright, count($this->places));
    }

    public function clear_wrong_from_response(array $response) {
        foreach ($this->places as $place => $notused) {
            if (!array_key_exists($this->field($place), $response)) {
                continue;
            }
            $answergiven = $response[$this->field($place)];
            $rightanswer = $this->get_right_choice_for($place);
            if (!$this->casesensitive == 1) {
                
                $answergiven = strtolower($answergiven);
                $rightanswer = strtolower($rightanswer);
            }
            if (!$this->compare_response_with_answer($answergiven, $rightanswer, $this->casesensitive, $this->disableregex)) {
                $response[$this->field($place)] = '';
            }
        }
        return $response;
    }

    public function grade_response(array $response) {
        $response = $this->discard_duplicates($response);
        list($right, $total) = $this->get_num_parts_right($response);
        $this->fraction = $right / $total;
        $grade = array($this->fraction, question_state::graded_state_for_fraction($this->fraction));
        return $grade;
    }

    public function compute_final_grade($responses, $totaltries) {
        // Only applies in interactive mode.
        $responses[0] = $this->discard_duplicates($responses[0]);
        $totalscore = 0;
        foreach ($this->places as $place => $notused) {
            $fieldname = $this->field($place);
            $lastwrongindex = -1;
            $finallyright = false;
            foreach ($responses as $i => $response) {
                $rcfp = $this->get_right_choice_for($place);
                /* break out the loop if response does not contain the key */
                if (!array_key_exists($fieldname, $response)) {
                    continue;
                }
                $resp = $response[$fieldname];
                if (!$this->compare_response_with_answer($resp, $rcfp, $this->casesensitive, $this->disableregex)) {
                    $lastwrongindex = $i;
                    $finallyright = false;
                } else {
                    $finallyright = true;
                }
            }
            if ($finallyright) {
                $totalscore += max(0, 1 - ($lastwrongindex + 1) * $this->penalty);
            }
        }
        return $totalscore / count($this->places);
    }

    public function compare_response_with_answer($answergiven, $answer, $casesensitive, $disableregex = false) {
        /* converts things like &lt; into < */
        $answer = htmlspecialchars_decode($answer);
        $answergiven = htmlspecialchars_decode($answergiven);
        /* useful with questions containing html code or math symbols */
        if ($disableregex == true) {
            if (strcmp(trim($answergiven), trim($answer)) == 0) {
                return true;
            } else {
                return false;
            }
        }
        $pattern = str_replace('/', '\/', $answer);
        $regexp = '/^' . $pattern . '$/u';
        
        /* the @ is to suppress warnings, e.g. someone forgot to turn off regex matching */
        if (@preg_match($regexp, trim($answergiven))) {
            return true;
        } else {
            return false;
        }
    }

}
