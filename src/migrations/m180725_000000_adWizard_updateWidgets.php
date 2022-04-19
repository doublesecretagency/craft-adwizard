<?php
/**
 * Ad Wizard plugin for Craft CMS
 *
 * Easily manage custom advertisements on your website.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\adwizard\migrations;

use craft\db\Migration;
use doublesecretagency\adwizard\widgets\AdTimeline;
use doublesecretagency\adwizard\widgets\GroupTotals;

/**
 * Migration: Update widgets for Craft 3 compatibility
 * @since 2.0.0
 */
class m180725_000000_adWizard_updateWidgets extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        // Auto-update Ad Timeline widgets
        $this->update('{{%widgets}}', [
            'type' => AdTimeline::class
        ], [
            'type' => 'AdWizard_AdTimeline'
        ], [], false);

        // Auto-update Group Totals widgets
        $this->update('{{%widgets}}', [
            'type' => GroupTotals::class
        ], [
            'type' => 'AdWizard_GroupTotals'
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180725_000000_adWizard_updateWidgets cannot be reverted.\n";

        return false;
    }

}
