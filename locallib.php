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
 * Token enrol plugin implementation.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

/**
 * Token enrolment plugin class.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_token_enrol_form extends moodleform {

    /**
     * @var stdClass The enrol instance.
     */
    protected $instance;

    /**
     * @var bool If there are too many keyholders.
     */
    protected $toomany = false;

    /**
     * Overriding this function to get unique form id for multiple token enrolments.
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id . '_' . get_class($this);
        return $formid;
    }

    /**
     * Form definition.
     *
     */
    public function definition() {
        global $USER, $OUTPUT, $CFG;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('token');

        $heading = $plugin->get_instance_name($instance);
        $mform->addElement('header', 'tokenheader', $heading);

        // Change the id of token enrolment key input as there can be multiple token enrolment methods.
        $mform->addElement('text', 'enroltoken', get_string('token', 'enrol_token'),
                ['id' => 'enroltoken_'.$instance->id]);
        $mform->setType('enroltoken', PARAM_TEXT);
        $context = context_course::instance($this->instance->courseid);
        $userfieldsapi = \core_user\fields::for_userpic();
        $ufields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $keyholders = get_users_by_capability($context, 'enrol/token:holdkey', $ufields);
        $keyholdercount = 0;
        foreach ($keyholders as $keyholder) {
            $keyholdercount++;
            if ($keyholdercount === 1) {
                $mform->addElement('static', 'keyholder', '', get_string('keyholder', 'enrol_token'));
            }

            if ($USER->id == $keyholder->id || has_capability('moodle/user:viewdetails', context_system::instance()) ||
                    has_coursecontact_role($keyholder->id)) {
                $profilelink = '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $keyholder->id . '&amp;course=' .
                $this->instance->courseid . '">' . fullname($keyholder) . '</a>';
            } else {
                $profilelink = fullname($keyholder);
            }
            $profilepic = $OUTPUT->user_picture($keyholder, array('size' => 35, 'courseid' => $this->instance->courseid));
            $mform->addElement('static', 'keyholder'.$keyholdercount, '', $profilepic . $profilelink);
        }

        $this->add_action_buttons(false, get_string('enrolme', 'enrol_token'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $instance->id);
    }

    /**
     * Form validation.
     *
     * @param array $data Form data.
     * @param array $files Form files.
     * @return array Array of errors.
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        $instance = $this->instance;

        if ($this->toomany) {
            $errors['notice'] = get_string('error');
            return $errors;
        }

        $conditions = [
                        'enrolid' => $instance->id,
                        'token' => $data['enroltoken'],
                        'timeused' => 0,
                    ];
        $validtoken = $DB->count_records('enrol_token_tokens', $conditions);
        if ($validtoken < 1) {
            $errors['enroltoken'] = get_string('tokeninvalid', 'enrol_token');
        }

        return $errors;
    }
}
