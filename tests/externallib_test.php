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

namespace enrol_token;

use core_external\external_api;
use enrol_token_external;
use externallib_advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/enrol/token/externallib.php');

/**
 * Token enrol external PHPunit tests
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class externallib_test extends externallib_advanced_testcase {

    protected function enable_plugin() {
        $enabled = enrol_get_plugins(true);
        $enabled['token'] = true;
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    protected function disable_plugin() {
        $enabled = enrol_get_plugins(true);
        unset($enabled['token']);
        $enabled = array_keys($enabled);
        set_config('enrol_plugins_enabled', implode(',', $enabled));
    }

    /**
     * Test get_instance_info
     */
    public function test_get_instance_info() {
        global $DB;

        $this->resetAfterTest(true);

        // Check if token enrolment plugin is enabled.
        $tokenplugin = enrol_get_plugin('token');
        $this->assertNotEmpty($tokenplugin);

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);

        $coursedata = new \stdClass();
        $coursedata->visible = 0;
        $course = self::getDataGenerator()->create_course($coursedata);

        // Add enrolment methods for course.
        $instanceid1 = $tokenplugin->add_instance($course, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 1',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id));
        $instanceid2 = $tokenplugin->add_instance($course, array('status' => ENROL_INSTANCE_DISABLED,
                                                                'customint6' => 1,
                                                                'name' => 'Test instance 2',
                                                                'roleid' => $studentrole->id));

        $instanceid3 = $tokenplugin->add_instance($course, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'roleid' => $studentrole->id,
                                                                'customint6' => 1,
                                                                'name' => 'Test instance 3',));

        $enrolmentmethods = $DB->get_records('enrol', array('courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED));
        $this->assertCount(3, $enrolmentmethods);

        $this->setAdminUser();
        $instanceinfo1 = enrol_token_external::get_instance_info($instanceid1);
        $instanceinfo1 = external_api::clean_returnvalue(enrol_token_external::get_instance_info_returns(), $instanceinfo1);

        $this->assertEquals($instanceid1, $instanceinfo1['id']);
        $this->assertEquals($course->id, $instanceinfo1['courseid']);
        $this->assertEquals('token', $instanceinfo1['type']);
        $this->assertEquals('Test instance 1', $instanceinfo1['name']);
        $this->assertTrue($instanceinfo1['status']);

        $instanceinfo2 = enrol_token_external::get_instance_info($instanceid2);
        $instanceinfo2 = external_api::clean_returnvalue(enrol_token_external::get_instance_info_returns(), $instanceinfo2);
        $this->assertEquals($instanceid2, $instanceinfo2['id']);
        $this->assertEquals($course->id, $instanceinfo2['courseid']);
        $this->assertEquals('token', $instanceinfo2['type']);
        $this->assertEquals('Test instance 2', $instanceinfo2['name']);
        $this->assertEquals(get_string('canntenrol', 'enrol_token'), $instanceinfo2['status']);

        $instanceinfo3 = enrol_token_external::get_instance_info($instanceid3);
        $instanceinfo3 = external_api::clean_returnvalue(enrol_token_external::get_instance_info_returns(), $instanceinfo3);
        $this->assertEquals($instanceid3, $instanceinfo3['id']);
        $this->assertEquals($course->id, $instanceinfo3['courseid']);
        $this->assertEquals('token', $instanceinfo3['type']);
        $this->assertEquals('Test instance 3', $instanceinfo3['name']);
        $this->assertTrue($instanceinfo3['status']);

        // Try to retrieve information using a normal user for a hidden course.
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            enrol_token_external::get_instance_info($instanceid3);
        } catch (\moodle_exception $e) {
            $this->assertEquals('coursehidden', $e->errorcode);
        }
    }

    /**
     * Test enrol_user
     */
    public function test_enrol_user() {
        global $DB;

        self::resetAfterTest(true);
        $this->enable_plugin();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course(array('groupmode' => SEPARATEGROUPS, 'groupmodeforce' => 1));
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();

        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);

        $tokenplugin = enrol_get_plugin('token');
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $instance1id = $tokenplugin->add_instance($course1, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'name' => 'Test instance 1',
                                                                'customint6' => 1,
                                                                'roleid' => $studentrole->id));
        $instance2id = $tokenplugin->add_instance($course2, array('status' => ENROL_INSTANCE_DISABLED,
                                                                'customint6' => 1,
                                                                'name' => 'Test instance 2',
                                                                'roleid' => $studentrole->id));
        $instance1 = $DB->get_record('enrol', array('id' => $instance1id), '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', array('id' => $instance2id), '*', MUST_EXIST);

        self::setUser($user1);

        // Bad token enrol me.
        $result = enrol_token_external::enrol_user($course1->id, 'abcdef');
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);

        self::assertFalse($result['status']);

        // Token enrol me.
        $tokens = $tokenplugin->generate_tokens($instance1, 1);
        $result = enrol_token_external::enrol_user($course1->id, $tokens[0]);
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);

        self::assertTrue($result['status']);
        self::assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $instance1->id)));
        self::assertTrue(is_enrolled($context1, $user1));

        // Try instance not enabled.
        try {
            enrol_token_external::enrol_user($course2->id, 'abcdef');
        } catch (\moodle_exception $e) {
            self::assertEquals('canntenrol', $e->errorcode);
        }

        // Enable the instance.
        $tokenplugin->update_status($instance2, ENROL_INSTANCE_ENABLED);

        // Generate tokens.
        $tokens2 = $tokenplugin->generate_tokens($instance2, 3);

        // Try not passing a token.
        $result = enrol_token_external::enrol_user($course2->id, '');
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertFalse($result['status']);
        self::assertCount(1, $result['warnings']);
        self::assertEquals('4', $result['warnings'][0]['warningcode']);

        // Try passing an invalid token.
        $result = enrol_token_external::enrol_user($course2->id, 'invalidtoken');
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertFalse($result['status']);
        self::assertCount(1, $result['warnings']);
        self::assertEquals('4', $result['warnings'][0]['warningcode']);

        // Everything correct, now.
        $result = enrol_token_external::enrol_user($course2->id, $tokens2[0]);
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);

        self::assertTrue($result['status']);
        self::assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $instance2->id)));
        self::assertTrue(is_enrolled($context2, $user1));

        // Try multiple instances now, multiple errors.
        $instance3id = $tokenplugin->add_instance($course2, array('status' => ENROL_INSTANCE_ENABLED,
                                                                'customint6' => 1,
                                                                'name' => 'Test instance 2',
                                                                'roleid' => $studentrole->id));
        $instance3 = $DB->get_record('enrol', array('id' => $instance3id), '*', MUST_EXIST);
        // Generate tokens.
        $tokens3 = $tokenplugin->generate_tokens($instance3, 1);

        // One token available.
        self::assertEquals(1, $DB->count_records('enrol_token_tokens', ['enrolid' => $instance3->id, 'timeused' => 0]));

        self::setUser($user3);
        $result = enrol_token_external::enrol_user($course2->id, 'invalidtoken');
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertFalse($result['status']);
        self::assertCount(2, $result['warnings']);

        // A used token. Not enrol.
        $result = enrol_token_external::enrol_user($course2->id, $tokens2[0]);
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertFalse($result['status']);
        self::assertFalse(is_enrolled($context2, $user3));

        // Now, everything ok with a new token.
        $result = enrol_token_external::enrol_user($course2->id, $tokens2[1]);
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertTrue($result['status']);
        self::assertTrue(is_enrolled($context2, $user3));

        // Now test passing an instance id.
        self::setUser($user4);
        $result = enrol_token_external::enrol_user($course2->id, $tokens3[0], $instance3id);
        $result = external_api::clean_returnvalue(enrol_token_external::enrol_user_returns(), $result);
        self::assertTrue($result['status']);
        self::assertTrue(is_enrolled($context2, $user3));
        self::assertCount(0, $result['warnings']);
        self::assertEquals(1, $DB->count_records('user_enrolments', array('enrolid' => $instance3->id)));

        // Not tokens available.
        self::assertEquals(0, $DB->count_records('enrol_token_tokens', ['enrolid' => $instance3->id, 'timeused' => 0]));

        // Token used by the current user.
        self::assertEquals(1, $DB->count_records('enrol_token_tokens', ['enrolid' => $instance3->id, 'userid' => $user4->id]));
    }
}
