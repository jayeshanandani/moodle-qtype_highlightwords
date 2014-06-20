YUI.add('moodle-qtype_highlightword-highlight', function (Y, NAME) {

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
 * @package   qtype_highlightwords
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*YUI.add('moodle-qtype_highlightwords-highlight', function(Y) {
    var HIGHLIGHTWORDSHIGHLIGHTNAME = 'highlightwords_highlight';
    var HIGHLIGHTWORDS_HIGHLIGHT = function() {
        HIGHLIGHTWORDS_HIGHLIGHT.superclass.constructor.apply(this, arguments);
    };

M.qtype_highlightwords = M.qtype_highlightwords || {};
    M.qtype_highlightwords.init_question = function(config) {
        return new  HIGHLIGHTWORDS_HIGHLIGHT(config);
    };
}
});*/

M.qtype_highlightwords = M.qtype_highlightwords || {};
M.qtype_highlightwords.highlight = {
  init: function(param) {
  }
};

}, '@VERSION@');
