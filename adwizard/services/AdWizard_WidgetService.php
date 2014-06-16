<?php
namespace Craft;

/**
 * AdWizard - Widget service
 */
class AdWizard_WidgetService extends BaseApplicationComponent
{

    // 
    public function positionBarChart($positionId = null)
    {

        if (!$positionId) {
            return 'No ad position specified.';
        }

        $positionRecord = AdWizard_PositionRecord::model()->findByPk($positionId);
        if (!$positionRecord) {
            return 'Specified ad position does not exist.';
        }

        $adRecords = AdWizard_AdRecord::model()->findAllByAttributes(array(
            'positionId' => $positionRecord->id,
            //'startDate' => '',
            //'endDate'   => '',
            //'maxViews'  => '',
            //'enabled'   => '',
        ));
        if (empty($adRecords)) {
            return 'No ads exist in that position.';
        }

        $data = array(
            array('Ad', 'Views', 'Clicks'),
        );
        foreach ($adRecords as $adRecord) {
            $ad = AdWizard_AdModel::populateModel($adRecord);
            $name = $ad->title;
            $views = $this->_getTrackingTotal('AdWizard_ViewRecord', $adRecord);
            $clicks = $this->_getTrackingTotal('AdWizard_ClickRecord', $adRecord);
            $data[] = array($name, $views, $clicks);
        }

        $options = $this->_optionsBarChart();
        $options['chartArea']['height'] = count($adRecords) * 60;
        $height  = ($options['chartArea']['height'] + 30);
        return $this->_loadChartJs('BarChart', $data, $options, $height);

    }

    // 
    public function adLineChart($adId = null)
    {

        if (!$adId) {
            return 'No ad specified.';
        }

        $adRecord = AdWizard_AdRecord::model()->findByPk($adId);
        if (empty($adRecord)) {
            return 'Ad number "'.$adId.'" does not exist.';
        }
        $ad = AdWizard_AdModel::populateModel($adRecord);

        $data = array(
            array('Day', 'Views', 'Clicks'),
        );

        $views = $this->_getTrackingHistory('AdWizard_ViewRecord', $adRecord);
        $clicks = $this->_getTrackingHistory('AdWizard_ClickRecord', $adRecord);

        for ($i = 1; $i <= 31; $i++) {
            $day = 'day'.$i;
            $dayOfMonth = date('F').' '.$i;
            if (date('j') < $i) {
                $data[] = array($dayOfMonth, null, null);
            } else {
                $data[] = array($dayOfMonth, (int) $views->$day, (int) $clicks->$day);
            }
        }

        $options = $this->_optionsLineChart($ad);
        $height  = ($options['chartArea']['height'] + 90);
        return $this->_loadChartJs('LineChart', $data, $options, $height);

    }
    
    // ============================================================== //

    // Get tracking total for views/clicks
    private function _getTrackingTotal($recordName, $ad)
    {
        $tracking = $this->_getTrackingHistory($recordName, $ad);
        // Count tracking
        $total = 0;
        if ($tracking) {
            for ($i = 1; $i <= 31; $i++) {
                $day = 'day'.$i;
                $total += $tracking->$day;
            }
        }
        return $total;
    }

    // Get tracking history for views/clicks
    private function _getTrackingHistory($recordName, $ad)
    {
        $year   = date('Y');
        $month  = date('n');
        $record = 'Craft\\'.$recordName;
        // Get existing
        $results = $record::model()->findByAttributes(array(
            'adId'  => $ad->id,
            'year'  => $year,
            'month' => $month,
        ));
        if (!$results) {
            $results = new $record;
        }
        return $results;
    }

    // Load chart JS
    private function _loadChartJs($chartType, $data, $options, $height)
    {
        $widgetId = $this->_uniqueId();
        craft()->templates->includeJsFile('//www.google.com/jsapi');
        craft()->templates->includeJs("
google.load('visualization', '1', {packages:['corechart']});
google.setOnLoadCallback(drawChart".$widgetId.");
function drawChart".$widgetId."() {
    var data = google.visualization.arrayToDataTable(".json_encode($data).");
    var options = ".json_encode($options).";
    var chart = new google.visualization.".$chartType."(document.getElementById('chart-".$widgetId."'));
    chart.draw(data, options);
}
");
        return '<div id="chart-'.$widgetId.'" style="height:'.$height.'px"></div>';
        return '<div id="chart-'.$widgetId.'"></div>';
    }

    // Create unique widget ID
    private function _uniqueId()
    {
        return substr(number_format(microtime(true),4,'',''),-6);
    }
    
    // ============================================================== //

    // Options for bar charts
    private function _optionsBarChart()
    {
        // https://developers.google.com/chart/interactive/docs/gallery/barchart#Configuration_Options
        return array(
            //'title' => 'Tracking Results',
            'vAxis' => array(
                //'title' => 'Year',
                //'titleTextStyle' => array('color' => 'red'),
            ),
            'hAxis' => array(
                'minValue' => 0,
                'gridlines' => array('count' => -1),
            ),
            //'titlePosition' => 'out',
            'chartArea' => array(
                'top' => 10,
                'width' => 400,
                'height' => 300,
            ),
        );
    }

    // Options for line charts
    private function _optionsLineChart($ad)
    {
        // https://developers.google.com/chart/interactive/docs/gallery/linechart#Configuration_Options
        return array(
            //'title' => 'Tracking Results',
            'vAxis' => array(
                'title' => $ad->title,
                //'titleTextStyle' => array('color' => 'red'),
                'minValue' => 0,
                //'gridlines' => array('count' => -1),
                //'viewWindowMode' => 'explicit',
                //'viewWindow' => array('min' => 0),
                'titleTextStyle' => array(
                    'bold' => true,
                    'italic' => false,
                ),
            ),
            'hAxis' => array(
                'title' => date('F Y'),
                'minValue' => 1,
                //'gridlines' => array('count' => -1),
                'textPosition' => 'none',
                'titleTextStyle' => array(
                    'bold' => true,
                    'italic' => false,
                ),
            ),
            //'titlePosition' => 'out',
            'chartArea' => array(
                'top' => 45,
                'width' => 400,
                'height' => 240,
            ),
        );
    }

}
