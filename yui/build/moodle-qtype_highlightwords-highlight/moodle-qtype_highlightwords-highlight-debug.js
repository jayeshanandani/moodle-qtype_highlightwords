YUI.add('moodle-qtype_highlightwords-highlight', function (Y, NAME) {

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

M.qtype_highlightwords = M.qtype_highlightwords || {};
M.qtype_highlightwords.highlight = {

    CSS: {
        SELECTED_WORD : 'selectedword'
    },

    /**
     * The selectors used throughout this class.
     *
     * @property SELECTORS
     * @private
     * @type Object
     * @static
     */
    SELECTORS: {
        VALUE_CHANGE_ELEMENTS: 'span'
    },

    rootDiv: null,
    inputName: null,

    init: function(inputname, topnode, readonly) {
        this.rootDiv = Y.one(topnode);
        this.inputName = inputname;
        if (!readonly) {
            this.rootDiv.delegate('click', this.value_change, this.SELECTORS.VALUE_CHANGE_ELEMENTS,this);
        }
    },

    value_change: function(e) {
        e.currentTarget.toggleClass('selectedword');

        var value = '';
        this.rootDiv.all(this.SELECTORS.VALUE_CHANGE_ELEMENTS).each(function(word) {
            if (word.hasClass('selectedword')) {
                if (value !== '') {
                    value += ',';
                }
                value += word.get('id');
            }
        });
        Y.one(document.getElementById(this.inputName)).set('value', value);
    }
};


}, '@VERSION@', {"requires": ["base", "node"]});
