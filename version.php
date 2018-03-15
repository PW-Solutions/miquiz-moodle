<?php

/**
 * @package   mod_miquiz
 * @copyright 2017, Thomas Wollmann <thomas.s.wollmann@gmail.com>
 * @license   Comercial, all rights reserved.
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_miquiz'; // Full name of the plugin (used for diagnostics).
$plugin->version   = 2018031500;  // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2015051109;  // Requires Moodle 2.9.
$plugin->cron      = 60;          // Period for cron to check this module (secs).
$plugin->release   = '2018-03-13';
$plugin->maturity = MATURITY_BETA;
