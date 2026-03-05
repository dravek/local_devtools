// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module for developer tools navbar dropdown.
 *
 * @module     local_devtools/devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax', 'core/notification', 'core/str', 'core/form-autocomplete'],
    function(Ajax, Notification, Str, AutoComplete) {

        var reloadNoticeKey = 'local_devtools_reload_notice';

        return {
            /**
             * Initialize the dropdown listeners and task autocomplete.
             */
            init: function() {
                var self = this;
                var menu = document.getElementById('local-devtools-menu');
                var reloadNotice = window.sessionStorage.getItem(reloadNoticeKey);

                if (!menu) {
                    return;
                }

                if (reloadNotice) {
                    window.sessionStorage.removeItem(reloadNoticeKey);
                    Notification.addNotification({
                        message: reloadNotice,
                        type: 'success'
                    });
                }

                // Enhance the static task <select> with Moodle's searchable autocomplete UI.
                AutoComplete.enhance(
                    '#local-devtools-task-select',
                    false,
                    null,
                    '',
                    false,
                    true,
                    '',
                    true
                ).then(function() {
                    var wrapper = menu.querySelector('.form-autocomplete-wrapper');
                    var selection = menu.querySelector('.form-autocomplete-selection');
                    var suggestions = menu.querySelector('.form-autocomplete-suggestions');

                    if (wrapper) {
                        wrapper.style.position = 'relative';
                    }

                    if (selection) {
                        selection.style.width = '100%';
                    }

                    if (suggestions) {
                        suggestions.style.position = 'absolute';
                        suggestions.style.left = '0';
                        suggestions.style.right = '0';
                        suggestions.style.zIndex = '1060';
                        suggestions.style.maxHeight = '16rem';
                        suggestions.style.overflowY = 'auto';
                    }

                    return null;
                }).catch(function() {
                    // Fall back to the native <select> if the enhancement fails.
                    return null;
                });

                var keepMenuOpen = function(e) {
                    if (e.target.closest('.form-autocomplete-wrapper') ||
                            e.target.closest('.form-autocomplete-selection') ||
                            e.target.closest('.form-autocomplete-suggestions') ||
                            e.target.closest('#local-devtools-debug-switch') ||
                            e.target.closest('#local-devtools-task-select')) {
                        e.stopPropagation();
                    }
                };

                menu.addEventListener('mousedown', keepMenuOpen);
                var debugSwitch = document.getElementById('local-devtools-debug-switch');
                if (debugSwitch) {
                    debugSwitch.dataset.previousValue = debugSwitch.checked ? '1' : '0';
                    debugSwitch.addEventListener('change', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var previousValue = debugSwitch.dataset.previousValue || '0';
                        var enabled = debugSwitch.checked;
                        var trigger = debugSwitch.closest('.local-devtools-debug-switch-wrap');

                        return self.executeAction('set_debug', '', trigger, {enabled: enabled}).then(function(result) {
                            if (!result || !result.success) {
                                debugSwitch.checked = (previousValue === '1');
                            } else {
                                debugSwitch.dataset.previousValue = debugSwitch.checked ? '1' : '0';
                            }

                            return null;
                        });
                    });
                }

                menu.addEventListener('click', function(e) {
                    var actionLink = e.target.closest('.local-devtools-action');
                    if (actionLink) {
                        e.preventDefault();
                        self.executeAction(actionLink.dataset.action, '', actionLink);
                        return;
                    }

                    var runTaskButton = e.target.closest('.local-devtools-run-task');
                    if (runTaskButton) {
                        e.preventDefault();
                        e.stopPropagation();

                        var taskSelect = document.getElementById('local-devtools-task-select');
                        var task = taskSelect ? taskSelect.value : '';

                        if (!task) {
                            Str.get_string('no_task_selected', 'local_devtools').then(function(s) {
                                Notification.alert('', s);
                                return null;
                            }).catch(Notification.exception);
                            return;
                        }

                        self.executeAction('run_scheduled_task', task, runTaskButton);
                        return;
                    }

                    keepMenuOpen(e);
                });
            },

            /**
             * Execute the given action via AJAX.
             *
             * @param {string} action
             * @param {string} task
             * @param {HTMLElement} trigger
             * @param {Object} extraArgs
             */
            executeAction: function(action, task, trigger, extraArgs) {
                var spinner = trigger ? trigger.querySelector('.spinner') : null;
                var controls = document.querySelectorAll(
                    '.local-devtools-action, .local-devtools-run-task, .local-devtools-debug-switch'
                );
                var keepDisabledUntilReload = false;
                var resetUiState = function() {
                    if (spinner) {
                        spinner.classList.add('hidden');
                    }

                    controls.forEach(function(element) {
                        element.classList.remove('disabled');
                        element.style.pointerEvents = 'auto';
                        if (element.tagName === 'BUTTON' || element.tagName === 'SELECT' || element.tagName === 'INPUT') {
                            element.disabled = false;
                        }
                    });
                };

                if (spinner) {
                    spinner.classList.remove('hidden');
                }

                controls.forEach(function(element) {
                    element.classList.add('disabled');
                    element.style.pointerEvents = 'none';
                    if (element.tagName === 'BUTTON' || element.tagName === 'SELECT' || element.tagName === 'INPUT') {
                        element.disabled = true;
                    }
                });

                var methodname;
                var args;

                if (action === 'purge_caches') {
                    methodname = 'local_devtools_purge_caches';
                    args = {};
                } else if (action === 'run_cron') {
                    methodname = 'local_devtools_run_cron';
                    args = {};
                } else if (action === 'run_scheduled_task') {
                    methodname = 'local_devtools_run_scheduled_task';
                    args = {task: task};
                } else if (action === 'set_debug') {
                    methodname = 'local_devtools_set_debug';
                    args = extraArgs || {};
                } else {
                    resetUiState();
                    return Promise.resolve({success: false});
                }

                var request;
                try {
                    request = Ajax.call([{methodname: methodname, args: args}])[0];
                } catch (error) {
                    resetUiState();
                    Notification.exception(error);
                    return Promise.resolve({success: false});
                }

                return request.then(function(result) {
                    if (result.success) {
                        var message = result.message;

                        if (action === 'purge_caches') {
                            message += ' Reloading browser...';
                        }

                        Notification.addNotification({
                            message: message,
                            type: 'success'
                        });

                        if (action === 'purge_caches') {
                            window.sessionStorage.setItem(reloadNoticeKey, result.message);
                            keepDisabledUntilReload = true;
                            window.location.reload();
                        }
                    } else {
                        Notification.addNotification({
                            message: result.message,
                            type: 'error'
                        });
                    }

                    return result;
                }, function(error) {
                    Notification.exception(error);
                    return {success: false};
                }).then(function(result) {
                    if (!keepDisabledUntilReload) {
                        resetUiState();
                    }
                    return result;
                });
            }
        };
    });
