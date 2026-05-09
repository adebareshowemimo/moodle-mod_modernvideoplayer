@mod @mod_modernvideoplayer
Feature: Add a Modern video player activity
  In order to deliver video content with focus enforcement
  As a teacher
  I need to add a Modern video player activity to my course

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

  Scenario: Teacher can create a Modern video player activity via the data generator
    Given the following "activities" exist:
      | activity         | course | name             | intro                        |
      | modernvideoplayer | C1     | Week 1 lecture   | Watch this week's lecture.   |
    When I am on the "Week 1 lecture" "modernvideoplayer activity" page logged in as teacher1
    Then I should see "Week 1 lecture"
    And I should see "No video file has been uploaded for this activity yet."
