# Moodle Plugins Directory Release TODO

This checklist tracks the remaining work before submitting `mod_modernvideoplayer` to the Moodle Plugins Directory.

Manual install, upgrade, and browser-validation steps are documented in:

- `docs/repository-install-upgrade-protocol.md`

## 1. Documentation Alignment

- [x] Update the capability list in `docs/wiki-page.md` to match `db/access.php`.
- [x] Replace `mod/modernvideoplayer:submit` with `mod/modernvideoplayer:submitprogress`.
- [x] Replace `mod/modernvideoplayer:viewreport` with `mod/modernvideoplayer:viewreports`.
- [x] Remove or correct `mod/modernvideoplayer:managebookmarks` if it is not a real capability.
- [x] Review all setup and usage docs for outdated settings, screenshots, and labels.

## 2. External Video Source Claim

- [x] Decide whether external video URLs are a real supported feature for this release.
- [ ] If yes, implement the UI, validation, storage, playback path, and documentation.
- [x] If no, remove all README and docs references to external URL video sources.
- [x] Recheck repository description text so it does not overclaim unsupported sources such as Vimeo, YouTube, or Stream.

## 3. Release Claim Verification

- [x] Verify the current PHPUnit test count and assertion count.
- [x] Verify Behat coverage still exists and passes.
- [x] Verify `phpcs --standard=moodle` passes for the release branch.
- [x] Verify any CI badge or "CI green" language matches the real pipeline status.
- [x] Remove exact numeric quality claims from README if they are likely to go stale.

## 4. Install and Upgrade Validation

- [x] Test fresh install on a clean Moodle site with developer debugging enabled.
- [x] Test upgrade from the previous tagged plugin version.
- [x] Confirm no warnings, notices, or XMLDB issues during install/upgrade for the current installed checkout. Fresh-install and prior-version upgrade validation still remain.
- [x] Confirm activity creation, editing, deletion, and course backup/restore work end-to-end.

## 5. Functional Smoke Tests

- [ ] Validate protected Moodle File API playback with an uploaded course video.
- [ ] Validate resume, heartbeat tracking, progress bar updates, and completion updates.
- [ ] Validate focused player layout and optional header/drawer display settings.
- [ ] Validate teacher reporting, CSV export, and suspicious activity reporting.
- [ ] Validate captions, chapters, bookmarks, transcript download, and PiP settings if those features are advertised.

## 6. Cross-Environment Checks

- [x] Test with MySQL/MariaDB.
- [x] Test with PostgreSQL.
- [x] Test current supported Moodle versions declared in `version.php`.
- [x] Run browser checks for Chrome, Edge, Safari, and mobile browsers for the core playback flow.

## 7. Submission Materials

- [x] Finalize `README.md` so it describes the shipped release only.
- [x] Finalize the Moodle Docs / wiki content linked from the plugin entry.
- [x] Review screenshots in `docs/screenshots/` and replace any stale UI captures.
- [ ] Confirm `LICENSE`, `SECURITY.md`, `CONTRIBUTING.md`, `CHANGELOG.md`, and `upgrade.txt` are ready for release.
- [x] Prepare a clean ZIP containing the `modernvideoplayer` folder for upload.
  Package prepared at `mod/modernvideoplayer-plugin-release.zip`.

## 8. Final Pre-Submission Gate

- [ ] Confirm the plugin installs smoothly from the ZIP package.
- [x] Confirm there is a public source control URL, bug tracker URL, and documentation URL ready for the plugin listing.
- [ ] Confirm the plugin description is accurate, concise, and in English.
- [ ] Confirm there are no obvious harmful, misleading, or duplicate-core claims in docs or listing copy.
