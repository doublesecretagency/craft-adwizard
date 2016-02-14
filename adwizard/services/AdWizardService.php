<?php
namespace Craft;

/**
 * Ads service
 */
class AdWizardService extends BaseApplicationComponent
{
	public $errorPrefix = '[Ad Wizard] ';

	private $_allGroupIds;
	private $_groupsById;
	private $_fetchedAllGroups = false;

	private $_csrfIncluded = false;

	// Groups

	/**
	 * Returns all of the group IDs.
	 *
	 * @return array
	 */
	public function getAllGroupIds()
	{
		if (!isset($this->_allGroupIds))
		{
			if ($this->_fetchedAllGroups)
			{
				$this->_allGroupIds = array_keys($this->_groupsById);
			}
			else
			{
				$this->_allGroupIds = craft()->db->createCommand()
					->select('id')
					->from('adwizard_groups')
					->queryColumn();
			}
		}

		return $this->_allGroupIds;
	}

	/**
	 * Returns all groups.
	 *
	 * @param string|null $indexBy
	 * @return array
	 */
	public function getAllGroups($indexBy = null)
	{
		if (!$this->_fetchedAllGroups)
		{
			$groupRecords = AdWizard_GroupRecord::model()->ordered()->findAll();
			$this->_groupsById = AdWizard_GroupModel::populateModels($groupRecords, 'id');
			$this->_fetchedAllGroups = true;
		}

		if ($indexBy == 'id')
		{
			return $this->_groupsById;
		}
		else if (!$indexBy)
		{
			return array_values($this->_groupsById);
		}
		else
		{
			$groups = array();
			foreach ($this->_groupsById as $group)
			{
				$groups[$group->$indexBy] = $group;
			}

			return $groups;
		}
	}

	/**
	 * Gets the total number of groups.
	 *
	 * @return int
	 */
	public function getTotalGroups()
	{
		return count($this->getAllGroupIds());
	}

	/**
	 * Returns a group by its ID.
	 *
	 * @param $groupId
	 * @return AdWizard_GroupModel|null
	 */
	public function getGroupById($groupId)
	{
		if (!isset($this->_groupsById) || !array_key_exists($groupId, $this->_groupsById))
		{
			$groupRecord = AdWizard_GroupRecord::model()->findById($groupId);

			if ($groupRecord)
			{
				$this->_groupsById[$groupId] = AdWizard_GroupModel::populateModel($groupRecord);
			}
			else
			{
				$this->_groupsById[$groupId] = null;
			}
		}

		return $this->_groupsById[$groupId];
	}

	/**
	 * Gets a group by its handle.
	 *
	 * @param string $groupHandle
	 * @return AdWizard_GroupModel|null
	 */
	public function getGroupByHandle($groupHandle)
	{
		$groupRecord = AdWizard_GroupRecord::model()->findByAttributes(array(
			'handle' => $groupHandle
		));

		if ($groupRecord)
		{
			return AdWizard_GroupModel::populateModel($groupRecord);
		}
	}

