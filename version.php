<?php

/**
 * @package   mod_miquiz
 * @copyright 2017, Thomas Wollmann <thomas.s.wollmann@gmail.com>
 * @license   Comercial, all rights reserved.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_miquiz'; // Full name of the plugin (used for diagnostics).
$plugin->version   = 2017080619;  // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2010112400;  // Requires Moodle 2.0.
$plugin->cron      = 60;          // Period for cron to check this module (secs).
$plugin->release   = '2017-08-06';
$plugin->maturity = MATURITY_BETA;
