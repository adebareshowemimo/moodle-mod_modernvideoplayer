@mod @mod_modernvideoplayer
Feature: Focus Mode enforcement settings
  In order to keep learners focused on the video
  As a teacher
  I need to be able to enable Focus Mode and toggle Picture-in-Picture, captions, and transcript download

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity          | course | name       | idnumber  |
      | modernvideoplayer | C1     | Focus demo | focusdemo |

  Scenario: The activity editing form exposes the media toggles with the expected defaults
    When I am on the "focusdemo" "Activity editing" page logged in as "admin"
    And I expand all fieldsets
    Then the field "enforcefocus" matches value "0"
    And the field "allowpip" matches value "1"
    And the field "allowcaptions" matches value "1"
    And the field "allowtranscriptdownload" matches value "1"
