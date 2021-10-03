<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Laminas\Db\Sql\Expression;

/**
 * Statistics api
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Stats extends AbstractApi
{
    /**
     * Get total download count of top serval items
     *
     * @param int $limit
     * @return array
     */
    public function getTopTotal($limit, $order = null)
    {
        $module = $this->getModule();

        $order = $order ?: 'count DESC';

        $where  = [
            'active'       => 1,
            'time_deleted' => 0,
        ];
        $model  = Pi::model('doc', $module);
        $select = $model->select()
            ->where($where)
            ->order($order);
        if ($limit) {
            $select->limit($limit)->offset(0);
        }
        $result = $model->selectWith($select)->toArray();

        return $result;
    }

    /**
     * Get top serval submitters
     *
     * @param int $days
     * @param int $limit
     * @param array $where
     * @return array
     */
    public function getTopSubmitterInPeriod($days, $limit, $where = [])
    {
        $dateFrom = !is_null($days) ? strtotime(sprintf('-%d day', $days)) : 0;
        $dateTo   = time();

        $result = [];
        $module = $this->getModule();

        if (!empty($dateFrom)) {
            $where['time_created >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time_created <= ?'] = $dateTo;
        }

        $model     = Pi::model('doc', $module);
        $select    = $model->select()
            ->where($where)
            ->columns([
                'uid',
                'total' => new Expression('count(id)'),
            ])->order('total DESC')
            ->group(['uid'])
            ->offset(0)
            ->limit($limit);
        $resultset = $model->selectWith($select)->toArray();

        $uids = [];
        foreach ($resultset as $row) {
            $result[]          = $row;
            $uids[$row['uid']] = $row['uid'];
        }

        if (!empty($uids)) {
            $resultSet = Pi::user()->get($uids, ['id', 'identity']);
            foreach ($resultSet as $user) {

                if (isset($user['id'])) {
                    $users[$user['id']] = [
                        'identity' => $user['identity'],
                    ];
                }
            }
            unset($resultSet);
        }

        foreach ($result as &$row) {
            $user = isset($users[$row['uid']]) ? $users[$row['uid']] : [
                'identity' => '',
            ];
            $row  = array_merge($row, $user);
        }

        return $result;
    }
}
