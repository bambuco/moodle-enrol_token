@enrol @enrol_token
Feature: Users can auto-enrol themtoken in courses where token enrolment is allowed
  In order to participate in courses
  As a user
  I need to auto enrol me in courses

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Token enrolment" "table_row"
    And I am on course index

  # Note: Please keep the javascript tag on this Scenario to ensure that we
  # test use of the singleselect functionality.
  @javascript
  Scenario: Token-enrolment enabled as guest
    Given I log in as "teacher1"
    And I add "Token enrolment" enrolment method in "Course 1" with:
      | Custom instance name | Test student enrolment |
    And I log out
    When I am on "Course 1" course homepage
    And I press "Access as a guest"
    Then I should see "Guests cannot access this course. Please log in."
    And I press "Continue"
    And I should see "Log in"

  Scenario: Token-enrolment enabled requiring an enrolment token
    Given I log in as "teacher1"
    When I add "Token enrolment" enrolment method in "Course 1" with:
      | Custom instance name | Test student enrolment |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I set the following fields to these values:
      | Enrolment token | 123456ABC |
    And I press "Enrol me"
    Then I should see "Incorrect enrolment token, please try again"
    And I should see "Test student enrolment"
    And I should not see "Topic 1"

  Scenario: Token-enrolment disabled
    Given I log in as "student1"
    When I am on "Course 1" course homepage
    Then I should see "You cannot enrol yourself in this course"
