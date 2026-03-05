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
 * External function to run Moodle cron via CLI.
 *
 * @package    local_devtools
 * @copyright  2026 David Carrillo <dravek@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_devtools\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_system;
use local_devtools\helper\cli_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * External function: run Moodle cron via CLI.
 */
class run_cron extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Runs Moodle cron via CLI.
     *
     * @return array
     */
    public static function execute(): array {
        global $CFG;

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/devtools:use', $context);

        set_time_limit(0);

        $success = false;
        $message = '';

        try {
            $phpbin    = !empty($CFG->pathtophp) ? $CFG->pathtophp : 'php';
            $scriptpath = cli_helper::resolve_cli_script('cron.php');
            $script     = escapeshellarg($scriptpath);
            $command    = escapeshellcmd($phpbin) . " $script 2>&1";

            $outputlines = [];
            $returnvar   = 0;
            exec($command, $outputlines, $returnvar);

            $outputstr = implode("\n", $outputlines);
            $success   = ($returnvar === 0);

            if (!$success) {
                $message = get_string('cli_error_code', 'local_devtools', $returnvar);
            } else {
                $message = get_string('cron_success', 'local_devtools');
            }
        } catch (\Throwable $e) {
            $success = false;
            $message = get_string('error_execution_failed', 'local_devtools');
        }

        return [
            'success' => $success,
            'message' => $message,
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
