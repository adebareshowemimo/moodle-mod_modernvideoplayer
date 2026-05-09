# docs.moodle.org wiki page — mod_modernvideoplayer

Paste the content below into a new page on https://docs.moodle.org titled
**"Modern video player"**. The Infobox is the required submission artefact;
the remaining sections match the structure of comparable activity modules
(e.g. `mod_quiz`, `mod_hvp`).

---

{{Infobox Plugin
|type = Activity module
|entry = Modern video player
|tab = Activities
|tab2 =
|tab3 =
|icon =
|image =
|description = A modern, privacy-first HTML5 video activity with heartbeat-validated progress, merged watched segments, focus mode, bookmarks, captions, chapters, gradebook integration and full backup/restore.
|issues = https://github.com/adebareshowemimo/mod_modernvideoplayer/issues
|discussion =
|maintainer = [[User:Adebare Showemimo|Adebare Showemimo]]
|contributors =
|moodle = 4.5, 5.0, 5.1, 5.2
|php = 8.1, 8.2, 8.3
|plugins directory = https://moodle.org/plugins/mod_modernvideoplayer
|source control = https://github.com/adebareshowemimo/mod_modernvideoplayer
|translations =
|tracker = https://github.com/adebareshowemimo/mod_modernvideoplayer/issues
|documentation = https://github.com/adebareshowemimo/mod_modernvideoplayer#readme
|release = 1.2.0
|dependencies =
|license = GPL-3.0-or-later
}}

The **Modern video player** activity (`mod_modernvideoplayer`) is a native
Moodle activity module that plays HTML5 video with server-side validated
progress tracking. It is designed as a privacy-first, no-lock-in
alternative to embedded third-party video platforms.

== Features ==

; Playback
: HTML5 `<video>` with keyboard shortcuts, playback-rate control, picture-in-picture, chapters and WebVTT captions/transcript.

; Progress tracking
: Heartbeat-based, server-validated progress. The server rejects suspicious seeks and fabricated completion attempts. Watched segments are merged and stored separately from the row-level progress record.

; Focus Mode
: Optional enforcement that disables picture-in-picture, playback-rate changes and transcript download for an individual activity.

; Bookmarks
: Learners can mark timestamps with a label and jump back to them later.

; Completion
: Native activity completion supports validated watch-percentage and validated end-of-video rules. The watched-percentage rule uses validated coverage, not elapsed time.

; Gradebook
: Optional numeric grade pushed to the gradebook on completion.

; Backup & restore
: Full activity config plus learner progress, segments and bookmarks. Backups round-trip across sites.

; Privacy
: Full Moodle Privacy API provider (metadata, userlist, plugin provider) with subject-access export and delete for all user data.

; Reporting
: Per-activity report of learner progress, percent complete, suspicious flag count, and last heartbeat.

== Installation ==

# Download the latest release ZIP from the Moodle Plugins Directory or the [https://github.com/adebareshowemimo/mod_modernvideoplayer GitHub releases page].
# In your Moodle site go to ''Site administration → Plugins → Install plugins'' and upload the ZIP, or unpack it under `mod/modernvideoplayer/` on the server.
# Visit ''Site administration → Notifications'' to run the installer.
# Review the site-wide defaults at ''Site administration → Plugins → Activity modules → Modern video player''.

== Using Modern video player ==

Teachers add an instance by turning editing on in a course and choosing
''Add an activity or resource → Modern video player''. The form exposes:

* Video file (uploaded to Moodle filestore; MP4 / WebM)
* Poster image, captions (.vtt), chapters (.vtt) — all optional
* Playback and enforcement settings, including Focus Mode related controls
* Completion rules for watched percentage and validated video ending
* Focused-view navigation and drawer visibility controls
* Grade (numeric, optional)

== Privacy and GDPR ==

The plugin stores only:

* `modernvideoplayer_progress` — per-user progress row (session token, validated position, max verified position, percent complete, completion flag, timestamps)
* `modernvideoplayer_segments` — merged validated watched intervals, keyed to a progress row
* `modernvideoplayer_bookmarks` — learner-authored timestamp bookmarks

All three tables are covered by the Privacy API provider and are deleted on
user-delete, context-delete, and userlist-delete paths.

The plugin makes no outbound network calls and does not embed any
third-party tracking or analytics code.

== Capabilities ==

* `mod/modernvideoplayer:addinstance` — create an activity
* `mod/modernvideoplayer:view` — view the player
* `mod/modernvideoplayer:submitprogress` — record validated playback progress
* `mod/modernvideoplayer:viewreports` — view the per-activity report
* `mod/modernvideoplayer:manage` — manage activity configuration and teacher controls

== See also ==

* [https://moodle.org/plugins/mod_modernvideoplayer Plugin page on the Moodle Plugins Directory]
* [https://github.com/adebareshowemimo/mod_modernvideoplayer Source code on GitHub]

[[Category:Activity]]
[[Category:Contributed code]]
