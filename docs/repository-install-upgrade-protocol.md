# Repository Install and Upgrade Protocol

Last updated: 2026-04-24

This protocol is for the final Moodle Plugins Directory submission checks that still require a clean Moodle environment.

Use it with the package:

- `C:\xampp\htdocs\premium-moodlle\mod\modernvideoplayer-plugin-release.zip`

## 1. Clean Install Protocol

### Goal

Confirm that the plugin installs cleanly from the ZIP package on a fresh Moodle site with developer debugging enabled.

### Environment

Prepare a clean Moodle instance with:

- Moodle version inside the declared support range
- developer debugging enabled
- no existing `mod_modernvideoplayer` installation

Recommended:

- one MariaDB/MySQL environment
- one PostgreSQL environment

### Steps

1. Start from a clean Moodle codebase and clean database.
2. Enable full developer debugging.
3. Install the plugin from:
   - `modernvideoplayer-plugin-release.zip`
4. Visit site notifications and complete installation.
5. Confirm there are:
   - no XMLDB warnings
   - no missing-string errors
   - no install exceptions
   - no upgrade-step notices for this release
6. Go to:
   - `Site administration -> Plugins -> Activity modules -> Modern video player`
7. Confirm admin settings load normally.
8. Create a course and add a `Modern video player` activity.
9. Confirm the activity form loads and saves normally.

### Pass Criteria

- plugin installs from ZIP without manual patching
- admin settings page renders
- activity form renders and saves
- no debugging errors or warnings during install

## 2. Previous-Version Upgrade Protocol

### Goal

Confirm that upgrading from the previous public plugin release preserves schema and functionality.

### Required Input

You need one of:

- the previous plugin release ZIP
- a git tag or archive for the previous public release

### Steps

1. Install the previous plugin release on a clean Moodle site.
2. Create at least one course activity with representative settings:
   - completion rules
   - Focus Mode / navigation settings
   - captions / chapters if available
3. Add representative learner data if possible:
   - progress rows
   - bookmarks
4. Replace the old plugin with:
   - `modernvideoplayer-plugin-release.zip`
5. Visit site notifications and run the upgrade.
6. Confirm there are:
   - no schema failures
   - no missing-field errors
   - no string exceptions
   - no capability or privacy-provider regressions
7. Open the existing activity and confirm it still works.

### Pass Criteria

- upgrade completes without manual DB intervention
- prior activity instances still open
- existing settings remain readable
- existing learner data remains readable

## 3. Browser Smoke Test Protocol

### Goal

Confirm the main learner and teacher flows in real browsers.

### Minimum Browser Matrix

- Chrome on Windows or macOS
- Edge on Windows
- Safari on iPhone or iPad
- Chrome on Android

### Learner Flow

1. Open the activity with a Moodle-managed uploaded video.
2. Confirm the video plays.
3. Watch long enough for progress to update.
4. Leave and re-open the activity.
5. Confirm resume is offered.
6. Resume and confirm progress continues.
7. Confirm completion updates when the configured threshold is met.

### Teacher Flow

1. Open the activity settings.
2. Confirm completion rules appear in Moodle Activity Completion.
3. Confirm navigation/drawer settings save and affect the player view.
4. Open the report page.
5. Confirm learner progress and CSV export work.

### Media Feature Checks

Where configured, also verify:

- captions
- transcript panel
- chapter markers
- Picture-in-Picture
- fullscreen
- focus-mode behavior

## 4. Submission Evidence to Keep

Capture and keep:

- install success screenshots
- upgrade success screenshots
- one screenshot of the activity settings form
- one screenshot of the learner player
- one screenshot of the report page
- notes for any browser-specific limitations

## 5. Final Release Decision

Repository submission is ready only when all of the following are true:

- clean ZIP install succeeds
- previous-version upgrade succeeds
- browser smoke tests succeed for the core flow
- repository listing text matches the shipped feature set
