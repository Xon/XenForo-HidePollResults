<?php

class HidePollResults_Install
{
	public static function installer()
	{
		if (XenForo_Application::$versionId < 1040070)
		{
			throw new XenForo_Exception('This add-on requires XenForo 1.4.0 or higher.', true);
		}

		self::addRemoveColumn('xf_poll', 'hide_results', 'add', "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'", 'close_date');
		self::addRemoveColumn('xf_poll', 'until_close', 'add', "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'", 'hide_results');

		XenForo_Application::setSimpleCacheData('HidePollResults', false);
	}
	
	public static function uninstaller()
	{
		self::addRemoveColumn('xf_poll', 'hide_results');
		self::addRemoveColumn('xf_poll', 'until_close');

		$db = XenForo_Application::getDb();

		$db->query("
			DELETE FROM xf_permission_entry where permission_group_id = 'forum' and permission_id in ('bypassHiddenPollResultOwn', 'bypassHiddenPollResults', 'hidePollResults')
		");
		$db->query("
			DELETE FROM xf_permission_content_entry where permission_group_id = 'forum' and permission_id in ('bypassHiddenPollResultOwn', 'bypassHiddenPollResults', 'hidePollResults')
		");
		XenForo_Application::defer('Permission', array(), 'Permission', true);
	}
	
	public static function addRemoveColumn($tableName, $columnName, $action = 'remove', $columnDef = NULL, $after = NULL)
	{
		$db = XenForo_Application::get('db');
		$exists = self::doesColumnExist($tableName, $columnName);
		
		if ($action === 'remove')
		{
			if ($exists)
			{
    			$db->query("
    				ALTER TABLE {$tableName} DROP COLUMN {$columnName}
    			");				
			}
		}
		elseif ($action === 'add')
		{
			if (!$exists)
			{
    			$db->query("
    				ALTER TABLE {$tableName} ADD {$columnName} {$columnDef} AFTER {$after}
    			");
			}			
		}
	}
	
	public static function doesColumnExist($tableName, $columnName)
	{
    	$db = XenForo_Application::get('db');
    
    	return $db->fetchRow("
			SHOW COLUMNS
			FROM $tableName
			WHERE Field = ?
		", $columnName);
	}	
}
