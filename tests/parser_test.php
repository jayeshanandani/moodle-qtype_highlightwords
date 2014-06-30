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
 * This file contains tests for qtype_highlightwords_parser.
 *
 * @package   qtype_highlightwords
 * @copyright 2014 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Tests for qtype_highlightwords_parser.
 *
 * @copyright 2014 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_highlightwords_parser_testcase extends basic_testcase {

    public function test_blank() {
        $this->assertEquals(array(), qtype_highlightwords_parser::parse(''));
    }

    public function test_simple_string() {
        $this->assertEquals(array(
                    0 => array('text' => 'hello', 'type' => 'word', 'marked' => false),
                    1 => array('text' => ' ',     'type' => 'gap'),
                    2 => array('text' => 'world', 'type' => 'word', 'marked' => false),
                ), qtype_highlightwords_parser::parse('hello world'));
    }

    public function test_html_string() {
        $this->assertEquals(array(
                    0 => array('text' => '<p>',       'type' => 'gap'),
                    1 => array('text' => 'hello',     'type' => 'word', 'marked' => false),
                    2 => array('text' => ' <b>',      'type' => 'gap'),
                    3 => array('text' => 'world',     'type' => 'word', 'marked' => false),
                    4 => array('text' => '</b>!</p>', 'type' => 'gap'),
                ), qtype_highlightwords_parser::parse('<p>hello <b>world</b>!</p>'));
    }

    public function test_simple_string_word_marked() {
        $this->assertEquals(array(
                    0 => array('text' => 'hello', 'type' => 'word', 'marked' => true),
                    1 => array('text' => ' ',     'type' => 'gap'),
                    2 => array('text' => 'world', 'type' => 'word', 'marked' => false),
                ), qtype_highlightwords_parser::parse('*hello world'));
    }

    public function test_html_word_marked() {
        $this->assertEquals(array(
                    0 => array('text' => '<p>',       'type' => 'gap'),
                    1 => array('text' => 'hello',     'type' => 'word', 'marked' => true),
                    2 => array('text' => ' <b>',      'type' => 'gap'),
                    3 => array('text' => 'world',     'type' => 'word', 'marked' => false),
                    4 => array('text' => '</b>!</p>', 'type' => 'gap'),
                ), qtype_highlightwords_parser::parse('<p>*hello <b>world</b>!</p>'));
    }

    public function test_simple_string_marked_words_no_space() {
        $this->assertEquals(array(
                0 => array('text' => 'hello', 'type' => 'word', 'marked' => true),
                1 => array('text' => 'world', 'type' => 'word', 'marked' => true),
        ), qtype_highlightwords_parser::parse('*hello*world'));
    }

    public function test_simple_string_marked_odd_marker_position() {
        $this->assertEquals(array(
                0 => array('text' => 'hello', 'type' => 'word', 'marked' => false),
                1 => array('text' => ' ',     'type' => 'gap'),
                2 => array('text' => 'world', 'type' => 'word', 'marked' => true),
        ), qtype_highlightwords_parser::parse('hello* world'));
    }

    public function test_html_word_marked_odd_marker_position() {
        $this->assertEquals(array(
                0 => array('text' => '<p>',       'type' => 'gap'),
                1 => array('text' => 'hello',     'type' => 'word', 'marked' => false),
                2 => array('text' => ' <b>',      'type' => 'gap'),
                3 => array('text' => 'world',     'type' => 'word', 'marked' => true),
                4 => array('text' => '</b>!</p>', 'type' => 'gap'),
            ), qtype_highlightwords_parser::parse('<p>hello *<b>world</b>!</p>'));
    }

    public function test_simple_string_other_marker() {
        $this->assertEquals(array(
                0 => array('text' => 'hello', 'type' => 'word', 'marked' => true),
                1 => array('text' => ' ',     'type' => 'gap'),
                2 => array('text' => 'world', 'type' => 'word', 'marked' => false),
            ), qtype_highlightwords_parser::parse('~hello world', '~'));
    }

    public function test_simple_string_long_marker() {
        $this->assertEquals(array(
                0 => array('text' => 'hello', 'type' => 'word', 'marked' => true),
                1 => array('text' => ' ',     'type' => 'gap'),
                2 => array('text' => 'world', 'type' => 'word', 'marked' => false),
        ), qtype_highlightwords_parser::parse(':-)hello world', ':-)'));
    }

    public function test_simple_string_with_newlines() {
        $this->assertEquals(array(
                0 => array('text' => 'hello', 'type' => 'word', 'marked' => false),
                1 => array('text' => "\n\n",  'type' => 'gap'),
                2 => array('text' => 'world', 'type' => 'word', 'marked' => true),
            ), qtype_highlightwords_parser::parse("hello\n*\nworld"));
    }

    public function test_simple_string_invalid_marker() {
        $this->setExpectedException('coding_exception');
        qtype_highlightwords_parser::parse('', 'x');
    }

    public function test_simple_string_invalid_long_marker() {
        $this->setExpectedException('coding_exception');
        qtype_highlightwords_parser::parse('', '(x)');
    }
}