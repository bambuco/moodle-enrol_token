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

use context_course;
use enrol_token_plugin;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/enrol/token/lib.php');
require_once($CFG->dirroot.'/enrol/token/locallib.php');

/**
 * Token enrolment plugin tests.
 *
 * @package    enrol_token
 * @category   phpunit
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \enrol_token_plugin
 */
final class token_test extends \advanced_testcase {

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

    public function test_basics() {
        $this->assertTrue(enrol_is_enabled('token'));
        $plugin = enrol_get_plugin('token');
        $this->assertInstanceOf('enrol_token_plugin', $plugin);
        $this->assertEquals(1, get_config('enrol_token', 'defaultenrol'));
        $this->assertEquals(ENROL_EXT_REMOVED_KEEP, get_config('enrol_token', 'expiredaction'));
    }

    public function test_sync_nothing() {
        global $SITE;

        $tokenplugin = enrol_get_plugin('token');

        $trace = new \null_progress_trace();

        // Just make sure the sync does not throw any errors when nothing to do.
        $tokenplugin->sync($trace, null);
        $tokenplugin->sync($trace, $SITE->id);
    }

    public function test_longtimnosee() {
        global $DB;
        $this->resetAfterTest();

        $tokenplugin = enrol_get_plugin('token');
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $now = time();

        $trace = new \progress_trace_buffer(new \text_progress_trace(), false);
        $this->enable_plugin();

        // Prepare some data.

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', ['shortname' => 'teacher']);
        $this->assertNotEmpty($teacherrole);

        $record = ['firstaccess' => $now - 60*60*24*800];
        $record['lastaccess'] = $now - 60*60*24*100;
        $user1 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 60*60*24*10;
        $user2 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 60*60*24*1;
        $user3 = $this->getDataGenerator()->create_user($record);
        $record['lastaccess'] = $now - 10;
        $user4 = $this->getDataGenerator()->create_user($record);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $tokenplugin->add_instance($course1, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course2, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course3, ['roleid' => $studentrole->id]);

        $this->assertEquals(3, $DB->count_records('enrol', ['enrol'=>'token']));
        $instance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $id = $tokenplugin->add_instance($course3, array('status'=>ENROL_INSTANCE_ENABLED, 'roleid'=>$teacherrole->id));
        $instance3b = $DB->get_record('enrol', array('id'=>$id), '*', MUST_EXIST);
        unset($id);

        $this->assertEquals($studentrole->id, $instance1->roleid);
        $instance1->customint2 = 60*60*24*14;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $tokenplugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $tokenplugin->enrol_user($instance1, $user3->id, $studentrole->id);
        $this->assertEquals(3, $DB->count_records('user_enrolments'));
        $DB->insert_record('user_lastaccess', array('userid'=>$user2->id, 'courseid'=>$course1->id, 'timeaccess'=>$now-60*60*24*20));
        $DB->insert_record('user_lastaccess', array('userid'=>$user3->id, 'courseid'=>$course1->id, 'timeaccess'=>$now-60*60*24*2));
        $DB->insert_record('user_lastaccess', array('userid'=>$user4->id, 'courseid'=>$course1->id, 'timeaccess'=>$now-60));

        $this->assertEquals($studentrole->id, $instance3->roleid);
        $instance3->customint2 = 60*60*24*50;
        $DB->update_record('enrol', $instance3);
        $tokenplugin->enrol_user($instance3, $user1->id, $studentrole->id);
        $tokenplugin->enrol_user($instance3, $user2->id, $studentrole->id);
        $tokenplugin->enrol_user($instance3, $user3->id, $studentrole->id);
        $tokenplugin->enrol_user($instance3b, $user1->id, $teacherrole->id);
        $tokenplugin->enrol_user($instance3b, $user4->id, $teacherrole->id);
        $this->assertEquals(8, $DB->count_records('user_enrolments'));
        $DB->insert_record('user_lastaccess', array('userid'=>$user2->id, 'courseid'=>$course3->id, 'timeaccess'=>$now-60*60*24*11));
        $DB->insert_record('user_lastaccess', array('userid'=>$user3->id, 'courseid'=>$course3->id, 'timeaccess'=>$now-60*60*24*200));
        $DB->insert_record('user_lastaccess', array('userid'=>$user4->id, 'courseid'=>$course3->id, 'timeaccess'=>$now-60*60*24*200));

        $maninstance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance2, $user1->id, $studentrole->id);
        $manualplugin->enrol_user($maninstance3, $user1->id, $teacherrole->id);

        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(9, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));

        // Execute sync - this is the same thing used from cron.

        $tokenplugin->sync($trace, $course2->id);
        $output = $trace->get_buffer();
        $trace->reset_buffer();
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertStringContainsString('No expired enrol_token enrolments detected', $output);
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user1->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user2->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user1->id)));
        $this->assertTrue($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user3->id)));

        $tokenplugin->sync($trace, null);
        $output = $trace->get_buffer();
        $trace->reset_buffer();
        $this->assertEquals(6, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user1->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user2->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user1->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user3->id)));
        $this->assertStringContainsString('unenrolling user ' . $user1->id . ' from course ' . $course1->id .
            ' as they did not log in for at least 14 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user1->id . ' from course ' . $course3->id .
            ' as they did not log in for at least 50 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user2->id . ' from course ' . $course1->id .
            ' as they did not access the course for at least 14 days', $output);
        $this->assertStringContainsString('unenrolling user ' . $user3->id . ' from course ' . $course3->id .
            ' as they did not access the course for at least 50 days', $output);
        $this->assertStringNotContainsString('unenrolling user ' . $user4->id, $output);

        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(4, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
    }

    public function test_expired() {
        global $DB;
        $this->resetAfterTest();

        $tokenplugin = enrol_get_plugin('token');
        $manualplugin = enrol_get_plugin('manual');
        $this->assertNotEmpty($manualplugin);

        $now = time();

        $trace = new \null_progress_trace();
        $this->enable_plugin();

        // Prepare some data.

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->assertNotEmpty($teacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);
        $context3 = \context_course::instance($course3->id);
        $tokenplugin->add_instance($course1, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course2, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course3, ['roleid' => $studentrole->id]);

        $this->assertEquals(3, $DB->count_records('enrol', array('enrol'=>'token')));
        $instance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance1->roleid);
        $instance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance2->roleid);
        $instance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $this->assertEquals($studentrole->id, $instance3->roleid);
        $id = $tokenplugin->add_instance($course3, array('status'=>ENROL_INSTANCE_ENABLED, 'roleid'=>$teacherrole->id));
        $instance3b = $DB->get_record('enrol', array('id'=>$id), '*', MUST_EXIST);
        $this->assertEquals($teacherrole->id, $instance3b->roleid);
        unset($id);

        $maninstance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $maninstance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);

        $manualplugin->enrol_user($maninstance2, $user1->id, $studentrole->id);
        $manualplugin->enrol_user($maninstance3, $user1->id, $teacherrole->id);

        $this->assertEquals(2, $DB->count_records('user_enrolments'));
        $this->assertEquals(2, $DB->count_records('role_assignments'));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));

        $tokenplugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $tokenplugin->enrol_user($instance1, $user2->id, $studentrole->id);
        $tokenplugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now-60);

        $tokenplugin->enrol_user($instance3, $user1->id, $studentrole->id, 0, 0);
        $tokenplugin->enrol_user($instance3, $user2->id, $studentrole->id, 0, $now-60*60);
        $tokenplugin->enrol_user($instance3, $user3->id, $studentrole->id, 0, $now+60*60);
        $tokenplugin->enrol_user($instance3b, $user1->id, $teacherrole->id, $now-60*60*24*7, $now-60);
        $tokenplugin->enrol_user($instance3b, $user4->id, $teacherrole->id);

        role_assign($managerrole->id, $user3->id, $context1->id);

        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));

        // Execute tests.

        $this->assertEquals(ENROL_EXT_REMOVED_KEEP, $tokenplugin->get_config('expiredaction'));
        $tokenplugin->sync($trace, null);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));


        $tokenplugin->set_config('expiredaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);
        $tokenplugin->sync($trace, $course2->id);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));

        $tokenplugin->sync($trace, null);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(7, $DB->count_records('role_assignments'));
        $this->assertEquals(5, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context1->id, 'userid'=>$user3->id, 'roleid'=>$studentrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context3->id, 'userid'=>$user2->id, 'roleid'=>$studentrole->id)));
        $this->assertFalse($DB->record_exists('role_assignments', array('contextid'=>$context3->id, 'userid'=>$user1->id, 'roleid'=>$teacherrole->id)));
        $this->assertTrue($DB->record_exists('role_assignments', array('contextid'=>$context3->id, 'userid'=>$user1->id, 'roleid'=>$studentrole->id)));


        $tokenplugin->set_config('expiredaction', ENROL_EXT_REMOVED_UNENROL);

        role_assign($studentrole->id, $user3->id, $context1->id);
        role_assign($studentrole->id, $user2->id, $context3->id);
        role_assign($teacherrole->id, $user1->id, $context3->id);
        $this->assertEquals(10, $DB->count_records('user_enrolments'));
        $this->assertEquals(10, $DB->count_records('role_assignments'));
        $this->assertEquals(7, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(2, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));

        $tokenplugin->sync($trace, null);
        $this->assertEquals(7, $DB->count_records('user_enrolments'));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance1->id, 'userid'=>$user3->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance3->id, 'userid'=>$user2->id)));
        $this->assertFalse($DB->record_exists('user_enrolments', array('enrolid'=>$instance3b->id, 'userid'=>$user1->id)));
        $this->assertEquals(6, $DB->count_records('role_assignments'));
        $this->assertEquals(5, $DB->count_records('role_assignments', array('roleid'=>$studentrole->id)));
        $this->assertEquals(1, $DB->count_records('role_assignments', array('roleid'=>$teacherrole->id)));
    }

    public function test_send_expiry_notifications() {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...

        /** @var $tokenplugin enrol_token_plugin */
        $tokenplugin = enrol_get_plugin('token');
        /** @var $manualplugin enrol_manual_plugin */
        $manualplugin = enrol_get_plugin('manual');
        $now = time();
        $admin = get_admin();

        $trace = new \null_progress_trace();
        $this->enable_plugin();

        // Note: hopefully nobody executes the unit tests the last second before midnight...

        $tokenplugin->set_config('expirynotifylast', $now - 60*60*24);
        $tokenplugin->set_config('expirynotifyhour', 0);

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $editingteacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        $this->assertNotEmpty($editingteacherrole);
        $managerrole = $DB->get_record('role', array('shortname'=>'manager'));
        $this->assertNotEmpty($managerrole);

        $user1 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser1'));
        $user2 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser2'));
        $user3 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser3'));
        $user4 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser4'));
        $user5 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser5'));
        $user6 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser6'));
        $user7 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser6'));
        $user8 = $this->getDataGenerator()->create_user(array('lastname'=>'xuser6'));

        $course1 = $this->getDataGenerator()->create_course(array('fullname'=>'xcourse1'));
        $course2 = $this->getDataGenerator()->create_course(array('fullname'=>'xcourse2'));
        $course3 = $this->getDataGenerator()->create_course(array('fullname'=>'xcourse3'));
        $course4 = $this->getDataGenerator()->create_course(array('fullname'=>'xcourse4'));

        $tokenplugin->add_instance($course1, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course2, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course3, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course4, ['roleid' => $studentrole->id]);

        $this->assertEquals(4, $DB->count_records('enrol', array('enrol'=>'manual')));
        $this->assertEquals(4, $DB->count_records('enrol', array('enrol'=>'token')));

        $maninstance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $instance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance1->expirythreshold = 60*60*24*4;
        $instance1->expirynotify    = 1;
        $instance1->notifyall       = 1;
        $instance1->status          = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance1);

        $maninstance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $instance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance2->expirythreshold = 60*60*24*1;
        $instance2->expirynotify    = 1;
        $instance2->notifyall       = 1;
        $instance2->status          = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance2);

        $maninstance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $instance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance3->expirythreshold = 60*60*24*1;
        $instance3->expirynotify    = 1;
        $instance3->notifyall       = 0;
        $instance3->status          = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance3);

        $maninstance4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'manual'), '*', MUST_EXIST);
        $instance4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance4->expirythreshold = 60*60*24*1;
        $instance4->expirynotify    = 0;
        $instance4->notifyall       = 0;
        $instance4->status          = ENROL_INSTANCE_ENABLED;
        $DB->update_record('enrol', $instance4);

        $tokenplugin->enrol_user($instance1, $user1->id, $studentrole->id, 0, $now + 60*60*24*1, ENROL_USER_SUSPENDED); // Suspended users are not notified.
        $tokenplugin->enrol_user($instance1, $user2->id, $studentrole->id, 0, $now + 60*60*24*5);                       // Above threshold are not notified.
        $tokenplugin->enrol_user($instance1, $user3->id, $studentrole->id, 0, $now + 60*60*24*3 + 60*60);               // Less than one day after threshold - should be notified.
        $tokenplugin->enrol_user($instance1, $user4->id, $studentrole->id, 0, $now + 60*60*24*4 - 60*3);                // Less than one day after threshold - should be notified.
        $tokenplugin->enrol_user($instance1, $user5->id, $studentrole->id, 0, $now + 60*60);                            // Should have been already notified.
        $tokenplugin->enrol_user($instance1, $user6->id, $studentrole->id, 0, $now - 60);                               // Already expired.
        $manualplugin->enrol_user($maninstance1, $user7->id, $editingteacherrole->id);
        $manualplugin->enrol_user($maninstance1, $user8->id, $managerrole->id);                                        // Highest role --> enroller.

        $tokenplugin->enrol_user($instance2, $user1->id, $studentrole->id);
        $tokenplugin->enrol_user($instance2, $user2->id, $studentrole->id, 0, $now + 60*60*24*1 + 60*3);                // Above threshold are not notified.
        $tokenplugin->enrol_user($instance2, $user3->id, $studentrole->id, 0, $now + 60*60*24*1 - 60*60);               // Less than one day after threshold - should be notified.

        $manualplugin->enrol_user($maninstance3, $user1->id, $editingteacherrole->id);
        $tokenplugin->enrol_user($instance3, $user2->id, $studentrole->id, 0, $now + 60*60*24*1 + 60);                  // Above threshold are not notified.
        $tokenplugin->enrol_user($instance3, $user3->id, $studentrole->id, 0, $now + 60*60*24*1 - 60*60);               // Less than one day after threshold - should be notified.

        $manualplugin->enrol_user($maninstance4, $user4->id, $editingteacherrole->id);
        $tokenplugin->enrol_user($instance4, $user5->id, $studentrole->id, 0, $now + 60*60*24*1 + 60);
        $tokenplugin->enrol_user($instance4, $user6->id, $studentrole->id, 0, $now + 60*60*24*1 - 60*60);

        // The notification is sent out in fixed order first individual users,
        // then summary per course by enrolid, user lastname, etc.
        $this->assertGreaterThan($instance1->id, $instance2->id);
        $this->assertGreaterThan($instance2->id, $instance3->id);

        $sink = $this->redirectMessages();

        $tokenplugin->send_expiry_notifications($trace);

        $messages = $sink->get_messages();

        $this->assertEquals(2+1 + 1+1 + 1 + 0, count($messages));

        // First individual notifications from course1.
        $this->assertEquals($user3->id, $messages[0]->useridto);
        $this->assertEquals($user8->id, $messages[0]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[0]->fullmessagehtml);

        $this->assertEquals($user4->id, $messages[1]->useridto);
        $this->assertEquals($user8->id, $messages[1]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[1]->fullmessagehtml);

        // Then summary for course1.
        $this->assertEquals($user8->id, $messages[2]->useridto);
        $this->assertEquals($admin->id, $messages[2]->useridfrom);
        $this->assertStringContainsString('xcourse1', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser4', $messages[2]->fullmessagehtml);
        $this->assertStringContainsString('xuser5', $messages[2]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[2]->fullmessagehtml);

        // First individual notifications from course2.
        $this->assertEquals($user3->id, $messages[3]->useridto);
        $this->assertEquals($admin->id, $messages[3]->useridfrom);
        $this->assertStringContainsString('xcourse2', $messages[3]->fullmessagehtml);

        // Then summary for course2.
        $this->assertEquals($admin->id, $messages[4]->useridto);
        $this->assertEquals($admin->id, $messages[4]->useridfrom);
        $this->assertStringContainsString('xcourse2', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[4]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser4', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser5', $messages[4]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[4]->fullmessagehtml);

        // Only summary in course3.
        $this->assertEquals($user1->id, $messages[5]->useridto);
        $this->assertEquals($admin->id, $messages[5]->useridfrom);
        $this->assertStringContainsString('xcourse3', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser1', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser2', $messages[5]->fullmessagehtml);
        $this->assertStringContainsString('xuser3', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser4', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser5', $messages[5]->fullmessagehtml);
        $this->assertStringNotContainsString('xuser6', $messages[5]->fullmessagehtml);


        // Make sure that notifications are not repeated.
        $sink->clear();

        $tokenplugin->send_expiry_notifications($trace);
        $this->assertEquals(0, $sink->count());

        // use invalid notification hour to verify that before the hour the notifications are not sent.
        $tokenplugin->set_config('expirynotifylast', time() - 60*60*24);
        $tokenplugin->set_config('expirynotifyhour', '24');

        $tokenplugin->send_expiry_notifications($trace);
        $this->assertEquals(0, $sink->count());

        $tokenplugin->set_config('expirynotifyhour', '0');
        $tokenplugin->send_expiry_notifications($trace);
        $this->assertEquals(6, $sink->count());
    }

    public function test_show_enrolme_link() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...
        $this->enable_plugin();

        /** @var $tokenplugin enrol_token_plugin */
        $tokenplugin = enrol_get_plugin('token');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();
        $course4 = $this->getDataGenerator()->create_course();
        $course5 = $this->getDataGenerator()->create_course();
        $course6 = $this->getDataGenerator()->create_course();
        $course7 = $this->getDataGenerator()->create_course();
        $course8 = $this->getDataGenerator()->create_course();
        $course9 = $this->getDataGenerator()->create_course();
        $course10 = $this->getDataGenerator()->create_course();
        $course11 = $this->getDataGenerator()->create_course();

        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();

        $tokenplugin->add_instance($course1, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course2, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course3, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course4, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course5, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course6, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course7, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course8, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course9, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course10, ['roleid' => $studentrole->id]);
        $tokenplugin->add_instance($course11, ['roleid' => $studentrole->id]);

        // New enrolments are allowed and enrolment instance is enabled.
        $instance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->update_status($instance1, ENROL_INSTANCE_ENABLED);

        // New enrolments are not allowed, but enrolment instance is enabled.
        $instance2 = $DB->get_record('enrol', array('courseid'=>$course2->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance2->customint6 = 0;
        $DB->update_record('enrol', $instance2);
        $tokenplugin->update_status($instance2, ENROL_INSTANCE_ENABLED);

        // New enrolments are allowed , but enrolment instance is disabled.
        $instance3 = $DB->get_record('enrol', array('courseid'=>$course3->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance3->customint6 = 1;
        $DB->update_record('enrol', $instance3);
        $tokenplugin->update_status($instance3, ENROL_INSTANCE_DISABLED);

        // New enrolments are not allowed and enrolment instance is disabled.
        $instance4 = $DB->get_record('enrol', array('courseid'=>$course4->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance4->customint6 = 0;
        $DB->update_record('enrol', $instance4);
        $tokenplugin->update_status($instance4, ENROL_INSTANCE_DISABLED);

        // Cohort member test.
        $instance5 = $DB->get_record('enrol', array('courseid'=>$course5->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance5->customint6 = 1;
        $instance5->customint5 = $cohort1->id;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->update_status($instance5, ENROL_INSTANCE_ENABLED);

        $id = $tokenplugin->add_instance($course5, $tokenplugin->get_instance_defaults());
        $instance6 = $DB->get_record('enrol', array('id'=>$id), '*', MUST_EXIST);
        $instance6->customint6 = 1;
        $instance6->customint5 = $cohort2->id;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->update_status($instance6, ENROL_INSTANCE_ENABLED);

        // Enrol start date is in future.
        $instance7 = $DB->get_record('enrol', array('courseid'=>$course6->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance7->customint6 = 1;
        $instance7->enrolstartdate = time() + 60;
        $DB->update_record('enrol', $instance7);
        $tokenplugin->update_status($instance7, ENROL_INSTANCE_ENABLED);

        // Enrol start date is in past.
        $instance8 = $DB->get_record('enrol', array('courseid'=>$course7->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance8->customint6 = 1;
        $instance8->enrolstartdate = time() - 60;
        $DB->update_record('enrol', $instance8);
        $tokenplugin->update_status($instance8, ENROL_INSTANCE_ENABLED);

        // Enrol end date is in future.
        $instance9 = $DB->get_record('enrol', array('courseid'=>$course8->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance9->customint6 = 1;
        $instance9->enrolenddate = time() + 60;
        $DB->update_record('enrol', $instance9);
        $tokenplugin->update_status($instance9, ENROL_INSTANCE_ENABLED);

        // Enrol end date is in past.
        $instance10 = $DB->get_record('enrol', array('courseid'=>$course9->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance10->customint6 = 1;
        $instance10->enrolenddate = time() - 60;
        $DB->update_record('enrol', $instance10);
        $tokenplugin->update_status($instance10, ENROL_INSTANCE_ENABLED);

        // Maximum enrolments reached.
        $instance11 = $DB->get_record('enrol', array('courseid'=>$course10->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance11->customint6 = 1;
        $instance11->customint3 = 1;
        $DB->update_record('enrol', $instance11);
        $tokenplugin->update_status($instance11, ENROL_INSTANCE_ENABLED);
        $tokenplugin->enrol_user($instance11, $user2->id, $studentrole->id);

        // Maximum enrolments not reached.
        $instance12 = $DB->get_record('enrol', array('courseid'=>$course11->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance12->customint6 = 1;
        $instance12->customint3 = 1;
        $DB->update_record('enrol', $instance12);
        $tokenplugin->update_status($instance12, ENROL_INSTANCE_ENABLED);

        $this->setUser($user1);
        $this->assertTrue($tokenplugin->show_enrolme_link($instance1));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance2));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance3));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance4));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance7));
        $this->assertTrue($tokenplugin->show_enrolme_link($instance8));
        $this->assertTrue($tokenplugin->show_enrolme_link($instance9));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance10));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance11));
        $this->assertTrue($tokenplugin->show_enrolme_link($instance12));

        require_once("$CFG->dirroot/cohort/lib.php");
        cohort_add_member($cohort1->id, $user1->id);

        $this->assertTrue($tokenplugin->show_enrolme_link($instance5));
        $this->assertFalse($tokenplugin->show_enrolme_link($instance6));
    }

    /**
     * This will check user enrolment only, rest has been tested in test_show_enrolme_link.
     */
    public function test_can_self_enrol() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->enable_plugin();

        $tokenplugin = enrol_get_plugin('token');

        $expectederrorstring = get_string('canntenrol', 'enrol_token');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $guest = $DB->get_record('user', array('id' => $CFG->siteguest));

        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        $this->assertNotEmpty($studentrole);
        $editingteacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'));
        $this->assertNotEmpty($editingteacherrole);

        $course1 = $this->getDataGenerator()->create_course();
        $tokenplugin->add_instance($course1, ['roleid' => $studentrole->id]);

        $instance1 = $DB->get_record('enrol', array('courseid'=>$course1->id, 'enrol'=>'token'), '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->update_status($instance1, ENROL_INSTANCE_ENABLED);
        $tokenplugin->enrol_user($instance1, $user2->id, $editingteacherrole->id);

        $this->setUser($guest);
        $this->assertStringContainsString(get_string('noguestaccess', 'enrol'),
                $tokenplugin->can_self_enrol($instance1, true));

        $this->setUser($user1);
        $this->assertTrue($tokenplugin->can_self_enrol($instance1, true));

        // Active enroled user.
        $this->setUser($user2);
        $tokenplugin->enrol_user($instance1, $user1->id, $studentrole->id);
        $this->setUser($user1);
        $this->assertSame($expectederrorstring, $tokenplugin->can_self_enrol($instance1, true));
    }

    /**
     * Test is_self_enrol_available function behavior.
     *
     * @covers ::is_self_enrol_available
     */
    public function test_is_self_enrol_available() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $this->preventResetByRollback(); // Messaging does not like transactions...
        $this->enable_plugin();

        $tokenplugin = enrol_get_plugin('token');

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        $course = $this->getDataGenerator()->create_course();
        $cohort1 = $this->getDataGenerator()->create_cohort();
        $cohort2 = $this->getDataGenerator()->create_cohort();
        $tokenplugin->add_instance($course, ['roleid' => $studentrole->id]);

        // New enrolments are allowed and enrolment instance is enabled.
        $instance = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'token'], '*', MUST_EXIST);
        $instance->customint6 = 1;
        $DB->update_record('enrol', $instance);
        $tokenplugin->update_status($instance, ENROL_INSTANCE_ENABLED);
        $this->setUser($user1);
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));

        $canntenrolerror = get_string('canntenrol', 'enrol_token');

        // New enrolments are not allowed, but enrolment instance is enabled.
        $instance->customint6 = 0;
        $DB->update_record('enrol', $instance);
        $this->setUser($user1);
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));

        // New enrolments are allowed, but enrolment instance is disabled.
        $instance->customint6 = 1;
        $DB->update_record('enrol', $instance);
        $tokenplugin->update_status($instance, ENROL_INSTANCE_DISABLED);
        $this->setUser($user1);
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));

        // New enrolments are not allowed and enrolment instance is disabled.
        $instance->customint6 = 0;
        $DB->update_record('enrol', $instance);
        $this->setUser($user1);
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));

        // Enable enrolment instance for the rest of the tests.
        $tokenplugin->update_status($instance, ENROL_INSTANCE_ENABLED);

        // Enrol start date is in future.
        $instance->customint6 = 1;
        $instance->enrolstartdate = time() + 60;
        $DB->update_record('enrol', $instance);
        $error = get_string('canntenrolearly', 'enrol_token', userdate($instance->enrolstartdate));
        $this->setUser($user1);
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));

        // Enrol start date is in past.
        $instance->enrolstartdate = time() - 60;
        $DB->update_record('enrol', $instance);
        $this->setUser($user1);
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));

        // Enrol end date is in future.
        $instance->enrolstartdate = 0;
        $instance->enrolenddate = time() + 60;
        $DB->update_record('enrol', $instance);
        $this->setUser($user1);
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));

        // Enrol end date is in past.
        $instance->enrolenddate = time() - 60;
        $DB->update_record('enrol', $instance);
        $error = get_string('canntenrollate', 'enrol_token', userdate($instance->enrolenddate));
        $this->setUser($user1);
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));

        // Maximum enrolments reached.
        $instance->customint3 = 1;
        $instance->enrolenddate = 0;
        $DB->update_record('enrol', $instance);
        $tokenplugin->enrol_user($instance, $user2->id, $studentrole->id);
        $error = get_string('maxenrolledreached', 'enrol_token');
        $this->setUser($user1);
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertEquals($error, $tokenplugin->is_self_enrol_available($instance));

        // Maximum enrolments not reached.
        $instance->customint3 = 3;
        $DB->update_record('enrol', $instance);
        $this->setUser($user1);
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertTrue($tokenplugin->is_self_enrol_available($instance));

        require_once("$CFG->dirroot/cohort/lib.php");
        cohort_add_member($cohort1->id, $user2->id);

        // Cohort test.
        $instance->customint5 = $cohort1->id;
        $DB->update_record('enrol', $instance);
        $error = get_string('cohortnonmemberinfo', 'enrol_token', $cohort1->name);
        $this->setUser($user1);
        $this->assertStringContainsString($error, $tokenplugin->is_self_enrol_available($instance));
        $this->setGuestUser();
        $this->assertStringContainsString($error, $tokenplugin->is_self_enrol_available($instance));
        $this->setUser($user2);
        $this->assertEquals($canntenrolerror, $tokenplugin->is_self_enrol_available($instance));
    }

    /**
     * Test get_welcome_email_contact().
     */
    public function test_get_welcome_email_contact() {
        global $DB;
        self::resetAfterTest(true);
        $this->enable_plugin();

        $user1 = $this->getDataGenerator()->create_user(['lastname' => 'Marsh']);
        $user2 = $this->getDataGenerator()->create_user(['lastname' => 'Victoria']);
        $user3 = $this->getDataGenerator()->create_user(['lastname' => 'Burch']);
        $user4 = $this->getDataGenerator()->create_user(['lastname' => 'Cartman']);
        $noreplyuser = \core_user::get_noreply_user();

        $course1 = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course1->id);

        // Get editing teacher role.
        $editingteacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $this->assertNotEmpty($editingteacherrole);

        // Enable token enrolment plugin and set to send email from course contact.
        $tokenplugin = enrol_get_plugin('token');
        $tokenplugin->add_instance($course1, []);

        $instance1 = $DB->get_record('enrol', ['courseid' => $course1->id, 'enrol' => 'token'], '*', MUST_EXIST);
        $instance1->customint6 = 1;
        $instance1->customint4 = ENROL_SEND_EMAIL_FROM_COURSE_CONTACT;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->update_status($instance1, ENROL_INSTANCE_ENABLED);

        // We do not have a teacher enrolled at this point, so it should send as no reply user.
        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($noreplyuser, $contact);

        // By default, course contact is assigned to teacher role.
        // Enrol a teacher, now it should send emails from teacher email's address.
        $tokenplugin->enrol_user($instance1, $user1->id, $editingteacherrole->id);

        // We should get the teacher email.
        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($user1->username, $contact->username);
        $this->assertEquals($user1->email, $contact->email);

        // Now let's enrol another teacher.
        $tokenplugin->enrol_user($instance1, $user2->id, $editingteacherrole->id);
        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_COURSE_CONTACT, $context);
        $this->assertEquals($user1->username, $contact->username);
        $this->assertEquals($user1->email, $contact->email);

        // Get manager role, and enrol user as manager.
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $this->assertNotEmpty($managerrole);
        $instance1->customint4 = ENROL_SEND_EMAIL_FROM_KEY_HOLDER;
        $DB->update_record('enrol', $instance1);
        $tokenplugin->enrol_user($instance1, $user3->id, $managerrole->id);

        // Give manager role holdkey capability.
        assign_capability('enrol/token:holdkey', CAP_ALLOW, $managerrole->id, $context);

        // We should get the manager email contact.
        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_KEY_HOLDER, $context);
        $this->assertEquals($user3->username, $contact->username);
        $this->assertEquals($user3->email, $contact->email);

        // Now let's enrol another manager.
        $tokenplugin->enrol_user($instance1, $user4->id, $managerrole->id);
        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_KEY_HOLDER, $context);
        $this->assertEquals($user3->username, $contact->username);
        $this->assertEquals($user3->email, $contact->email);

        $instance1->customint4 = ENROL_SEND_EMAIL_FROM_NOREPLY;
        $DB->update_record('enrol', $instance1);

        $contact = $tokenplugin->get_welcome_email_contact(ENROL_SEND_EMAIL_FROM_NOREPLY, $context);
        $this->assertEquals($noreplyuser, $contact);
    }

    /**
     * Test for getting user enrolment actions.
     */
    public function test_get_user_enrolment_actions() {
        global $CFG, $PAGE;
        $this->resetAfterTest();

        // Set page URL to prevent debugging messages.
        $PAGE->set_url('/enrol/editinstance.php');

        $pluginname = 'token';
        $tokenplugin = enrol_get_plugin('token');

        // Only enable the token enrol plugin.
        $CFG->enrol_plugins_enabled = $pluginname;

        $generator = $this->getDataGenerator();

        // Get the enrol plugin.
        $plugin = enrol_get_plugin($pluginname);

        // Create a course.
        $course = $generator->create_course();
        $tokenplugin->add_instance($course, []);

        // Create a teacher.
        $teacher = $generator->create_user();
        // Enrol the teacher to the course.
        $enrolresult = $generator->enrol_user($teacher->id, $course->id, 'editingteacher', $pluginname);
        $this->assertTrue($enrolresult);
        // Create a student.
        $student = $generator->create_user();
        // Enrol the student to the course.
        $enrolresult = $generator->enrol_user($student->id, $course->id, 'student', $pluginname);
        $this->assertTrue($enrolresult);

        // Login as the teacher.
        $this->setUser($teacher);
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $manager = new \course_enrolment_manager($PAGE, $course);
        $userenrolments = $manager->get_user_enrolments($student->id);
        $this->assertCount(1, $userenrolments);

        $ue = reset($userenrolments);
        $actions = $plugin->get_user_enrolment_actions($manager, $ue);
        // Token enrol has 2 enrol actions -- edit and unenrol.
        $this->assertCount(2, $actions);
    }
}
