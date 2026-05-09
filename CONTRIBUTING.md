# Contributing to `mod_modernvideoplayer`

Thank you for taking the time to contribute! This plugin is a community
project, and your input — bug reports, ideas, code, docs, tests — makes it
better for every Moodle site that uses it.

## Ways to contribute

- **Report a bug** — open a [GitHub Issue](https://github.com/adebareshowemimo/moodle-mod_modernvideoplayer/issues/new/choose) using the *Bug report* template.
- **Suggest a feature** — open a [Discussion](https://github.com/adebareshowemimo/moodle-mod_modernvideoplayer/discussions) first so we can scope it.
- **Improve docs** — PRs against `README.md` or the docs folder are always welcome.
- **Fix code** — see "Development setup" below.

## Development setup

```bash
# 1. Fork, then clone into your Moodle install
cd /path/to/moodle/mod
git clone git@github.com:<you>/modernvideoplayer.git

# 2. Create a feature branch
cd modernvideoplayer
git checkout -b feature/my-change

# 3. Install Moodle dev deps if you haven't already
cd /path/to/moodle
composer install
npm ci

# 4. Build AMD
npx grunt amd --root=mod/modernvideoplayer

# 5. Run upgrade after DB changes
php admin/cli/upgrade.php
```

## Coding standards

We follow the [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle).
`moodle-plugin-ci` enforces it on every PR.

- 4-space indentation (PHP), no tabs
- PHPDoc on every public class / method
- Language strings in `lang/en/modernvideoplayer.php` — never hardcode user-facing text
- Database changes: update `db/install.xml`, add a savepoint block in `db/upgrade.php`, bump `version.php`
- New capabilities: add to `db/access.php` + document in `README.md`
- Privacy: if you store new user data, update `classes/privacy/provider.php`

## Commit messages

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
feat(player): add captions CC button
fix(backup): restore autoplay setting for child courses
docs(readme): clarify install steps
test(completion): add phpunit coverage for partial watches
```

## Pull request checklist

Before requesting review:

- [ ] Branch based on `develop`, rebased on latest `develop`
- [ ] `moodle-plugin-ci` passes locally (`phplint`, `codechecker`, `validate`, `savepoints`, `mustache`, `grunt`)
- [ ] New behaviour covered by PHPUnit and/or Behat tests
- [ ] Language strings added (English) — translations come later
- [ ] `version.php` bumped if DB/lib changed
- [ ] Privacy provider updated if user data touched
- [ ] `CHANGELOG.md` entry under `## [Unreleased]`
- [ ] CLA signed (the bot will prompt on first PR)

## Contributor License Agreement

We use a lightweight CLA so the project can stay dual-distributable (community
plugin on moodle.org + private commercial add-ons that extend it). Your
contribution remains under GPLv3; the CLA simply confirms you have the right
to submit it. The [cla-assistant](https://cla-assistant.io) bot will comment
on your first PR with a one-click sign-off.

## Where to ask questions

- [GitHub Discussions](https://github.com/adebareshowemimo/moodle-mod_modernvideoplayer/discussions) — ideas, help, show-and-tell
- [GitHub Issues](https://github.com/adebareshowemimo/moodle-mod_modernvideoplayer/issues) — bugs only
- Moodle forums — general Moodle help

## Code of Conduct

Participation is governed by [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md). Be kind.