	/**
	 * Saves a group.
	 *
	 * @param AdWizard_GroupModel $group
	 * @throws \Exception
	 * @return bool
	 */
	public function saveGroup(AdWizard_GroupModel $group)
	{
		if ($group->id)
		{
			$groupRecord = AdWizard_GroupRecord::model()->findById($group->id);

			if (!$groupRecord)
			{
				throw new Exception(Craft::t('No group exists with the ID “{id}”', array('id' => $group->id)));
			}

			$oldGroup = AdWizard_GroupModel::populateModel($groupRecord);
			$isNewGroup = false;
		}
		else
		{
			$groupRecord = new AdWizard_GroupRecord();
			$isNewGroup = true;
		}

		$groupRecord->name   = $group->name;
		$groupRecord->handle = $group->handle;

		$groupRecord->validate();
		$group->addErrors($groupRecord->getErrors());

		if (!$group->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{

				// Save it!
				$groupRecord->save(false);

				// Now that we have a group ID, save it on the model
				if (!$group->id)
				{
					$group->id = $groupRecord->id;
				}

				// Might as well update our cache of the group while we have it.
				$this->_groupsById[$group->id] = $group;

				if ($transaction !== null)
				{
					$transaction->commit();
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				throw $e;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Deletes a group by its ID.
	 *
	 * @param int $groupId
	 * @throws \Exception
	 * @return bool
	 */
	public function deleteGroupById($groupId)
	{
		if (!$groupId)
		{
			return false;
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{

			// Grab the ad ids so we can clean the elements table.
			$adIds = craft()->db->createCommand()
				->select('id')
				->from('adwizard_ads')
				->where(array('groupId' => $groupId))
				->queryColumn();

			craft()->elements->deleteElementById($adIds);

			$affectedRows = craft()->db->createCommand()->delete('adwizard_groups', array('id' => $groupId));

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return (bool) $affectedRows;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			throw $e;
		}
	}

	// ============================================================== //

	/**
	 * Returns an ad by its ID.
	 *
	 * @param int $adId
	 * @return AdWizard_AdModel|null
	 */
	public function getAdById($adId)
	{
		return craft()->elements->getElementById($adId, 'AdWizard_Ad');
	}

	/**
	 * Saves an ad.
	 *
	 * @param AdWizard_AdModel $ad
	 * @throws Exception
	 * @return bool
	 */
	public function saveAd(AdWizard_AdModel $ad)
	{
		$isNewAd = !$ad->id;

		// Ad data
		if (!$isNewAd)
		{
			$adRecord = AdWizard_AdRecord::model()->findById($ad->id);

			if (!$adRecord)
			{
				throw new Exception(Craft::t('No ad exists with the ID “{id}”', array('id' => $ad->id)));
			}
		}
		else
		{
			$adRecord = new AdWizard_AdRecord();
		}

		$assetId = (!empty($ad->assetId) ? $ad->assetId[0] : null);

		$adRecord->groupId   = $ad->groupId;
		$adRecord->assetId   = $assetId;
		$adRecord->url       = $ad->url;
		$adRecord->details   = $ad->details;
		$adRecord->startDate = $ad->startDate;
		$adRecord->endDate   = $ad->endDate;
		$adRecord->maxViews  = (int) $ad->maxViews;

		if (!$adRecord->validate())
		{
			// Copy the record's errors over to the ad model
			$ad->addErrors($adRecord->getErrors());

			// Might as well validate the content as well,
			// so we get a complete list of validation errors
			if (!craft()->content->validateContent($ad))
			{
				// Copy the content model's errors over to the ad model
				$ad->addErrors($ad->getContent()->getErrors());
			}

			return false;
		}

		if (!$ad->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				// Fire an 'onBeforeSaveAd' ad
				$this->onBeforeSaveAd(new Event($this, array(
					'ad'      => $ad,
					'isNewAd' => $isNewAd
				)));

				if (craft()->elements->saveElement($ad))
				{
					// Now that we have an element ID, save it on the other stuff
					if ($isNewAd)
					{
						$adRecord->id = $ad->id;
					}

					$adRecord->save(false);

					// Fire an 'onSaveAd' ad
					$this->onSaveAd(new Event($this, array(
						'ad'      => $ad,
						'isNewAd' => $isNewAd
					)));

					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
				}
			}
			catch (\Exception $e)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				throw $e;
			}
		}

		return false;
	}

	// Events

	/**
	 * Fires an 'onBeforeSaveAd' event.
	 *
	 * @param Event $event
	 */
	public function onBeforeSaveAd(Event $event)
	{
		$this->raiseEvent('onBeforeSaveAd', $event);
	}

	/**
	 * Fires an 'onSaveAd' event.
	 *
	 * @param Event $event
	 */
	public function onSaveAd(Event $event)
	{
		$this->raiseEvent('onSaveAd', $event);
	}

	// ============================================================== //

	// Display ad
	public function renderAd($id, $transform = null, $retina = false)
	{
		$ad = $this->getAdById($id);
		return $this->_renderIndividualAd($ad, $transform, $retina);
	}

	// Display random ad from group
	public function renderRandomAdFromGroup($group, $transform = null, $retina = false)
	{
		$ad = $this->_getRandomAdFromGroup($group);
		return $this->_renderIndividualAd($ad, $transform, $retina);
	}

	// DEPRECATED
	public function renderAdFromGroup($group, $transform = null)
	{
		return $this->renderRandomAdFromGroup($group, $transform);
	}

	// Render an individual ad
	private function _renderIndividualAd($ad, $transform = null, $retina = false)
	{
		if (!$ad) {return false;}
		if (is_string($ad)) {return $ad;}
		if ($this->_displayAd($ad, $transform, $retina)) {
			$this->trackView($ad->id);
		}
		return TemplateHelper::getRaw($ad->html);
	}

	// ============================================================== //

	// Get individual ad via group
	private function _getRandomAdFromGroup($groupHandle)
	{
		if (!$groupHandle) {
			$this->err('Please specify an ad group.');
			return false;
		}

		$groupRecord = AdWizard_GroupRecord::model()->findByAttributes(array(
			'handle' => $groupHandle,
		));
		if (!$groupRecord) {
			$this->err('"'.$groupHandle.'" is not a valid group handle.');
			return false;
		}

		$fields = array(
			'adwizard_ads.*',
			'elements.enabled AS enabled',
		);
		$result = craft()->db->createCommand()
			->select(implode(',', $fields))
			->from('adwizard_ads adwizard_ads')
			->join('elements elements', 'adwizard_ads.id=elements.id')
			->where('enabled = 1')
			->andWhere('groupId = :groupId', array(':groupId' => $groupRecord->id))
			->andWhere('assetId IS NOT NULL')
			->andWhere('(startDate  <= NOW()   ) OR (startDate IS NULL)')
			->andWhere('(endDate    >= NOW()   ) OR (endDate   IS NULL)')
			->andWhere('(totalViews <  maxViews) OR (maxViews  =  0)   ')
			->order('RAND()')
			->queryRow();

		if ($result) {
			return AdWizard_AdModel::populateModel($result);
		} else {
			$this->err('No ads are available in the "'.$groupRecord->name.'" group.');
			return false;
		}

	}

	// Renders HTML of ad
	private function _displayAd(AdWizard_AdModel $ad, $transform = null, $retina = false)
	{
		$asset = craft()->assets->getFileById($ad->assetId);

		if (!$asset) {
			$this->err('No image specified for ad "'.$ad->title.'".');
			return false;
		} else if (!$ad->url) {
			$this->err('No URL specified for ad "'.$ad->title.'".');
			return false;
		}

		$onclick = "adWizard.click({$ad->id}, '{$ad->url}')";

		if (is_string($transform)) {
			$t = craft()->assetTransforms->getTransformByHandle($transform);
		} else if (is_array($transform)) {
			$t = AssetTransformModel::populateModel($transform);
		} else {
			$t = false;
		}

		if ($t) {
			$t->unsetAttributes(array(
				'id',
				'name',
				'handle',
			));
			if (true === $retina) {
				$t->setAttribute('width',  $t->width  * 2);
				$t->setAttribute('height', $t->height * 2);
			}
			$url    = $asset->getUrl($t);
			$width  = $asset->getWidth($t);
			$height = $asset->getHeight($t);
			if (true === $retina) {
				$width  = $width  / 2;
				$height = $height / 2;
			}
		} else {
			$url    = $asset->getUrl();
			$width  = $asset->getWidth();
			$height = $asset->getHeight();
		}

		$ad->html = PHP_EOL
				.'<img'
				.' src="'.$url.'"'
				.' width="'.$width.'"'
				.' height="'.$height.'"'
				.' class="adWizard-ad"'
				.' style="cursor:pointer"'
				.' onclick="'.$onclick.'"'
				.'/>';

		craft()->templates->includeJsResource('adwizard/js/superagent.js');
		craft()->templates->includeJsResource('adwizard/js/adwizard.js');

		// CSRF
		if (craft()->config->get('enableCsrfProtection') === true) {
			if (!$this->_csrfIncluded) {
				craft()->templates->includeJs('
window.csrfTokenName = "'.craft()->config->get('csrfTokenName').'";
window.csrfTokenValue = "'.craft()->request->getCsrfToken().'";
');
				$this->_csrfIncluded = true;
			}
		}

		return true;
	}

	// ============================================================== //

	// Tracking

	// Track view
	public function trackView($id)
	{
		$this->_incrementTotal($id, 'totalViews');
		return $this->_incrementDay($id, 'AdWizard_ViewRecord');
	}

	// Track click
	public function trackClick($id)
	{
		$this->_incrementTotal($id, 'totalClicks');
		return $this->_incrementDay($id, 'AdWizard_ClickRecord');
	}

	// Increment total overall value
	private function _incrementTotal($id, $field)
	{
		$ad = AdWizard_AdRecord::model()->findByPk($id);
		return $ad->saveCounters(array($field => 1));
	}

	// Increment total daily value
	private function _incrementDay($id, $recordName)
	{
		$year   = date('Y');
		$month  = date('n');
		$day    = 'day'.date('j');
		$record = __NAMESPACE__.'\\'.$recordName;
		// Get existing
		$tracking = $record::model()->findByAttributes(array(
			'adId'  => $id,
			'year'  => $year,
			'month' => $month,
		));
		// If tracking exists
		if ($tracking) {
			return $tracking->saveCounters(array(
				$day    => 1,
				'total' => 1,
			));
		} else {
			$trackingRecord = new $record;
			$trackingRecord->adId  = $id;
			$trackingRecord->year  = $year;
			$trackingRecord->month = $month;
			$trackingRecord->$day  = 1;
			$trackingRecord->total = 1;
			return $trackingRecord->save();
		}
	}

	// ============================================================== //

	// Records

	/**
	 * Returns attributes of tracking record.
	 *
	 * @return array
	 */
	public function trackingAttributes()
	{
		return array(
			'year'  => array(AttributeType::Number, 'default' => date('Y')),
			'month' => array(AttributeType::Number, 'default' => date('n')),
			'day1'  => array(AttributeType::Number, 'default' => 0),
			'day2'  => array(AttributeType::Number, 'default' => 0),
			'day3'  => array(AttributeType::Number, 'default' => 0),
			'day4'  => array(AttributeType::Number, 'default' => 0),
			'day5'  => array(AttributeType::Number, 'default' => 0),
			'day6'  => array(AttributeType::Number, 'default' => 0),
			'day7'  => array(AttributeType::Number, 'default' => 0),
			'day8'  => array(AttributeType::Number, 'default' => 0),
			'day9'  => array(AttributeType::Number, 'default' => 0),
			'day10' => array(AttributeType::Number, 'default' => 0),
			'day11' => array(AttributeType::Number, 'default' => 0),
			'day12' => array(AttributeType::Number, 'default' => 0),
			'day13' => array(AttributeType::Number, 'default' => 0),
			'day14' => array(AttributeType::Number, 'default' => 0),
			'day15' => array(AttributeType::Number, 'default' => 0),
			'day16' => array(AttributeType::Number, 'default' => 0),
			'day17' => array(AttributeType::Number, 'default' => 0),
			'day18' => array(AttributeType::Number, 'default' => 0),
			'day19' => array(AttributeType::Number, 'default' => 0),
			'day20' => array(AttributeType::Number, 'default' => 0),
			'day21' => array(AttributeType::Number, 'default' => 0),
			'day22' => array(AttributeType::Number, 'default' => 0),
			'day23' => array(AttributeType::Number, 'default' => 0),
			'day24' => array(AttributeType::Number, 'default' => 0),
			'day25' => array(AttributeType::Number, 'default' => 0),
			'day26' => array(AttributeType::Number, 'default' => 0),
			'day27' => array(AttributeType::Number, 'default' => 0),
			'day28' => array(AttributeType::Number, 'default' => 0),
			'day29' => array(AttributeType::Number, 'default' => 0),
			'day30' => array(AttributeType::Number, 'default' => 0),
			'day31' => array(AttributeType::Number, 'default' => 0),
			'total' => array(AttributeType::Number, 'default' => 0),
		);
	}

	/**
	 * Returns relations of tracking record.
	 *
	 * @return array
	 */
	public function trackingRelations()
	{
		return array(
			'ad' => array(BaseRecord::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => BaseRecord::CASCADE),
		);
	}

	// ============================================================== //

	// Stats

	// Total views in specified month
	public function monthTotalViews($id, $year, $month)
	{
		return $this->_getMonthTotal($id, 'AdWizard_ViewRecord', $year, $month);
	}

	// Total clicks in specified month
	public function monthTotalClicks($id, $year, $month)
	{
		return $this->_getMonthTotal($id, 'AdWizard_ClickRecord', $year, $month);
	}

	/**
	 * Get total number of views/clicks per month
	 *
	 * @return int
	 */
	public function _getMonthTotal($id, $recordName, $year, $month)
	{
		$record = __NAMESPACE__.'\\'.$recordName;
		// Get existing
		$tracking = $record::model()->findByAttributes(array(
			'adId'  => $id,
			'year'  => $year,
			'month' => $month,
		));
		// If tracking exists
		if ($tracking) {
			return $tracking->total;
		} else {
			return 0;
		}
	}

	// ============================================================== //

	/*
	// Check active status of ad
	private function _adStatus($ad)
	{
		$now       = new DateTime('now');
		$startDate = new DateTime($ad['startDate']);
		$endDate   = new DateTime($ad['endDate']);
		$notes  = '';
		$active = false;
		if (!$ad['enabled']) {
			$notes = 'Not enabled';
		} else if ($ad['startDate'] == $ad['endDate']) {
			$notes = 'Start & end date match';
		} else if ($endDate < $startDate) {
			$notes = 'Dates in wrong order';
		} else if ($now < $startDate) {
			$notes = 'Has not started yet';
		} else if ($endDate < $now) {
			$notes = 'Has already ended';
		} else if ($ad['maxViews'] && ($ad['maxViews'] <= $ad['totalViews'])) {
			$notes = 'Max impressions reached';
		} else {
			$notes = 'ACTIVE';
			$active = true;
		}
		return array(
			'active' => $active,
			'notes'  => $notes,
		);
	}
	*/

	// Output error to console log
	private function err($error)
	{
		$err = $this->errorPrefix.$error;
		craft()->templates->includeJs("console.log('{$err}')");
	}

}
