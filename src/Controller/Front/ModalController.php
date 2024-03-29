<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Module\Media\Form\MediaEditFilter;
use Module\Media\Form\MediaEditForm;
use Module\Media\Form\MediaEditFullForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\In;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;

/**
 * Modal controller
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class ModalController extends ActionController
{
    var $ids = -1;

    public function dispatch(Request $request, Response $response = null)
    {
        header("X-Robots-Tag: noindex, nofollow", true);

        if (!Pi::user()->getId()) {
            return $this->redirect()->toRoute('home');
        }

        return parent::dispatch($request, $response); // TODO: Change the autogenerated stub
    }

    /**
     * List media
     */
    public function listAction()
    {
        $draw                  = $this->params('draw');
        $length                = $this->params('length');
        $start                 = $this->params('start');
        $keyword               = $this->params('search');
        $uploadCount           = $this->params('uploadCount');
        $showUIDMedia          = $this->params('show_uid_media');
        $showCheckedItemsFirst = $this->params('show_checked_items_first');

        if (isset($keyword['value'])) {
            $keyword = _escape($keyword['value']);
        } else {
            $keyword = null;
        }

        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        $where = [];

        if (Pi::engine()->section() == 'admin') {
            // Get admin roles
            $adminRoles = Pi::registry('role')->read('admin');

            // Set model
            $roleModel = Pi::model('user_role');

            // select users
            $select = $roleModel->select();
            $select->where(['role' => array_keys($adminRoles)]);
            $roleRowset = $roleModel->selectWith($select);

            // Set user list
            $adminRoles = [];
            foreach ($roleRowset as $role) {
                $adminRoles[] = $role->uid;
            }

            // Set selected user
            if ($showUIDMedia) {
                $adminRoles[] = $showUIDMedia;
            }

            // Set ware
            $where['uid'] = $adminRoles;
        } else {
            $where['uid'] = Pi::user()->getId();
        }

        $where['time_deleted'] = 0;

        $mediaModel = Pi::model('doc', $this->getModule());
        $linkModel  = Pi::model('link', $this->getModule());

        $select = $mediaModel->select();
        $select->where($where);
        $select->order('time_created DESC');

        if ($keyword && trim($keyword)) {

            $keyword        = trim($keyword);
            $keywordArray   = explode(' ', $keyword);
            $keywordBoolean = '+' . trim(implode(' +', $keywordArray));

            $select->where(
                new \Laminas\Db\Sql\Predicate\Expression("MATCH(" . $mediaModel->getTable() . ".title, " . $mediaModel->getTable() . ".description) AGAINST (? IN BOOLEAN MODE) OR " . $mediaModel->getTable() . ".title LIKE ? OR " . $mediaModel->getTable() . ".description LIKE ?", $keywordBoolean, '%' . $keyword . '%', '%' . $keyword . '%')
            );
            $select->columns(array_merge($select->getRawState($select::COLUMNS), [
                new \Laminas\Db\Sql\Expression("((MATCH(" . $mediaModel->getTable() . ".title) AGAINST (?) * 2) + (MATCH(" . $mediaModel->getTable() . ".description) AGAINST (?) * 1)) AS score", [$keyword, $keyword]),
            ]));
            $select->order('score DESC, time_created DESC');
        } else {
            $select->order('time_created DESC');
        }

        $resultsetFull = $mediaModel->selectWith($select);


        $select = $mediaModel->select();
        $select->where($where);
        $select->offset($start);
        $select->join(['link' => $linkModel->getTable()], $mediaModel->getTable() . ".id = link.media_id", [], \Laminas\Db\Sql\Select::JOIN_LEFT);
        $select->group($mediaModel->getTable() . ".id");

        $select->columns(array_merge($select->getRawState($select::COLUMNS), [
            new \Laminas\Db\Sql\Expression('COUNT(DISTINCT link.id) as nb_links'),
        ]));

        if ($keyword && trim($keyword)) {

            $keyword        = trim($keyword);
            $keywordArray   = explode(' ', $keyword);
            $keywordBoolean = '+' . trim(implode(' +', $keywordArray));

            $select->where(
                new \Laminas\Db\Sql\Predicate\Expression("MATCH(" . $mediaModel->getTable() . ".title, " . $mediaModel->getTable() . ".description) AGAINST (? IN BOOLEAN MODE) OR " . $mediaModel->getTable() . ".title LIKE ? OR " . $mediaModel->getTable() . ".description LIKE ?", $keywordBoolean, '%' . $keyword . '%', '%' . $keyword . '%')
            );
            $select->columns(array_merge($select->getRawState($select::COLUMNS), [
                new \Laminas\Db\Sql\Expression("((MATCH(" . $mediaModel->getTable() . ".title) AGAINST (?) * 2) + (MATCH(" . $mediaModel->getTable() . ".description) AGAINST (?) * 1)) AS score", [$keyword, $keyword]),
            ]));
            $select->order('score DESC, time_created DESC');
        } else {
            if ($showCheckedItemsFirst) {
                $sortExpression = new Expression('FIELD (' . $mediaModel->getTable() . '.id, ' . $showCheckedItemsFirst . ') DESC');
                $select->order($sortExpression);
            }
            $select->order('time_created DESC');
        }

        $select->limit($length);
        $resultset = $mediaModel->selectWith($select);

        $section = Pi::engine()->section() == 'admin' ? 'admin' : 'default';

        $data = [];
        foreach ($resultset as $media) {

            $removeBtn = '';

            if (!$media->time_deleted) {
                $removeUrl = $this->url($section, [
                    'controller' => 'modal',
                    'action'     => 'delete',
                    'id'         => $media->id,
                ]);

                $disabled = '';

                if ($media->nb_links > 0) {
                    $alertMsg = __("This media can't be deleted as it is already used by current or another item.");
                    $disabled = 'disabled="disabled" data-toggle="tooltip" title="' . $alertMsg . '"';
                }

                $removeBtn = <<<PHP
<a $disabled class="btn btn-danger btn-sm do-ajax remove-media-ajax" data-href="$removeUrl" data-value="delete">
    <span class="fas fa-times text-white" ></span >
</a>
PHP;

                $mediaEditForm = new MediaEditForm();
                $seasonOptions = $mediaEditForm->get('season')->getOptions();

                $buttons = '';
                foreach ($seasonOptions['value_options'] as $value => $label) {
                    $class   = $media->season == $value ? 'btn-primary' : 'btn-secondary';
                    $buttons .= '<button type="button" data-id="' . $value . '" class="btn btn-primary ' . $class . '">' . $label . '</button>';
                }

                $mediaFormActionUrl = Pi::service('url')->assemble(Pi::engine()->section() == 'admin' ? 'admin' : 'default', [
                    'module'     => 'media',
                    'controller' => 'modal',
                    'action'     => 'mediaformSeason',
                ]);

                $seasonBtn = <<<PHP
<div class="btn-group season-switch" role="group" aria-label="Extra-small button group" data-url="{$mediaFormActionUrl}" data-id="{$media->id}">
    {$buttons}
</div>
PHP;
            }

            $img = (string)Pi::api('resize', 'media')->resize($media)->thumbcrop(100, 100);

            $data[] = [
                'DT_RowAttr' => [
                    'data-media-id'     => $media['id'],
                    'data-media-img'    => $img,
                    'data-media-season' => $media['season'],
                ],
                'checked'    => '<span class="fas fa-check"></span>',
                'img'        => "<img src='" . $img . "' class='media-modal-thumb' />",
                'title'      => $media->title,
                'date'       => _date($media->time_created),
                'season'     => $seasonBtn,
                'removeBtn'  => $removeBtn,
            ];
        }

        $uploadedMedia = [];
        $i             = 0;
        foreach ($resultsetFull as $media) {
            $i++;
            /**
             * Reach upload count, full media list useless
             */
            if ($i > $uploadCount) {
                break;
            }

            $img             = (string)Pi::api('resize', 'media')->resize($media)->thumbcrop(100, 100);
            $uploadedMedia[] = [
                'id'     => (int)$media['id'],
                'img'    => $img,
                'season' => $media['season'],
            ];
        }

        $output = [
            "draw"            => (int)$draw,
            "recordsTotal"    => (int)$resultsetFull->count(),
            "recordsFiltered" => (int)$resultsetFull->count(),
            "data"            => $data,
            'uploadedMedia'   => $uploadedMedia,
        ];

        return $output;
    }

    /**
     * List media
     */
    public function currentSelectedMediaAction()
    {
        $ids = $this->params('ids');

        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        if (!$ids) {
            return $this->redirect()->toRoute('home');
        }

        /**
         * Get current media list with current order
         */
        $mediaModel = Pi::model('doc', 'media');
        $where      = [
            new In($mediaModel->getTable() . '.id', explode(',', $ids)),
        ];
        $order      = [new Expression('FIELD (' . $mediaModel->getTable() . '.id, ' . $ids . ')')];
        $resultset  = Pi::api('doc', $this->getModule())->getList($where, 0, 0, $order);

        $data = [];
        foreach ($resultset as $media) {
            $data[] = [
                'id'     => $media['id'],
                'img'    => (string)Pi::api('resize', 'media')->resize($media)->thumbcrop(100, 100),
                'season' => $media['season'],
            ];
        }

        return $data;
    }

    /**
     * List media
     */
    public function formlistAction()
    {
        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        $ids = (is_numeric($this->params('ids')) || $this->params('ids')) ? $this->params('ids') : $this->ids;

        $mediaModel = Pi::model('doc', 'media');

        $where = [
            new In($mediaModel->getTable() . '.id', explode(',', $ids)),
        ];


        $order = [new Expression('FIELD (' . $mediaModel->getTable() . '.id, ' . $ids . ')')];

        // Get media list
        $module = $this->getModule();
        $medias = Pi::api('doc', $module)->getList($where, 0, 0, $order);

        $haveToComplete = false;
        foreach ($medias as $media) {
            $hasInvalidFields = Pi::api('doc', 'media')->hasInvalidFields($media);

            if ($hasInvalidFields) {
                $haveToComplete = true;
            }
        }

        /* @var Pi\Mvc\Controller\Plugin\View $view */
        $view = $this->view();

        $view->setLayout('layout-content');
        $view->setTemplate('../front/modal-formlist');
        $view->assign([
            'title'          => _a('Resource List'),
            'medias'         => $medias,
            'haveToComplete' => $haveToComplete,
        ]);

        return Pi::service('view')->render($view->getViewModel());
    }

    /**
     * Media form
     */
    public function mediaformAction()
    {
        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        $id         = $this->params('id');
        $fromModule = $this->params('from_module', 'media');

        if ($id) {
            // Get media list
            $module = $this->getModule();
            $media  = Pi::model('doc', $module)->find($id);

            // Front user can't delete media from others
            if (Pi::engine()->section() != 'admin' && $media->uid != Pi::user()->getId()) {
                die('Not allowed');
            }

            if (Pi::engine()->section() == 'admin') {
                $form = new MediaEditFullForm('media', ['thumbUrl' => Pi::api('doc', 'media')->getUrl($media->id)]);
            } else {
                $form = new MediaEditForm('media', ['thumbUrl' => Pi::api('doc', 'media')->getUrl($media->id)]);
            }


            $form->setAttribute('action', $this->url('', ['action' => 'mediaform']) . '?id=' . $id);

            $form->setData($media->toArray());
            $form->setInputFilter(new MediaEditFilter());
            $form->get('submit')->setAttribute('class', 'd-none');

            $view = new \Laminas\View\Model\ViewModel;

            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                // Get file type

                $form->setData($post);
                if ($form->isValid()) {
                    $formIsValid = true;
                    // upload image
                    $file = $this->request->getFiles();
                    if (!empty($file['file']['name'])) {
                        $this->currentId = $id;

                        if (extension_loaded('intl') && !normalizer_is_normalized($file['file']['name'])) {
                            $file['file']['name'] = normalizer_normalize($file['file']['name']);
                        }

                        $title = preg_replace('#(.*)\.(.*)#', '$1', $file['file']['name']);
                        $title = str_replace(['-', '_', '.'], ' ', $title);

                        // Set params
                        $params             = [];
                        $params['filename'] = $file['file']['name'];
                        $params['title']    = $title;
                        $params['type']     = 'image';
                        $params['active']   = 1;
                        $params['module']   = 'media';
                        $params['uid']      = Pi::user()->getId();
                        $params['ip']       = Pi::user()->getIp();

                        // Upload media
                        $response = Pi::api('doc', 'media')->upload($params, $id, $fromModule);


                        if (!isset($response['path']) || !$response['path']) {
                            $formIsValid = false;

                            $view->setVariable('message', implode('<br />', $response['upload_errors']));
                        } else {
                            $post['path']     = $response['path'];
                            $post['filename'] = $response['filename'];
                        }
                    } else if ($post['filename'] && $post['filename'] != $media->filename) {

                        $filter  = new Pi\Filter\Urlizer;
                        $options = Pi::service('media')->getOption('local', 'options');

                        // get old path
                        $slug                = $filter($media->filename, '-', true);
                        $firstChars          = str_split(substr($slug, 0, 3));
                        $relativeDestination = '/original/' . implode('/', $firstChars) . '/';
                        $rootPath            = $options['root_path'];
                        $destination         = $rootPath . $relativeDestination;
                        $oldFinalPath        = $destination . $slug;


                        $slug                = $filter($post['filename'], '-', true);
                        $firstChars          = str_split(substr($slug, 0, 3));
                        $relativeDestination = '/original/' . implode('/', $firstChars) . '/';
                        $rootPath            = $options['root_path'];
                        $destination         = $rootPath . $relativeDestination;
                        $newFinalPath        = $destination . $slug;
                        $finalSlug           = $slug;

                        $filenameBase = pathinfo($slug, PATHINFO_FILENAME);
                        $filenameExt  = pathinfo($slug, PATHINFO_EXTENSION);

                        $i = 1;
                        while (is_file($newFinalPath)) {
                            $finalSlug    = $filenameBase . '-' . $i++ . '.' . $filenameExt;
                            $newFinalPath = $destination . $finalSlug;
                        }

                        $post['filename'] = $finalSlug;
                        $post['path']     = $relativeDestination;

                        Pi::service('file')->mkdir($destination);

                        rename(
                            $oldFinalPath,
                            $newFinalPath
                        );
                    }

                    if ($formIsValid) {
                        $media->assign($post);
                        $media->time_updated = time();

                        if ($uid = Pi::user()->getId()) {
                            $media->updated_by = $uid;
                        }
                        $media->save();

                        return [
                            'status'  => 1,
                            'content' => null,
                            'url'     => (string)Pi::api('resize', 'media')->resizeFormList($media),
                            'season'  => $media->season,
                        ];
                    }
                }
            }

            $view->setTemplate('front/partial/modal-media-form');
            $view->setVariable('form', $form);

            return [
                'status'  => 0,
                'content' => Pi::service('view')->render($view),
            ];
        }

        return false;
    }

    /**
     * Media form
     */
    public function mediaformSeasonAction()
    {
        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        $id     = $this->params('id');
        $season = $this->params('season');

        if ($id) {
            // Get media list
            $module = $this->getModule();
            $media  = Pi::model('doc', $module)->find($id);

            // Front user can't delete media from others
            if (Pi::engine()->section() != 'admin' && $media->uid != Pi::user()->getId()) {
                die('Not allowed');
            }

            if ($media && $media->id) {
                $media->season       = $season;
                $media->time_updated = time();

                if ($uid = Pi::user()->getId()) {
                    $media->updated_by = $uid;
                }

                $media->save();
            }
        }

        return false;
    }

    /**
     * Upload media
     */
    public function uploadAction()
    {
        $uid = Pi::user()->getId();

        if ($uid) {
            // Get file type
            $file = $this->request->getFiles();

            // Get main module
            $from = $this->params('from');
            if (isset($from) && !empty($from)) {
                $params['module'] = $from;
            } else {
                $params['module'] = $this->getModule();
            }

            if (extension_loaded('intl') && !normalizer_is_normalized($file['file']['name'])) {
                $file['file']['name'] = normalizer_normalize($file['file']['name']);
            }

            $title = preg_replace('#(.*)\.(.*)#', '$1', $file['file']['name']);
            $title = str_replace(['-', '_', '.'], ' ', $title);

            // Set params
            $params['filename'] = $file['file']['name'];
            $params['title']    = $title;
            $params['type']     = 'image';
            $params['active']   = 1;
            $params['module']   = $this->getModule();
            $params['uid']      = Pi::user()->getId();
            $params['ip']       = Pi::user()->getIp();

            $fromModule = $this->params('from_module', 'media');

            // Upload media
            $response = Pi::api('doc', 'media')->upload($params, null, $fromModule);

            // Check
            if (!isset($response['id']) || !$response['id']) {
                http_response_code(500);

                foreach ($response['upload_errors'] as &$value) {
                    $value = '<li>' . $value . '</li>';
                }
                $response = '<ul>' . implode('', $response['upload_errors']) . '</ul>';
            } else {
                $response = __('Media uploaded successfully');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        echo $response;
        exit;
    }

    /**
     * Delete media resources
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function deleteAction()
    {
        $id  = $this->params('id', 0);
        $ids = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }

        $where = ['id' => $ids];

        // Front user can't delete media from others
        if (Pi::engine()->section() != 'admin') {
            $where['uid'] = Pi::user()->getId();
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            ['time_deleted' => time()],
            $where
        );

        return $this->redirect()->toRoute(
            '',
            [
                'controller' => 'modal',
                'action'     => 'list',
            ]
        );
    }

    /**
     * Undelete media resources
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function undeleteAction()
    {
        $id  = $this->params('id', 0);
        $ids = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(_a('Invalid media ID'));
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            ['time_deleted' => null],
            ['id' => $ids]
        );

        // Go to list page or original page
        return $this->redirect()->toRoute(
            '',
            [
                'controller' => 'modal',
                'action'     => 'list',
            ]
        );
    }
}
