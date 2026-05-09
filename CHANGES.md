# Changes

User-facing release notes for `mod_modernvideoplayer`.

The canonical, full changelog lives in [CHANGELOG.md](CHANGELOG.md) — this file
exists because the Moodle Plugins Directory uploader prefills release notes
from a file named exactly `CHANGES.md` / `CHANGES` / `CHANGES.txt`.

## 1.3.5 - 2026-05-09

### Fixed
- Removed duplicate manual loading of the plugin `styles.css` file.

## 1.3.4 - 2026-05-09

### Fixed
- Added Moodle boilerplate license headers to stylesheet, templates, and AMD
  JavaScript source/build files.

## 1.3.3 - 2026-05-09

### Fixed
- Added explicit parameter, context, and capability checks to the
  mark-complete external service.

## 1.3.2 - 2026-05-09

### Changed
- Renamed the source repository to `moodle-mod_modernvideoplayer` to match
  Moodle's recommended repository naming convention.
- Updated public GitHub links in README, contribution notes, issue templates,
  and Moodle submission documentation.

## 1.3.1 - 2026-05-09

### Fixed
- Fixed Moodle 4.5 PHPUnit compatibility in next-activity resolution.
- Fixed Moodle codechecker formatting failures across the release changes.

## 1.3.0 - 2026-05-09

### Added
- End-of-video "Next activity" overlay with Replay and Continue actions.
- Per-activity Continue target settings: automatic next activity in course order
  or a manually selected activity.
- New `mod_modernvideoplayer_get_next_activity` AJAX web service.
- Completion badge in the custom player controls.
- Moodle 5.2 support declaration while keeping Moodle 4.5 as the minimum
  supported version.

### Changed
- Teacher report now uses Moodle `table_sql` for pagination, sortable columns,
  and downloadable exports.
- Report KPI totals now use aggregate SQL that respects the active report
  filters.
- Completed-video resume now opens a replay prompt instead of resuming at the
  final timestamp.
- Seekbar drag and click behavior now updates the UI immediately while still
  respecting validated seek limits.

### Fixed
- Completion-gated next activities can unlock immediately after video completion
  because Moodle completion is synced before resolving the Continue target.
- Fullscreen fallback support improved for WebKit/iOS Safari.

## 1.2.0 — 2026-04-23

### Fixed
- Privacy API now covers the `modernvideoplayer_bookmarks` table. Learner
  bookmarks are included in subject-access exports and are deleted by all four
  privacy delete paths (per-user, per-userlist, per-context,
  delete-all-in-context).
- Backup and restore now round-trip learner bookmarks. Previously bookmarks
  were silently dropped on course duplicate / course restore.

### Added
- `$plugin->supported = [405, 502]` declared in `version.php` so the Plugins
  Directory auto-publishes the supported Moodle version range.
- Privacy metadata language strings for the bookmarks table.

## 1.1.0 — 2026-04-23

- First stable release (`MATURITY_STABLE`).
- See [CHANGELOG.md](CHANGELOG.md) for the per-release breakdown of the
  0.5.x — 0.11.x feature line (playback UI, captions/transcript/chapters,
  Focus Mode enforcement, integrity, progress & completion, gradebook,
  bookmarks, reporting, privacy, backup/restore, mobile hooks, PHPUnit and
  Behat coverage).
