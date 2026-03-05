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
 * External function to enable/disable Moodle debug messages.
 *
 * @package    local_devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_devtools\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use moodle_exception;

/**
 * External function: set Moodle debug messages on/off.
 */
class set_debug extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'enabled' => new external_value(PARAM_BOOL, 'Whether debug messages should be enabled'),
        ]);
    }

    /**
     * Enables/disables Moodle debug messages.
     *
     * @param bool $enabled
     * @return array
     */
    public static function execute(bool $enabled): array {
        global $CFG;

        $params = self::validate_parameters(self::execute_parameters(), ['enabled' => $enabled]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/devtools:use', $context);

        if (!empty($CFG->config_php_settings) && array_key_exists('debug', $CFG->config_php_settings)) {
            throw new moodle_exception('debug_locked_configphp', 'local_devtools');
        }

        $level = $params['enabled'] ? DEBUG_DEVELOPER : DEBUG_NONE;
        set_config('debug', $level);

        return [
            'success' => true,
            'message' => $params['enabled'] ? get_string('debug_enabled_success', 'local_devtools') :
                get_string('debug_disabled_success', 'local_devtools'),
            'output'  => '',
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the action was successful'),
            'message' => new external_value(PARAM_TEXT, 'Success or error message'),
            'output'  => new external_value(PARAM_RAW, 'Truncated output from the execution'),
        ]);
    }
}
