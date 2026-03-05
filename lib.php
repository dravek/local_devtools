<?php
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
 * Library functions and hooks.
 *
 * @package    local_devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renders the developer tools icon and dropdown in the navbar.
 *
 * @param renderer_base $renderer
 * @return string
 */
function local_devtools_render_navbar_output(renderer_base $renderer) {
    global $CFG;

    if (!has_capability('local/devtools:use', context_system::instance())) {
        return '';
    }

    $tasks = \core\task\manager::get_all_scheduled_tasks();
    $tasklist = [];

    foreach ($tasks as $task) {
        $tasklist[] = [
            'classname' => get_class($task),
            'name'      => $task->get_name(),
            'component' => $task->get_component(),
        ];
    }

    // Sort by name.
    usort($tasklist, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    $data = [
        'tasks' => $tasklist,
    ];

    $plugininfo = \core_plugin_manager::instance()->get_plugin_info('local_devtools');
    $release = '0.0.0';
    if ($plugininfo && !empty($plugininfo->release)) {
        $release = $plugininfo->release;
    }
    $debugforced = !empty($CFG->config_php_settings) && array_key_exists('debug', $CFG->config_php_settings);
    $data['pluginrelease'] = 'v.' . $release;
    $data['debugenabled'] = ((int)($CFG->debug ?? DEBUG_NONE) !== DEBUG_NONE);
    $data['debuglocked'] = $debugforced;

    return $renderer->render_from_template('local_devtools/navbar_dropdown', $data);
}
