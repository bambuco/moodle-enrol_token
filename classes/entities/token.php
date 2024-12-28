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
 * Token entity implementation.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace enrol_token\entities;

use lang_string;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\date;

/**
 * Class token
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class token extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_table_aliases(): array {
        return [
            'enrol_token_tokens' => 'ett',
            'user' => 'u',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('token', 'enrol_token');
    }

    /**
     * Initialise the entity.
     *
     * @return base
     */
    public function initialise(): base {

        $columns = $this->get_all_columns();

        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Add extra columns to report.
     * @return array
     * @throws \coding_exception
     */
    protected function get_all_columns(): array {

        $tokenalias = $this->get_table_alias('enrol_token_tokens');
        $useralias = $this->get_table_alias('user');

        $columns[] = (new column(
            'id',
            new lang_string('tokenid', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.id")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'enrolid',
            new lang_string('enrolid', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.enrolid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'userid',
            new lang_string('userid', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.userid")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);

        $columns[] = (new column(
            'userfullname',
            new lang_string('usedbynames', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_join("LEFT JOIN {user} {$useralias} " .
                "ON {$useralias}.id = {$tokenalias}.userid")
            ->add_fields("$useralias.id, $useralias.firstname, $useralias.lastname")
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->set_callback(static function(?int $userid, ?object $data): string {
                if (empty($userid)) {
                    return '';
                }

                return \html_writer::link(new \moodle_url('/user/view.php',
                                        ['id' => $userid]),
                                        $data->firstname . ' ' . $data->lastname,
                                        ['target' => '_blank']);
            });

        $columns[] = (new column(
            'token',
            new lang_string('token', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.token, $tokenalias.timeused")
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(false)
            ->set_callback(static function(?string $token, ?object $data): string {
                if (empty($data->timeused)) {
                    return $token;
                }

                return '<strike>' . $token . '</strike>';
            });

        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.timecreated")
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(false)
            ->set_callback(static function(?int $timecreated): string {
                return userdate($timecreated, get_string('strftimedatetimeshortaccurate', 'langconfig'));
            });

        $columns[] = (new column(
            'timeused',
            new lang_string('timeused', 'enrol_token'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->add_fields("$tokenalias.timeused")
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(false)
            ->set_callback(static function(?int $timeused): string {
                if (empty($timeused)) {
                    return '';
                }

                return userdate($timeused, get_string('strftimedatetimeshortaccurate', 'langconfig'));
            });

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {

        $filters = [];
        $tokenalias = $this->get_table_alias('enrol_token_tokens');

        $filters[] = (new filter(
            text::class,
            'token',
            new lang_string('token', 'enrol_token'),
            $this->get_entity_name(),
            "$tokenalias.token",
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timeused',
            new lang_string('timeused', 'enrol_token'),
            $this->get_entity_name(),
            "$tokenalias.timeused"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

}
