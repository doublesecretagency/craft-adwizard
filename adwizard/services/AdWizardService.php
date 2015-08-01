<?php
namespace Craft;

/**
 * Ads service
 */
class AdWizardService extends BaseApplicationComponent
{
	private $_allPositionIds;
	private $_positionsById;
	private $_fetchedAllPositions = false;
	private $_errorPrefix = '[Ad Wizard] ';

	private $_csrfIncluded = false;

	// Positions

	/**
	 * Returns all of the position IDs.
	 *
	 * @return array
	 */
	public function getAllPositionIds()
	{
		if (!isset($this->_allPositionIds))
		{
			if ($this->_fetchedAllPositions)
			{
				$this->_allPositionIds = array_keys($this->_positionsById);
			}
			else
			{
				$this->_allPositionIds = craft()->db->createCommand()
					->select('id')
					->from('adwizard_positions')
					->queryColumn();
			}
		}

		return $this->_allPositionIds;
	}

	/**
	 * Returns all positions.
	 *
	 * @param string|null $indexBy
	 * @return array
	 */
	public function getAllPositions($indexBy = null)
	{
		if (!$this->_fetchedAllPositions)
		{
			$positionRecords = AdWizard_PositionRecord::model()->ordered()->findAll();
			$this->_positionsById = AdWizard_PositionModel::populateModels($positionRecords, 'id');
			$this->_fetchedAllPositions = true;
		}

		if ($indexBy == 'id')
		{
			return $this->_positionsById;
		}
		else if (!$indexBy)
		{
			return array_values($this->_positionsById);
		}
		else
		{
			$positions = array();
			foreach ($this->_positionsById as $position)
			{
				$positions[$position->$indexBy] = $position;
			}

			return $positions;
		}
	}

	/**
	 * Gets the total number of positions.
	 *
	 * @return int
	 */
	public function getTotalPositions()
	{
		return count($this->getAllPositionIds());
	}

	/**
	 * Returns a position by its ID.
	 *
	 * @param $positionId
	 * @return AdWizard_PositionModel|null
	 */
	public function getPositionById($positionId)
	{
		if (!isset($this->_positionsById) || !array_key_exists($positionId, $this->_positionsById))
		{
			$positionRecord = AdWizard_PositionRecord::model()->findById($positionId);

			if ($positionRecord)
			{
				$this->_positionsById[$positionId] = AdWizard_PositionModel::populateModel($positionRecord);
			}
			else
			{
				$this->_positionsById[$positionId] = null;
			}
		}

		return $this->_positionsById[$positionId];
	}

	/**
	 * Gets a position by its handle.
	 *
	 * @param string $positionHandle
	 * @return AdWizard_PositionModel|null
	 */
	public function getPositionByHandle($positionHandle)
	{
		$positionRecord = AdWizard_PositionRecord::model()->findByAttributes(array(
			'handle' => $positionHandle
		));

		if ($positionRecord)
		{
			return AdWizard_PositionModel::populateModel($positionRecord);
		}
	}

