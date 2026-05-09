/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Playback speed menu for the modern video player.
 *
 * Renders a dropdown of playback rates honoring the per-instance
 * allowplaybackspeed and maxplaybackspeed caps. Rates above the cap
 * are filtered out; 1.0x is always included.
 *
 * The enforcer module still clamps any programmatic override, so this
 * menu is purely a UX affordance.
 *
 * @module     mod_modernvideoplayer/speedmenu
 * @copyright  2026 Adebare Showemimo <adebareshowemimo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const AVAILABLE_RATES = [0.5, 0.75, 1.0, 1.25, 1.5, 1.75, 2.0];

const formatRate = (rate) => {
    const rounded = Math.round(rate * 100) / 100;
    if (Number.isInteger(rounded)) {
        return rounded + 'x';
    }
    return rounded.toString().replace(/\.?0+$/, '') + 'x';
};

/**
 * Initialise the playback speed menu.
 *
 * @param {HTMLElement} root player root
 * @param {HTMLVideoElement} video the <video> element
 * @param {object} state progress state (allowplaybackspeed, maxplaybackspeed)
 * @param {object} strings localised strings
 * @returns {void}
 */
export const init = (root, video, state, strings) => {
    const button = root.querySelector('[data-action="toggle-speed"]');
    const menu = root.querySelector('[data-region="speed-menu"]');
    const currentLabel = root.querySelector('[data-region="speed-current"]');

    if (!button || !menu) {
        return;
    }

    if (!state.allowplaybackspeed) {
        button.classList.add('d-none');
        return;
    }

    const cap = state.maxplaybackspeed && state.maxplaybackspeed > 0 ? state.maxplaybackspeed : 2.0;
    const allowedRates = AVAILABLE_RATES.filter((r) => r <= cap + 0.001);

    // Build menu items.
    while (menu.firstChild) {
        menu.removeChild(menu.firstChild);
    }
    allowedRates.forEach((rate) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'dropdown-item modernvideoplayer-speedmenu__item';
        item.dataset.rate = String(rate);
        item.textContent = formatRate(rate);
        if (rate === 1.0) {
            item.textContent = formatRate(rate) + ' (' + (strings.speednormal || 'Normal') + ')';
        }
        menu.appendChild(item);
    });

    button.classList.remove('d-none');

    const setCurrent = (rate) => {
        if (currentLabel) {
            currentLabel.textContent = formatRate(rate);
        }
        menu.querySelectorAll('.modernvideoplayer-speedmenu__item').forEach((el) => {
            el.classList.toggle('modernvideoplayer-speedmenu__item--active',
                Math.abs(parseFloat(el.dataset.rate) - rate) < 0.001);
        });
    };

    const closeMenu = () => {
        menu.classList.add('d-none');
        button.setAttribute('aria-expanded', 'false');
    };

    const openMenu = () => {
        menu.classList.remove('d-none');
        button.setAttribute('aria-expanded', 'true');
    };

    button.addEventListener('click', (ev) => {
        ev.stopPropagation();
        if (menu.classList.contains('d-none')) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    menu.addEventListener('click', (ev) => {
        const item = ev.target.closest('.modernvideoplayer-speedmenu__item');
        if (!item) {
            return;
        }
        const rate = parseFloat(item.dataset.rate);
        if (Number.isFinite(rate) && rate > 0) {
            video.playbackRate = rate;
        }
        closeMenu();
    });

    // Close on outside click.
    document.addEventListener('click', (ev) => {
        if (!menu.classList.contains('d-none') && !menu.contains(ev.target) && ev.target !== button) {
            closeMenu();
        }
    });

    // Close on Escape.
    document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape' && !menu.classList.contains('d-none')) {
            closeMenu();
            button.focus();
        }
    });

    // Keep the current label in sync (covers enforcer clamping too).
    video.addEventListener('ratechange', () => {
        setCurrent(video.playbackRate || 1);
    });

    setCurrent(video.playbackRate || 1);
};
