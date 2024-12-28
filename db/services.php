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
 * Token enrol plugin external functions and service definitions.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = [
    'enrol_token_get_instance_info' => [
        'classname'   => 'enrol_token_external',
        'methodname'  => 'get_instance_info',
        'classpath'   => 'enrol/token/externallib.php',
        'description' => 'token enrolment instance information.',
        'type'        => 'read',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'enrol_token_enrol_user' => [
        'classname'   => 'enrol_token_external',
        'methodname'  => 'enrol_user',
        'classpath'   => 'enrol/token/externallib.php',
        'description' => 'Token enrol the current user in the given course.',
        'type'        => 'write',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
