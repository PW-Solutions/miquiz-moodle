<?php
// This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod_miquiz
 * @copyright  Bernhard Brandstetter <bernhard.brandstetter@fhstp.ac.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('miquiz_heading', get_string('generalconfig', 'miquiz'),
            get_string('explaingeneralconfig', 'miquiz')));
    $settings->add(new admin_setting_configtext('miquiz_baseurl', get_string('configbaseurl', 'miquiz'), '', 'https://test.mi-quiz.de/',PARAM_URL));
    $settings->add(new admin_setting_configtext('miquiz_apikey', get_string('configapikey', 'miquiz'), '', '',PARAM_ALPHANUM));
    $settings->add(new admin_setting_configtext('miquiz_loginprovider', get_string('configloginprovider', 'miquiz'), '', 'fhstp',PARAM_ALPHA));
    $settings->add(new admin_setting_configtext('miquiz_categorygroup', get_string('configcategorygroup', 'miquiz'), '', 'FHSTP',PARAM_ALPHA));
}