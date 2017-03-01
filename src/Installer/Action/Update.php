<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Media\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');

        // Set doc model
        $docModel = Pi::model('doc', $this->module);
        $docTable = $docModel->getTable();
        $docAdapter = $docModel->getAdapter();

        // Set test model
        $testModel = Pi::model('test', $this->module);
        $testTable = $testModel->getTable();
        $testAdapter = $testModel->getAdapter();

        if (version_compare($moduleVersion, '1.0.4', '<')) {

            $sql =<<<SQL
ALTER TABLE %s
  DROP `url`,
  DROP `path`,
  DROP `name`,
  DROP `size`,
  DROP `module`,
  DROP `type`,
  DROP `token`,
  DROP `ip`,
SQL;

            $sql = sprintf($sql, $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.5', '<')) {

            $sql =<<<SQL
ALTER TABLE %s ADD `season` TINYINT NULL AFTER `count`, ADD `updated_by` INT NULL AFTER `season`, ADD `license_type` VARCHAR(255) NULL AFTER `updated_by`, ADD `copyright` VARCHAR(255) NULL AFTER `license_type`, ADD `geoloc_latitude` FLOAT NULL AFTER `copyright`, ADD `geoloc_longitude` FLOAT NULL AFTER `geoloc_latitude`, ADD `cropping` TEXT NULL AFTER `geoloc_longitude`, ADD `featured` TINYINT NOT NULL AFTER `cropping`;
SQL;

            $sql = sprintf($sql, $docTable);
            try {
                $docAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '1.0.6', '<')) {

            $sql =<<<SQL
CREATE TABLE %s ( `id` INT NOT NULL AUTO_INCREMENT , `title` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
SQL;

            $sql = sprintf($sql, $testTable);
            try {
                $testAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table create query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }
            
        return true;
    }
}