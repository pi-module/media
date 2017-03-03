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

class Link extends AbstractApi
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
    protected function model($name = 'link')
    {
        $model = Pi::model($name, $this->module);

        return $model;
    }

    /**
     * Add a link
     *
     * @param array $data
     *
     * @return int
     */
    public function add(array $data)
    {
        $row = $this->model()->createRow($data);
        $row->save();

        return (int) $row->id;
    }

    /**
     * Get links by object
     *
     * @param array $params
     * @return \Zend\Db\ResultSet\ResultSetInterface
     */
    public function getlistByObject($params)
    {
        $select = $this->model()->select();
        $select->where($params);

        $result = $this->model()->selectWith($select);

        return $result;
    }
    
    /**
     * Remove links by object
     * 
     * @param array $params
     * @return bool
     */
    public function deleteByObject($params)
    {
        $result = $this->model()->delete($params);

        return $result;
    }

    /**
     * Update links from doc to objects
     * @param \Pi\Db\RowGateway\RowGateway $object
     */
    public function updateLinks($object){

        $model = $object->getModel();

        if(!empty($object->id) && $mediaLinks = $model->getMediaLinks()){
            $tableWithoutPrefix = str_replace(Pi::db()->getTablePrefix(), '', $model->getTable());

            preg_match('#^([^_]*)_(.*)#', $tableWithoutPrefix, $matches);

            if(isset($matches[1], $matches[2])){
                $links = array();

                $module = $matches[1];
                $object_name = $matches[2];
                $object_id = $object->id;

                $data = array(
                    'module' => $module,
                    'object_name' => $object_name,
                    'object_id' => $object_id,
                );

                $currentLinks = $this->getlistByObject($data)->toArray();
                foreach($currentLinks as $key => $currentLink){
                    unset($currentLinks[$key]['id']);
                }

                foreach($mediaLinks as $mediaLink){
                    if(isset($object->$mediaLink)){
                        $values = explode(',', $object->$mediaLink);

                        foreach($values as $value){
                            $data['field'] = $mediaLink;
                            $data['media_id'] = $value;

                            $links[] = $data;
                        }
                    }
                }


                print_r($currentLinks);
                print_r($links);

                $diff1 = $this->array_diff_assoc_recursive($currentLinks, $links);
                $diff2 = $this->array_diff_assoc_recursive($links, $currentLinks);

                foreach($diff1 as $data){
                    $this->deleteByObject($data);
                }

                foreach($diff2 as $data){
                    $this->add($data);
                }
            }
        }
    }

    public function array_diff_assoc_recursive($array1, $array2)
    {
        foreach($array1 as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                elseif(!is_array($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                else
                {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if($new_diff != FALSE)
                    {
                        $difference[$key] = $new_diff;
                    }
                }
            }
            elseif(!isset($array2[$key]) || $array2[$key] != $value)
            {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }
}
