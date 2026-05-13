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
 * Heartbeat tracking for the modern video player.
 *
 * @module     mod_modernvideoplayer/tracker
 * @copyright  2025 Adebare Showemmo | adebareshowemimo@gmail.com | support@agunfoninteractivity.com | www.agunfoninteractivity.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

const heartbeatRequest = (config, video, state) => Ajax.call([{
    methodname: 'mod_modernvideoplayer_heartbeat',
    args: {
        cmid: config.cmid,
        currenttime: video.currentTime,
        duration: video.duration || 0,
        playing: !video.paused,
        playbackrate: video.playbackRate,
        visibility: document.visibilityState === 'hidden' ? 'hidden' : 'visible',
        sessiontoken: state.sessiontoken
    }
}])[0];

export const markComplete = (
    config,
    video,
    state,
    currentTime = video.currentTime,
    duration = video.duration || 0
) => Ajax.call([{
    methodname: 'mod_modernvideoplayer_mark_complete',
    args: {
        cmid: config.cmid,
        currenttime: currentTime,
        duration,
        sessiontoken: state.sessiontoken
    }
}])[0];

export const start = (config, video, state, onUpdate, onComplete = null) => {
    const interval = Math.max(5000, (state.heartbeatinterval || 15) * 1000);
    let inflight = false;
    let lastsentat = 0;
    let lastplaybacksync = 0;

    const tick = (force = false) => {
        const now = Date.now();
        if (inflight) {
            return Promise.resolve();
        }
        if (!force && (now - lastsentat) < 2000) {
            return Promise.resolve();
        }

        inflight = true;
        lastsentat = now;

        return heartbeatRequest(config, video, state).then((response) => {
            state.allowedposition = response.allowedposition;
            state.maxverifiedposition = response.maxverifiedposition;
            state.percentcomplete = response.percentcomplete;
            state.completed = response.completed;
            state.sessiontoken = response.sessiontoken;
            onUpdate(response);
            return undefined;
        }).catch(() => {
            window.console.warn(config.strings.progressunavailable);
        }).then(() => {
            inflight = false;
            return undefined;
        });
    };

    const timer = window.setInterval(() => {
        void tick();
    }, interval);

    const syncNow = () => {
        void tick(true);
    };

    const syncOnHide = () => {
        if (document.visibilityState === 'hidden') {
            syncNow();
        }
    };

    const syncOnPause = () => {
        syncNow();
    };

    const syncOnPlay = () => {
        syncNow();
    };

    const syncOnSeeked = () => {
        syncNow();
    };

    const syncWhilePlaying = () => {
        if (video.paused) {
            return;
        }

        const now = Date.now();
        if ((now - lastplaybacksync) >= 2500) {
            lastplaybacksync = now;
            void tick();
        }
    };

    video.addEventListener('play', syncOnPlay);
    video.addEventListener('pause', syncOnPause);
    video.addEventListener('seeked', syncOnSeeked);
    video.addEventListener('timeupdate', syncWhilePlaying);
    document.addEventListener('visibilitychange', syncOnHide);
    window.addEventListener('pagehide', syncNow);
    window.addEventListener('beforeunload', syncNow);

    const syncOnEnded = () => {
        markComplete(config, video, state).then((response) => {
            state.completed = response.completed;
            state.percentcomplete = response.percentcomplete;
            onUpdate(response);
            if (typeof onComplete === 'function') {
                onComplete(response);
            }
            return undefined;
        }).catch(() => {
            window.console.warn(config.strings.progressunavailable);
        });
    };
    video.addEventListener('ended', syncOnEnded);

    return {
        sync: () => tick(true),
        dispose: () => {
            window.clearInterval(timer);
            video.removeEventListener('play', syncOnPlay);
            video.removeEventListener('pause', syncOnPause);
            video.removeEventListener('seeked', syncOnSeeked);
            video.removeEventListener('timeupdate', syncWhilePlaying);
            video.removeEventListener('ended', syncOnEnded);
            document.removeEventListener('visibilitychange', syncOnHide);
            window.removeEventListener('pagehide', syncNow);
            window.removeEventListener('beforeunload', syncNow);
        }
    };
};
