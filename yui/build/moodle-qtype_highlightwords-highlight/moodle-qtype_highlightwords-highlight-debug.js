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
		RED_BG : 'red-bg'
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

    init: function(param) {
        this.click_word(param);
    },
    
    click_word:function(){
		this.delegate('click', this.value_change, this.SELECTORS.VALUE_CHANGE_ELEMENTS);
	},

	value_change:function() {
        Y.one(this).toggleClass(this.CSS.RED_BG);
         	if (Y.one(this).hasClass(this.CSS.RED_BG)) {
            	temp[this.getAttribute('id')] = this.getHTML();
        	} else {
            	delete temp[this.getAttribute('id')];
        	}
        	console.log(temp);
    }
};

}, '@VERSION@', {"requires": ["base", "node", "text-wordbreak"]});
