<?php
namespace Craft;

class AdWizard_AdElementType extends BaseElementType
{
    
    public function getName()
    {
        return Craft::t('Ads');
    }

    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }

    /**
     * Returns whether this element type has titles.
     *
     * @return bool
     */
    public function hasTitles()
    {
        return true;
    }

    /**
     * Returns whether this element type can have statuses.
     *
     * @return bool
     */
    public function hasStatuses()
    {
        return true;
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     * @return array|false
     */
    public function getSources($context = null)
    {

        $sources = array(
            '*' => array(
                'label' => Craft::t('All ads'),
            )
        );

        foreach (craft()->adWizard->getAllPositions() as $position)
        {
            $key = 'position:'.$position->id;

            $sources[$key] = array(
                'label'    => $position->name,
                'criteria' => array('positionId' => $position->id)
            );
        }

        return $sources;
    }

    /**
     * Returns the attributes that can be shown/sorted by in table views.
     *
     * @param string|null $source
     * @return array
     */
    public function defineTableAttributes($source = null)
    {
        return array(
            'title'     => Craft::t('Title'),
            'url'       => Craft::t('URL'),
            'startDate' => Craft::t('Start Date'),
            'endDate'   => Craft::t('End Date'),
            //'totalClicks' => Craft::t('Total Click-Thrus'),
            //'totalViews'  => Craft::t('Total Impressions'),
            'maxViews'  => Craft::t('Max Impressions'),
        );
    }

    /**
     * Returns the table view HTML for a given attribute.
     *
     * @param BaseElementModel $element
     * @param string $attribute
     * @return string
     */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {
        switch ($attribute)
        {

            case 'title':
            {
                return $element->$attribute;
            }

            case 'url':
            {
                $url = $element->$attribute;

                if ($url)
                {
                    $value = $url;

                    // Add some <wbr> tags in there so it doesn't all have to be on one line
                    $find = array('/');
                    $replace = array('/<wbr>');

                    $wordSeparator = craft()->config->get('slugWordSeparator');

                    if ($wordSeparator)
                    {
                        $find[] = $wordSeparator;
                        $replace[] = $wordSeparator.'<wbr>';
                    }

                    $value = str_replace($find, $replace, $value);

                    return '<a href="'.$url.'" target="_blank" class="go"><span dir="ltr">'.$value.'</span></a>';
                }
                else
                {
                    return '';
                }
            }

            case 'startDate':
            case 'endDate':
            {
                $date = $element->$attribute;

                if ($date)
                {
                    return $date->localeDate();
                }
                else
                {
                    return '';
                }
            }

            case 'totalClicks':
            case 'totalViews':
            case 'maxViews':
            {
                return $element->$attribute;
            }

            default:
            {
                return parent::getTableAttributeHtml($element, $attribute);
            }
        }
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'position'   => AttributeType::Mixed,
            'positionId' => AttributeType::Mixed,
            'startDate'  => AttributeType::Mixed,
            'endDate'    => AttributeType::Mixed,
            'order'      => array(AttributeType::String, 'default' => 'adwizard_ads.startDate asc'),
        );
    }

    /**
     * Modifies an element query targeting elements of this type.
     *
     * @param DbCommand $query
     * @param ElementCriteriaModel $criteria
     * @return mixed
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
        $query
            ->addSelect('adwizard_ads.*')
            ->join('adwizard_ads adwizard_ads', 'adwizard_ads.id = elements.id');

        if ($criteria->positionId)
        {
            $query->andWhere(DbHelper::parseParam('adwizard_ads.positionId', $criteria->positionId, $query->params));
        }

        if ($criteria->position)
        {
            $query->join('adwizard_positions adwizard_positions', 'adwizard_positions.id = adwizard_ads.positionId');
            $query->andWhere(DbHelper::parseParam('adwizard_positions.handle', $criteria->position, $query->params));
        }

        if ($criteria->startDate)
        {
            $query->andWhere(DbHelper::parseDateParam('entries.startDate', $criteria->startDate, $query->params));
        }

        if ($criteria->endDate)
        {
            $query->andWhere(DbHelper::parseDateParam('entries.endDate', $criteria->endDate, $query->params));
        }
    }

    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     * @return array
     */
    public function populateElementModel($row)
    {
        return AdWizard_AdModel::populateModel($row);
    }

    /**
     * Returns the HTML for an editor HUD for the given element.
     *
     * @param BaseElementModel $element
     * @return string
     */
    /*
    public function getEditorHtml(BaseElementModel $element)
    {
        // Start/End Dates
        $html = craft()->templates->render('adwizard/_edit', array(
            'element' => $element,
        ));

        // Everything else
        $html .= parent::getEditorHtml($element);

        return $html;
    }
    */
}
