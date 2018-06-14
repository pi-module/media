<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Api;

use Closure;
use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\File\Transfer\Upload;
use Pi\Filter;

class Test extends AbstractApi
{
    /**
     * Module name
     * @var string
     */
    protected $module = 'media';

    /**
     * Get model
     *
     * @param string $name
     *
     * @return Pi\Application\Model\Model
     */
    protected function model($name = 'test')
    {
        $model = Pi::model($name, $this->module);

        return $model;
    }

    /**
     * Add a test
     *
     * @param array $data
     *
     * @return int
     */
    public function add(array $data)
    {
        unset($data['submit']);

        $row = $this->model()->createRow($data);
        $row->save();

        return (int) $row->id;
    }

    /**
     * Update test
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $row = $this->model()->find($id);

        if ($row) {
            if (array_key_exists('id', $data)) {
                unset($data['id']);
            }

            unset($data['submit']);

            $row->assign($data);
            $row->save();

            return true;
        }

        return false;
    }

    /**
     * Get list by condition
     *
     * @param array  $condition
     * @param int    $limit
     * @param int    $offset
     * @param string|array $order
     * @param array $attr
     *
     * @return array
     */
    public function getList(
        array $condition,
        $limit  = 0,
        $offset = 0,
        $order  = '',
        array $attr = array()
    ) {
        $model  = $this->model();
        $select = $model->select()->where($condition);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        if ($attr) {
            $select->columns($attr);
        }
        $rowset = $model->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[$row->id] = $row->toArray();
        }

        return $result;
    }
}
