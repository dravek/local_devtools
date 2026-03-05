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
 * External services definition.
 *
 * @package    local_devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_devtools_purge_caches' => [
        'classname'   => 'local_devtools\external\purge_caches',
        'description' => 'Purges all Moodle caches',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_devtools_run_cron' => [
        'classname'   => 'local_devtools\external\run_cron',
        'description' => 'Runs Moodle cron via CLI',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_devtools_run_scheduled_task' => [
        'classname'   => 'local_devtools\external\run_scheduled_task',
        'description' => 'Runs a specific scheduled task via CLI',
        'type'        => 'write',
        'ajax'        => true,
    ],
    'local_devtools_set_debug' => [
        'classname'   => 'local_devtools\external\set_debug',
        'description' => 'Enables/disables Moodle debug messages',
        'type'        => 'write',
        'ajax'        => true,
    ],
];
