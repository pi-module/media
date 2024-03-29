<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

/**
 * List controller
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */

use Module\Media\Form\MediaEditForm;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi;
use Module\Media\Form\MediaEditFullForm;
use Module\Media\Form\MediaEditFilter;
use Laminas\View\Model\ViewModel;

/**
 * List controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends ActionController
{
    var $currentId = null;

    /* public function dispatch(Request $request, Response $response = null)
    {
        header("X-Robots-Tag: noindex, nofollow", true);

        if(!Pi::user()->getId()){
            return $this->redirect()->toRoute('home');
        }

        return parent::dispatch($request, $response); // TODO: Change the autogenerated stub
    } */

    /**
     * Get application title by appkey
     *
     * @param array $appkeys
     * @return array
     */
    protected function getAppTitle($appkeys)
    {
        $result   = [];
        $modelApp = $this->getModel('application');
        $rowApp   = $modelApp->select(['appkey' => $appkeys]);
        foreach ($rowApp as $row) {
            $result[$row->appkey] = $row->title ?: $row->name;
        }
        unset($rowApp);
        unset($modelApp);

        return $result;
    }

    /**
     * Get category title by category ids
     *
     * @param array $category
     * @return array
     */
    protected function getCategoryTitle($category)
    {
        $result        = [];
        $modelCategory = $this->getModel('category');
        $rowCategory   = $modelCategory->select(['id' => $category]);
        foreach ($rowCategory as $row) {
            $result[$row->id] = $row->title ?: $row->name;
        }
        unset($rowCategory);
        unset($modelCategory);

        return $result;
    }


    /**
     * List all media
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /* if(!Pi::service('user')->hasIdentity()){
            $this->jumpToDenied();
        } */

        $active = $this->params('status', null);
        if ($active !== null) {
            $active = (int)$active;
        }

        $page   = (int)$this->params('p', 1);
        $limit  = (int)$this->config('page_limit') > 0 ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;

        $where  = [];
        $params = [];
        if (1 === $active) {
            $where['active']  = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active']  = 0;
            $params['status'] = 1;
        }

        $orphan = $this->params('orphan', 0);
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        }

        $params['delete'] = $delete;
        $params['orphan'] = $orphan;

        $user    = $this->params('user', null);
        $keyword = $this->params('keyword', null);

        /* if(Pi::engine()->section() != 'admin'){
            $user = Pi::user()->getId();
        } */

        if (is_numeric($user)) {
            $userModel = Pi::service('user')->getUser($user);
        } elseif ($user) {
            $userModel = Pi::service('user')->getUser($user, 'identity');
        } else {
            $userModel = '';
        }
        $uid = $userModel ? $userModel->get('id') : 0;

        if ($uid) {
            $where['uid'] = $uid;
        }

        $params = [
            'user'    => $user,
            'keyword' => $keyword,
        ];

        // Get media list
        $module    = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created DESC',
            [],
            $keyword,
            $orphan
        );

        $uids = $appkeys = [];
        $apps = $users = $avatars = [];
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
            $uids[]    = $row['uid'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }

        // Get users
        if (!empty($uids)) {
            $users   = Pi::user()->get($uids);
            $avatars = Pi::avatar()->get($uids);
        }

        // Total count
        $totalCountQuery = Pi::api('doc', $module)->getList(
            $where,
            0,
            0,
            'time_created DESC',
            [],
            $keyword,
            $orphan
        );
        $totalCount      = count($totalCountQuery);

        // Paginator
        $paginator = Paginator::factory($totalCount, [
            'page'        => $page,
            'limit'       => $limit,
            'url_options' => [
                'page_param' => 'p',
                'params'     => array_filter(array_merge([
                    'module'     => $this->getModule(),
                    'controller' => 'list',
                    'action'     => 'index',
                ])),
                'options'    => [
                    'query' => $params,
                ],
            ],
        ]);

        $navTabs = [
            [
                'active' => null === $active && !$delete && !$orphan,
                'label'  => __('All resources'),
                'href'   => $this->url('', [
                    'action' => 'index',
                ]),
            ],
            [
                'active' => $delete,
                'label'  => __('Deleted resources'),
                'href'   => $this->url('', [
                    'action' => 'index',
                    'delete' => 1,
                ]),
            ],
            [
                'active' => $orphan,
                'label'  => __('Orphan resources'),
                'href'   => $this->url('', [
                    'action' => 'index',
                    'orphan' => 1,
                ]),
            ],
        ];

        $this->view()->setTemplate('list-index');
        $this->view()->headTitle(__('My Medias'));
        $this->view()->assign([
            'title'     => __('Resource List'),
            'apps'      => $apps,
            'medias'    => $resultset,
            'paginator' => $paginator,
            'tabs'      => $navTabs,
            'users'     => $users,
            'avatars'   => $avatars,
            'active'    => $active,
            'delete'    => $delete,
            'user'      => $user,
            'keyword'   => $keyword,
        ]);
    }

    public function attachAction()
    {
        $this->view()->setTemplate('list-attach');
    }

    public function updatecropAction()
    {
        // Get id
        $id       = $this->params('id');
        $cropping = $this->params('cropping');

        if (empty($id)) {
            die('ID is missing');
        }

        if (empty($cropping)) {
            die('Cropping data is missing');
        }

        $row = $this->getModel('doc')->find($id);

        if ($row) {
            $row->cropping = $cropping;
            $row->save();
        } else {
            die('Entity is missing');
        }

        if (Pi::service()->hasService('log')) {
            Pi::service()->getService('log')->mute(true);
        }

        return $this->getResponse();
    }

    public function addAction()
    {
        // Set params
        $params = [];

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

        // Set params
        $title = preg_replace('#(.*)\.(.*)#', '$1', $file['file']['name']);
        $title = str_replace(['-', '_', '.'], ' ', $title);


        $params['filename'] = $file['file']['name'];
        $params['title']    = $title;
        $params['type']     = 'image';
        $params['active']   = 1;
        $params['module']   = $this->getModule();
        $params['uid']      = Pi::user()->getId();
        $params['ip']       = Pi::user()->getIp();

        // Upload media
        $response = Pi::api('doc', 'media')->upload($params, $this->currentId);

        // Check
        if (!isset($response['path']) || !$response['path']) {
            $response = [
                'status'  => 0,
                'message' => implode(' - ', $response['upload_errors']),
            ];
        } else {
            $response = [
                'status'      => 1,
                'message'     => __('Media attach '),
                'id'          => $response['id'],
                'path'        => $response['path'],
                'filename'    => $response['filename'],
                'title'       => '',
                'time_create' => '',
                'type'        => '',
                'hits'        => '',
                'size'        => '',
                'preview'     => Pi::api('doc', 'media')->getUrl($response['id']),
            ];
        }

        return $response;
    }

    /**
     * Delete media resources
     *
     * @return ViewModel|array
     * @throws \Exception
     */
    public function deleteAction()
    {
        $id   = $this->params('id', 0);
        $from = $this->params('redirect', '');

        $where = ['id' => $id];

        // Front user can't delete media from others
        if (Pi::engine()->section() != 'admin') {
            $where['uid'] = Pi::user()->getId();
        }

        // Mark media as deleted
        $this->getModel('doc')->update(
            ['time_deleted' => time()],
            $where
        );

        $request = $this->getRequest();

        if ($request->isXmlHttpRequest()) {
            $response = [
                'status'      => 1,
                'message'     => __('Media deleted'),
                'id'          => $id,
                'title'       => '',
                'time_create' => '',
                'type'        => '',
                'hits'        => '',
                'size'        => '',
                'preview'     => '',
            ];

            return $response;
        } else {
            // Go to list page or original page
            if ($from) {
                $from = urldecode($from);
                return $this->redirect()->toUrl($from);
            } else {
                return $this->redirect()->toRoute(
                    '',
                    [
                        'controller' => 'list',
                        'action'     => 'index',
                    ]
                );
            }
        }
    }

    /**
     * Undelete media resources
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function undeleteAction()
    {
        $from = $this->params('redirect', '');
        $id   = $this->params('id', 0);
        $ids  = array_filter(explode(',', $id));
        if (empty($ids)) {
            throw new \Exception(__('Invalid media ID'));
        }
        // Mark media as deleted
        $this->getModel('doc')->update(
            ['time_deleted' => null],
            ['id' => $ids]
        );
        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                [
                    'controller' => 'list',
                    'action'     => 'index',
                ]
            );
        }
    }

    /**
     * Active or diactivate media
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function activeAction()
    {
        $from = $this->params('redirect', '');

        $id  = $this->params('id', 0);
        $ids = array_filter(explode(',', $id));

        if (empty($ids)) {
            throw new \Exception(__('Invalid media ID'));
        }

        $status = $this->params('status', 1);
        $status = $status ? 1 : 0;

        // Mark media as deleted
        $this->getModel('doc')->update(
            ['active' => $status],
            ['id' => $ids]
        );

        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                [
                    'controller' => 'list',
                    'action'     => 'index',
                ]
            );
        }
    }

    /**
     * Edit media
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $this->view()->setTemplate('list-edit');

        $id  = $this->params('id', 0);
        $row = $this->getModel('doc')->find($id);
        if (!$row) {
            $this->view()->assign('id', $row->id);
            return;
        }

        // Front user can't delete media from others
        if (Pi::engine()->section() != 'admin' && $row->uid != Pi::user()->getId()) {
            die('Not allowed');
        }

        $form = $this->getMediaForm('edit', ['thumbUrl' => Pi::api('doc', 'media')->getUrl($row->id)]);
        $form->setData($row->toArray());

        $this->view()->assign([
            'form' => $form,
            'id'   => $row->id,
        ]);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            // Get file type
            $file = $this->request->getFiles();

            $form->setData($post);
            $form->setInputFilter(new MediaEditFilter);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    __('There are some error occur')
                );
            }

            $data = $form->getData();

            // upload image
            if (!empty($file['file']['name'])) {
                $this->currentId = $id;
                $response        = $this->addAction();

                if ($response['status'] != 1) {
                    return $this->renderForm(
                        $form,
                        $response['message']
                    );
                }

                $data['path']     = $response['path'];
                $data['filename'] = $response['filename'];
            } else if ($data['filename'] && $data['filename'] != $row->filename) {

                $filter  = new Pi\Filter\Urlizer;
                $options = Pi::service('media')->getOption('local', 'options');

                // get old path
                $slug                = $filter($row->filename, '-', true);
                $firstChars          = str_split(substr($slug, 0, 3));
                $relativeDestination = '/original/' . implode('/', $firstChars) . '/';
                $rootPath            = $options['root_path'];
                $destination         = $rootPath . $relativeDestination;
                $oldFinalPath        = $destination . $slug;


                $slug                = $filter($data['filename'], '-', true);
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

                $data['path']     = $relativeDestination;
                $data['filename'] = $finalSlug;

                mkdir($destination, 0777, true);

                $result = rename(
                    Pi::path($oldFinalPath),
                    Pi::path($newFinalPath)
                );

                if (!$result) {
                    return $this->renderForm(
                        $form,
                        __('There are some error occur when renaming filename')
                    );
                }
            } elseif (!$data['filename']) {

                $form->get('filename')->setValue($row->filename);

                return $this->renderForm(
                    $form,
                    __('Filename can not be empty')
                );
            }

            $id = $this->saveMedia($data);
            if (empty($id)) {
                return $this->renderForm(
                    $form,
                    __('Cannot save media data')
                );
            }

            return $this->redirect()->toRoute(
                '',
                [
                    'controller' => 'list',
                    'action'     => 'index',
                ]
            );
        }
    }

    /**
     * Getting form instance
     *
     * @param string $action Action to request when submit
     * @return \Module\Media\Form\MediaEditForm
     */
    protected function getMediaForm($action = 'edit', $options = [])
    {
        if (Pi::engine()->section() == 'admin') {
            $form = new MediaEditFullForm('media', $options);
        } else {
            $form = new MediaEditForm('media', $options);
        }
        $form->setAttribute('action', $this->url('', ['action' => $action]));

        return $form;
    }

    /**e
     * Render form
     *
     * @param Laminas\Form\Form $form Form instance
     * @param string $message Message assign to template
     * @param bool $error Whether is error message
     */
    public function renderForm($form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
    }

    /**
     * Save media data
     *
     * @param array $data
     * @return int
     */
    protected function saveMedia($data)
    {
        $id = $data['id'];
        unset($data['id']);

        $modelDoc = $this->getModel('doc');
        $rowMedia = $modelDoc->find($id);
        if ($rowMedia) {
            $rowMedia->assign($data);
            $rowMedia->time_updated = time();

            if ($uid = Pi::user()->getId()) {
                $rowMedia->updated_by = $uid;
            }

            $rowMedia->save();
        } else {
            $rowMedia = $modelDoc->createRow($data);
            $rowMedia->save();
        }

        return $rowMedia->id;
    }
}

