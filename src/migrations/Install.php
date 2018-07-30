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

/**
 * Installation Migration
 * @since 2.0.0
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%adwizard_ads}}');
        $this->dropTableIfExists('{{%adwizard_groups}}');
        $this->dropTableIfExists('{{%adwizard_clicks}}');
        $this->dropTableIfExists('{{%adwizard_views}}');
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%adwizard_ads}}', [
            'id'          => $this->integer()->notNull(),
            'groupId'     => $this->integer()->notNull(),
            'assetId'     => $this->integer(),
            'url'         => $this->text()->notNull(),
            'details'     => $this->text(),
            'startDate'   => $this->dateTime(),
            'endDate'     => $this->dateTime(),
            'maxViews'    => $this->integer()->defaultValue(0),
            'totalViews'  => $this->integer()->defaultValue(0),
            'totalClicks' => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
            'PRIMARY KEY([[id]])',
        ]);
        $this->createTable('{{%adwizard_groups}}', [
            'id'          => $this->primaryKey(),
            'name'        => $this->string()->notNull(),
            'handle'      => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
        $this->createTable('{{%adwizard_clicks}}', $this->_trackingTable());
        $this->createTable('{{%adwizard_views}}',  $this->_trackingTable());
    }

    /**
     * Architecture for tracking table.
     *
     * @return array
     */
    private function _trackingTable(): array
    {
        return [
            'id'          => $this->primaryKey(),
            'adId'        => $this->integer()->notNull(),
            'year'        => $this->integer()->defaultValue(2000),
            'month'       => $this->integer()->defaultValue(1),
            'day1'        => $this->integer()->defaultValue(0),
            'day2'        => $this->integer()->defaultValue(0),
            'day3'        => $this->integer()->defaultValue(0),
            'day4'        => $this->integer()->defaultValue(0),
            'day5'        => $this->integer()->defaultValue(0),
            'day6'        => $this->integer()->defaultValue(0),
            'day7'        => $this->integer()->defaultValue(0),
            'day8'        => $this->integer()->defaultValue(0),
            'day9'        => $this->integer()->defaultValue(0),
            'day10'       => $this->integer()->defaultValue(0),
            'day11'       => $this->integer()->defaultValue(0),
            'day12'       => $this->integer()->defaultValue(0),
            'day13'       => $this->integer()->defaultValue(0),
            'day14'       => $this->integer()->defaultValue(0),
            'day15'       => $this->integer()->defaultValue(0),
            'day16'       => $this->integer()->defaultValue(0),
            'day17'       => $this->integer()->defaultValue(0),
            'day18'       => $this->integer()->defaultValue(0),
            'day19'       => $this->integer()->defaultValue(0),
            'day20'       => $this->integer()->defaultValue(0),
            'day21'       => $this->integer()->defaultValue(0),
            'day22'       => $this->integer()->defaultValue(0),
            'day23'       => $this->integer()->defaultValue(0),
            'day24'       => $this->integer()->defaultValue(0),
            'day25'       => $this->integer()->defaultValue(0),
            'day26'       => $this->integer()->defaultValue(0),
            'day27'       => $this->integer()->defaultValue(0),
            'day28'       => $this->integer()->defaultValue(0),
            'day29'       => $this->integer()->defaultValue(0),
            'day30'       => $this->integer()->defaultValue(0),
            'day31'       => $this->integer()->defaultValue(0),
            'total'       => $this->integer()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ];
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%adwizard_ads}}', ['groupId']);
        $this->createIndex(null, '{{%adwizard_ads}}', ['assetId']);
        $this->createIndex(null, '{{%adwizard_groups}}', ['name'],   true);
        $this->createIndex(null, '{{%adwizard_groups}}', ['handle'], true);
        $this->createIndex(null, '{{%adwizard_clicks}}', ['adId']);
        $this->createIndex(null, '{{%adwizard_views}}',  ['adId']);
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%adwizard_ads}}', ['id'],      '{{%elements}}',        ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%adwizard_ads}}', ['groupId'], '{{%adwizard_groups}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%adwizard_ads}}', ['assetId'], '{{%assets}}',          ['id'], 'SET NULL');
        $this->addForeignKey(null, '{{%adwizard_clicks}}', ['adId'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%adwizard_views}}',  ['adId'], '{{%elements}}', ['id'], 'CASCADE');
    }

}
