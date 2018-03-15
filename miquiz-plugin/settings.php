<?php

if ( $hassiteconfig ){

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/instanceurl',
		get_string('miquiz_setting_instanceurl_title', 'miquiz'),
		get_string('miquiz_setting_instanceurl_helper', 'miquiz'),
		'https://app.mi-quiz.de',
		PARAM_TEXT 
	) );

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/apikey',
		get_string('miquiz_setting_apikey_title', 'miquiz'),
		get_string('miquiz_setting_apikey_helper', 'miquiz'),
		'No Key Defined',
		PARAM_TEXT 
	) );

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/loginprovider',
		get_string('miquiz_setting_loginprovider_title', 'miquiz'),
		get_string('miquiz_setting_loginprovider_helper', 'miquiz'),
		'default',
		PARAM_TEXT 
	) );

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/modulename',
		get_string('miquiz_setting_modulename_title', 'miquiz'),
		get_string('miquiz_setting_modulename_helper', 'miquiz'),
		'Moodle',
		PARAM_TEXT 
	) );
	
	$settings->add( new admin_setting_configtext(
		'mod_miquiz/instancename',
		get_string('miquiz_setting_instancename_title', 'miquiz'),
		get_string('miquiz_setting_instancename_helper', 'miquiz'),
		'MI-Quiz',
		PARAM_TEXT 
	) );
	

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/questiontimetag',
		get_string('miquiz_setting_questiontimetag_title', 'miquiz'),
		get_string('miquiz_setting_questiontimetag_helper', 'miquiz'),
		'Antwortzeit',
		PARAM_TEXT 
	) );
	

	$settings->add( new admin_setting_configtext(
		'mod_miquiz/questiondefaulttime',
		get_string('miquiz_setting_questiondefaulttime_title', 'miquiz'),
		get_string('miquiz_setting_questiondefaulttime_helper', 'miquiz'),
		'60',
		PARAM_TEXT 
	) );

}