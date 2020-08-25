<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_miquiz_upgrade($oldversion)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2019022308) {
        $table = new xmldb_table('miquiz');
        $field = new xmldb_field('statsonlyforfinishedgames');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2019022308, 'mod', 'miquiz');
    }

    if ($oldversion < 2019032510) {
        $table = new xmldb_table('miquiz');
        $field = new xmldb_field('has_training_phase');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2019032510, 'mod', 'miquiz');
    }

    if ($oldversion < 2019081300) {
        $table = new xmldb_table('miquiz');

        $field = new xmldb_field('game_mode_random_fight');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('game_mode_picked_fight');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2019081300, 'mod', 'miquiz');
    }

    if ($oldversion < 2020060900) {
        $table = new xmldb_table('miquiz');

        $field = new xmldb_field('game_mode_solo_fight');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020060900, 'mod', 'miquiz');
    }

    if ($oldversion < 2020061300) {
        $table = new xmldb_table('miquiz');

        $field = new xmldb_field('show_always_in_production');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020061300, 'mod', 'miquiz');
    }    

    return true;
}
