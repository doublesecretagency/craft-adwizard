<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m160204_000000_adWizard_changePositionsToGroups extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$this->_renameTable('adwizard_positions', 'adwizard_groups');
		$this->_renameForeignKey('adwizard_ads', 'positionId', 'groupId', 'adwizard_groups');
		$this->_renameWidget();
		return true;
	}

	// Rename table
	private function _renameTable($oldName, $newName)
	{
		$this->dropIndex($oldName, 'name', true);
		$this->dropIndex($oldName, 'handle', true);
		$this->renameTable($oldName, $newName);
		$this->createIndex($newName, 'name', true);
		$this->createIndex($newName, 'handle', true);
	}

	// Rename foreign key
	private function _renameForeignKey($table, $oldId, $newId, $relatedTable, $relatedId = 'id')
	{
		// Add new column
		$this->addColumnAfter($table, $newId, array(ColumnType::Int, 'required' => true), $oldId);

		// Get existing data
		$query = craft()->db->createCommand()
			->select("id, $oldId")
			->from($table)
		;

		// Port data to new column
		foreach ($query->queryAll() as $row) {
			$this->update($table, array($newId=>$row[$oldId]), 'id=:id', array(':id'=>$row['id']));
		}

		// Drop existing foreign key
		$this->dropForeignKey($table, $oldId);
		$this->dropColumn($table, $oldId);

		// Convert new column to foreign key
		$this->addForeignKey($table, $newId, $relatedTable, $relatedId, 'CASCADE', 'CASCADE');
	}

	// Rename widget
	private function _renameWidget()
	{
		// Get existing data
		$query = craft()->db->createCommand()
			->select('id, type, settings')
			->from('widgets')
		;

		// Rename widget and keys
		foreach ($query->queryAll() as $row) {
			$newData = array();
			switch ($row['type']) {
				case 'AdWizard_PositionTotals':
					$newData['type'] = 'AdWizard_GroupTotals';
				case 'AdWizard_AdTimeline':
					$newData['settings'] = preg_replace('/positionId/', 'groupId', $row['settings']);
					break;
			}
			if (!empty($newData)) {
				$this->update('widgets', $newData, 'id=:id', array(':id'=>$row['id']));
			}
		}
	}

}
