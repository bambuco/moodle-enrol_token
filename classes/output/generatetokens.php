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
 * Class containing renderers for generate tokens.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_token\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing form for generate tokens.
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generatetokens implements renderable, templatable {

    /**
     * @var object Enrol.
     */
    private $enrol = null;

    /**
     * Constructor.
     *
     * @param object $enrol Current enrol instance
     */
    public function __construct($enrol) {

        $this->enrol = $enrol;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $defaultvariables = [
            'enrolid' => $this->enrol->id,
            'urlbase' => $CFG->wwwroot,
        ];

        return $defaultvariables;
    }
}
