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

namespace filter_recitactivity;

use core\hook\output\before_standard_footer_html_generation;
use core\hook\output\before_standard_top_of_body_html_generation;
use html_writer;
use moodle_url;

/**
 * Allows the plugin to add any elements to the head of a HTML document.
 *
 * @package    filter_recitactivity
 * @copyright  2024 RECITFAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {
        global $CFG;

        try {
            $script = "<script src='%s'></script>";
            $html = sprintf($script, "{$CFG->wwwroot}/filter/recitactivity/classes/qrcode/qrcode.min.js");
            $html .= sprintf($script, "{$CFG->wwwroot}/filter/recitactivity/filter.js?v=1190");
            $hook->add_html($html);
        } catch (\dml_read_exception $e) {
            return;
        }
    }
}
