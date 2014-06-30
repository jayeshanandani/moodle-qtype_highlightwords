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
 * This file contains a class for splitting up HTML into 'words' and 'separators'.
 *
 * @package   qtype_highlightwords
 * @copyright 2014 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * A class for splitting up HTML into 'words' and 'separators'.
 */
abstract class qtype_highlightwords_parser {

    /**
     * Take some HTML input, and split it into 'words' and 'gaps'.
     * In addition, certain words can be marked, in which this is
     * indicated in the return value.
     *
     * For example, the input "<p>*Hello <b>world</b>!</p>" leads to the output
     * array(
     *     0 => array('text' => '<p>',      'type' => 'gap'),
     *     1 => array('text' => 'Hello',    'type' => 'word', 'marked' => true),
     *     2 => array('text' => ' <b>',     'type' => 'gap'),
     *     3 => array('text' => 'world',    'type' => 'word', 'marked' => false),
     *     4 => array('text' => '</b>!<p>', 'type' => 'gap'),
     *
     * Note that it is always the case that if you concatenate all the text in the
     * return value in order, you should get the input text with the markers stripped.
     *
     * @param string $html the input HTML source.
     * @param string $marker the character that marks the following word as selected. E.g. '*'
     * @return array of fragments. The array keys are integers in order. The values
     *     are again arrays with two are three keys: text, type and marked.
     *     type can be 'gap' or 'word', and marked will be persent only for words.
     */
    public static function parse($html, $marker = '*') {

        if (preg_match('~\w~', $marker)) {
            throw new coding_exception('The marker may not contain any word characters.');
        }

        // A separator is made up of non-word characters, or complete HTML open/close tags.
        $htmltagregex = '</?\w+(\s[^>]+)?>';
        $bits = preg_split('~((?:[^\w<]|(?:' . $htmltagregex . '))+)~',
                $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        // The result of the preg_split will be an array of alternating words and
        // separators, starting with a word. This tracks that.
        $isword = false; // Note, this gets flipped at the start of the loop.
        $result = array();
        $nextwordselected = false;
        foreach ($bits as $bit) {
            $isword = !$isword;

            if ($isword) {
                if ($bit === '') {
                    continue;
                }

                $item = array(
                    'text'   => $bit,
                    'type'   => 'word',
                    'marked' => $nextwordselected,
                );
                $nextwordselected = false;

            } else {
                if (strpos($bit, $marker) !== false) {
                    $nextwordselected = true;
                    $bit = str_replace($marker, '', $bit);
                }

                if ($bit === '') {
                    continue;
                }

                $item = array(
                    'text' => $bit,
                    'type' => 'gap',
                );
            }

            $result[] = $item;
        }
        return $result;
    }
}