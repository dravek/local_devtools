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
 * CLI helper utility for devtools external functions.
 *
 * @package    local_devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_devtools\helper;

use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility class providing shared CLI helpers for devtools external functions.
 */
class cli_helper {

    /**
     * Resolve a CLI script path, supporting mixed root/public setups.
     *
     * @param string $scriptname The script filename (e.g. 'cron.php').
     * @return string Absolute path to the script.
     * @throws moodle_exception If the script cannot be found.
     */
    public static function resolve_cli_script(string $scriptname): string {
        global $CFG;

        $candidates = [
            $CFG->dirroot . '/admin/cli/' . $scriptname,
            dirname($CFG->dirroot) . '/admin/cli/' . $scriptname,
        ];

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        throw new moodle_exception(
            'error_unknown_action',
            'local_devtools',
            '',
            null,
            'CLI script not found: ' . $scriptname
        );
    }
}