	/**
	 * Saves a position.
	 *
	 * @param AdWizard_PositionModel $position
	 * @throws \Exception
	 * @return bool
	 */
	public function savePosition(AdWizard_PositionModel $position)
	{
		if ($position->id)
		{
			$positionRecord = AdWizard_PositionRecord::model()->findById($position->id);

			if (!$positionRecord)
			{
				throw new Exception(Craft::t('No position exists with the ID “{id}”', array('id' => $position->id)));
			}

			$oldPosition = AdWizard_PositionModel::populateModel($positionRecord);
			$isNewPosition = false;
		}
		else
		{
			$positionRecord = new AdWizard_PositionRecord();
			$isNewPosition = true;
		}

		$positionRecord->name       = $position->name;
		$positionRecord->handle     = $position->handle;

		$positionRecord->validate();
		$position->addErrors($positionRecord->getErrors());

		if (!$position->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{

				// Save it!
				$positionRecord->save(false);

				// Now that we have a position ID, save it on the model
				if (!$position->id)
				{
					$position->id = $positionRecord->id;
				}

				// Might as well update our cache of the position while we have it.
				$this->_positionsById[$position->id] = $position;

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
	 * Deletes a position by its ID.
	 *
	 * @param int $positionId
	 * @throws \Exception
	 * @return bool
	 */
	public function deletePositionById($positionId)
	{
		if (!$positionId)
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
				->where(array('positionId' => $positionId))
				->queryColumn();

			craft()->elements->deleteElementById($adIds);

			$affectedRows = craft()->db->createCommand()->delete('adwizard_positions', array('id' => $positionId));

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

	// Ads

	/**
	 * Returns all ads.
	 *
	 * @param string|null $indexBy
	 * @return array
	 */
	public function getAllAds($indexBy = null)
	{
		$adRecords = AdWizard_AdRecord::model()->findAll();
		$adsById = AdWizard_AdModel::populateModels($adRecords, 'id');

		if ($indexBy == 'id')
		{
			return $adsById;
		}
		else if (!$indexBy)
		{
			return array_values($adsById);
		}
		else
		{
			$ads = array();
			foreach ($adsById as $ad)
			{
				$ads[$ad->$indexBy] = $ad;
			}

			return $ads;
		}
	}

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

		$adRecord->positionId = $ad->positionId;
		$adRecord->assetId    = $assetId;
		$adRecord->url        = $ad->url;
		$adRecord->details    = $ad->details;
		$adRecord->startDate  = $ad->startDate;
		$adRecord->endDate    = $ad->endDate;
		$adRecord->maxViews   = (int) $ad->maxViews;

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
	public function renderAd($id)
	{
		$ad = $this->_getAdById($id);
		return $this->_renderIndividualAd($ad);
	}

	// Display random ad from position
	public function renderAdFromPosition($position)
	{
		$ad = $this->_getAdByPosition($position);
		return $this->_renderIndividualAd($ad);
	}

	// Render an individual ad
	private function _renderIndividualAd($ad)
	{
		if (!$ad) {return false;}
		if (is_string($ad)) {return $ad;}
		if ($this->_displayAd($ad)) {
			$this->trackView($ad->id);
		}
		return TemplateHelper::getRaw($ad->html);
	}
	
	// ============================================================== //
	

	// Get individual ad via ID
	private function _getAdById($id)
	{
		$ad = AdWizard_AdRecord::model()->findByPk($id);
		return AdWizard_AdModel::populateModel($ad);
	}

	// Get individual ad via position
	private function _getAdByPosition($positionHandle)
	{
		if (!$positionHandle) {
			return $this->_errorPrefix.'Please specify an ad position.';
		}
		
		$positionRecord = AdWizard_PositionRecord::model()->findByAttributes(array(
			'handle' => $positionHandle,
		));
		if (!$positionRecord) {
			return $this->_errorPrefix.'Invalid position handle.';
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
			->andWhere('positionId = :positionId', array(':positionId' => $positionRecord->id))
			->andWhere('assetId IS NOT NULL')
			->andWhere('(startDate  <= NOW()   ) OR (startDate IS NULL)')
			->andWhere('(endDate    >= NOW()   ) OR (endDate   IS NULL)')
			->andWhere('(totalViews <  maxViews) OR (maxViews  =  0)   ')
			->order('RAND()')
			->queryRow();

		if ($result) {
			return AdWizard_AdModel::populateModel($result);
		} else {
			return $this->_errorPrefix.'No ads available in this position.';
		}
		
	}
	

	// Renders HTML of ad
	private function _displayAd(AdWizard_AdModel $ad)
	{

		$asset = craft()->assets->getFileById($ad->assetId);

		if (!$asset) {
			$ad->html = $this->_errorPrefix.'No image specified for ad "'.$ad->title.'".';
			return false;
		}

		$url = craft()->assets->getUrlForFile($asset);

		if (!$ad->url) {
			$ad->html = $this->_errorPrefix.'No URL specified for ad "'.$ad->title.'".';
			return false;
		}

		// =================== //
		// Info should be included in AdWizard_AdModel
		// $ad->width = 200;
		// $ad->height = 200;
		// $ad->filepath = '/example.jpg';
		// =================== //
		$onclick = "adWizard.click({$ad->id}, '{$ad->url}')";
		$ad->html = PHP_EOL
				.'<img '
				.'width="'.$asset->width.'" '
				.'height="'.$asset->height.'" '
				.'src="'.$url.'" '
				.'class="adWizard-ad" '
				.'onclick="'.$onclick.'" '
				.'style="cursor:pointer" '
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

}
