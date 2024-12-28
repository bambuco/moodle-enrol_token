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
 * System report for tokens.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_token\systemreports;

// We need to include the lib file because it is not included in AJAX calls.
require_once($CFG->dirroot . '/enrol/token/lib.php');

use enrol_token\entities\token;
use core_reportbuilder\local\helpers\database;
use core_reportbuilder\system_report;
use core_reportbuilder\local\report\action;
use stdClass;

/**
 * Class tokens
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tokens extends system_report {

    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        global $PAGE;

        // We need to ensure page context is always set, as required by output and string formatting.
        $PAGE->set_context($this->get_context());

        // Our main entity, it contains all of the column definitions that we need.
        $entitymain = new token();
        $entitymainalias = $entitymain->get_table_alias('enrol_token_tokens');

        $this->set_main_table('enrol_token_tokens', $entitymainalias);
        $this->add_entity($entitymain);

        $enrolid = $this->get_parameter('enrolid', 0, 'int');

        if (empty($enrolid)) {
            throw new \Exception('Enrol ID is required');
        }

        $param = database::generate_param_name();
        $params = [
            $param => $enrolid,
        ];
        $where = [
            "$entitymainalias.enrolid = :$param",
        ];

        $wheresql = implode(' AND ', $where);

        $this->add_base_condition_sql($wheresql, $params);

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->add_base_fields("{$entitymainalias}.id");

        $actiondelete = new action(
            new \moodle_url('/enrol/token/tokens.php', [
                                    'enrolid' => $enrolid,
                                    'delete' => ':id',
                                    'sesskey' => sesskey(),
                                ]
                            ),
            new \pix_icon('i/trash', ''),
            [],
            false,
            new \lang_string('delete')
        );

        $actiondelete->add_callback(function (stdClass $row): bool {
            foreach ($row as $key => $value) {
                if (strpos($key, 'timeused') !== false) {
                    return empty($value);
                }
            }
            return true;
        });

        $this->add_action(($actiondelete));

        // Set if report can be downloaded.
        $this->set_downloadable(true);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('enrol/token:config', $this->get_context());
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    public function add_columns(): void {
        $columns = [
            'token:token',
            'token:userfullname',
            'token:timecreated',
            'token:timeused',
        ];

        $this->add_columns_from_entities($columns);
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $filters = [
            'token:token',
            'token:timeused',
        ];

        $this->add_filters_from_entities($filters);
    }
}
