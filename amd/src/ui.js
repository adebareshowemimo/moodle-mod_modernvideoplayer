/**
 * UI helper methods for the modern video player.
 *
 * @module     mod_modernvideoplayer/ui
 * @copyright  2025 Adebare Showemmo | adebareshowemimo@gmail.com | support@agunfoninteractivity.com | www.agunfoninteractivity.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const updatePercent = (root, currentPercent, completionPercent = currentPercent, isCompleted = false) => {
    const label = root.querySelector('[data-region="percent-complete"]');
    const badge = root.querySelector('[data-region="completion-badge"]');
    const progress = root.querySelector('[data-region="verified-progress"]');
    const thumb = root.querySelector('[data-region="timeline-thumb"]');
    const safeCurrent = Math.max(0, Math.min(100, currentPercent));
    const safeCompletion = Math.max(0, Math.min(100, completionPercent));

    if (label) {
        label.textContent = `${Math.round(safeCompletion)}%`;
        label.classList.toggle('d-none', isCompleted);
    }
    if (badge) {
        badge.classList.toggle('d-none', !isCompleted);
    }
    if (progress) {
        progress.style.width = `${safeCurrent}%`;
        progress.setAttribute('aria-valuenow', `${Math.round(safeCurrent)}`);
    }
    if (thumb) {
        thumb.style.left = `${safeCurrent}%`;
        thumb.classList.toggle('d-none', safeCurrent <= 0);
    }
};

export const updateBuffered = (root, percent) => {
    const label = root.querySelector('[data-region="buffered"]');
    const progress = root.querySelector('[data-region="buffered-progress"]');

    if (label) {
        label.textContent = `${Math.round(percent)}%`;
    }
    if (progress) {
        progress.style.width = `${Math.max(0, Math.min(100, percent))}%`;
        progress.setAttribute('aria-valuenow', `${Math.round(percent)}`);
    }
};

export const updateTime = (root, current, duration) => {
    const label = root.querySelector('[data-region="playback-time"]');
    if (!label) {
        return;
    }

    const currentLabel = formatTime(current);
    const durationLabel = duration && Number.isFinite(duration) ? formatTime(duration) : '0:00';
    label.textContent = `${currentLabel} / ${durationLabel}`;
};

const BI_SVG_OPEN = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"'
    + ' fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">';

const ICON_PLAY = BI_SVG_OPEN
    + '<path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308'
    + 'c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393"/></svg>';

const ICON_PAUSE = BI_SVG_OPEN
    + '<path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5'
    + 'm5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5"/></svg>';

const ICON_VOLUME_UP = BI_SVG_OPEN
    + '<path d="M11.536 14.01A8.47 8.47 0 0 0 14.026 8a8.47 8.47 0 0 0-2.49-6.01l-.708.707'
    + 'A7.48 7.48 0 0 1 13.025 8c0 2.071-.84 3.946-2.197 5.303z"/>'
    + '<path d="M10.121 12.596A6.48 6.48 0 0 0 12.025 8a6.48 6.48 0 0 0-1.904-4.596l-.707.707'
    + 'A5.48 5.48 0 0 1 11.025 8a5.48 5.48 0 0 1-1.61 3.89z"/>'
    + '<path d="M8.707 11.182A4.5 4.5 0 0 0 10.025 8a4.5 4.5 0 0 0-1.318-3.182L8 5.525'
    + 'A3.5 3.5 0 0 1 9.025 8 3.5 3.5 0 0 1 8 10.475zM6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39'
    + 'L3.825 10.5H1.5A.5.5 0 0 1 1 10V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06"/></svg>';

const ICON_VOLUME_MUTE = BI_SVG_OPEN
    + '<path d="M6.717 3.55A.5.5 0 0 1 7 4v8a.5.5 0 0 1-.812.39L3.825 10.5H1.5A.5.5 0 0 1 1 10'
    + 'V6a.5.5 0 0 1 .5-.5h2.325l2.363-1.89a.5.5 0 0 1 .529-.06'
    + 'm7.137 2.096a.5.5 0 0 1 0 .708L12.207 8l1.647 1.646a.5.5 0 0 1-.708.708L11.5 8.707'
    + 'l-1.646 1.647a.5.5 0 0 1-.708-.708L10.793 8 9.146 6.354a.5.5 0 1 1 .708-.708L11.5 7.293'
    + 'l1.646-1.647a.5.5 0 0 1 .708 0"/></svg>';

export const updatePlayState = (root, paused, strings) => {
    const icon = root.querySelector('[data-region="play-icon"]');
    const label = root.querySelector('[data-region="play-label"]');

    if (icon) {
        icon.innerHTML = paused ? ICON_PLAY : ICON_PAUSE;
    }
    if (label) {
        label.textContent = paused ? strings.play : strings.pause;
    }
};

export const updateMuteState = (root, muted, strings) => {
    const icon = root.querySelector('[data-region="mute-icon"]');
    const label = root.querySelector('[data-region="mute-label"]');

    if (icon) {
        icon.innerHTML = muted ? ICON_VOLUME_MUTE : ICON_VOLUME_UP;
    }
    if (label) {
        label.textContent = muted ? strings.unmute : strings.mute;
    }
};

export const formatTime = (seconds) => {
    const total = Math.max(0, Math.floor(seconds || 0));
    const hrs = Math.floor(total / 3600);
    const mins = Math.floor((total % 3600) / 60);
    const secs = total % 60;

    if (hrs > 0) {
        return `${hrs}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    return `${mins}:${String(secs).padStart(2, '0')}`;
};

export const showResumeOverlay = (root, heading, subtitle, options = {}) => {
    const overlay = root.querySelector('[data-region="resume-overlay"]');
    const headingEl = root.querySelector('[data-region="resume-heading"]');
    const subtitleEl = root.querySelector('[data-region="resume-subtitle"]');
    const eyebrowEl = root.querySelector('[data-region="resume-eyebrow"]');
    const buttonEl = root.querySelector('[data-action="resume-playback"]');
    const restartEl = root.querySelector('[data-action="restart-playback"]');
    const nextEl = root.querySelector('[data-action="resume-next-activity"]');

    if (headingEl) {
        headingEl.textContent = heading;
    }
    if (subtitleEl) {
        subtitleEl.textContent = subtitle;
    }
    if (eyebrowEl && typeof options.eyebrow === 'string') {
        eyebrowEl.textContent = options.eyebrow;
    }
    if (buttonEl && typeof options.buttonLabel === 'string') {
        buttonEl.textContent = options.buttonLabel;
    }
    // Both variants of this overlay share the same DOM, so the optional buttons are
    // toggled per-call. The completed-replay variant suppresses "Start from beginning"
    // (server-side reset) since completion is being preserved, and reveals the
    // "next-activity" CTA. The in-progress resume variant gets neither override.
    if (restartEl) {
        restartEl.classList.toggle('d-none', options.showRestart === false);
    }
    if (nextEl) {
        nextEl.classList.toggle('d-none', !options.showNextActivity);
    }
    if (overlay) {
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');
    }
};

export const hideResumeOverlay = (root) => {
    const overlay = root.querySelector('[data-region="resume-overlay"]');
    if (overlay) {
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
    }
};

export const showNextActivityOverlay = (root) => {
    const overlay = root.querySelector('[data-region="next-activity-overlay"]');
    if (overlay) {
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');
    }
};

export const hideNextActivityOverlay = (root) => {
    const overlay = root.querySelector('[data-region="next-activity-overlay"]');
    if (overlay) {
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
    }
};
