<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

class Modules_SecretKeysManager_List_SecretKeys extends pm_View_List_Simple
{
    /**
     * Modules_SecretKeysManager_List_SecretKeys constructor.
     *
     * @param Zend_View $view
     * @param Zend_Controller_Request_Abstract $request
     * @param array $options
     */
    public function __construct(Zend_View $view, Zend_Controller_Request_Abstract $request, array $options = [])
    {
        parent::__construct($view, $request, [
            'defaultSortField' => 'ip_address',
            'defaultSortDirection' => pm_View_List_Simple::SORT_DIR_DOWN,
        ]);

        $this->setColumns([
            pm_View_List_Simple::COLUMN_SELECTION,
            'key' => [
                'title' => $this->lmsg('columnTitleKey'),
                'searchable' => true,
                'sortable' => true,
            ],
            'ip_address' => [
                'title' => $this->lmsg('columnTitleIpAddress'),
                'searchable' => true,
                'sortable' => true,
            ],
            'description' => [
                'title' => $this->lmsg('columnTitleDescription'),
                'searchable' => true,
                'sortable' => true,
            ],
        ]);

        $this->setTools([
            [
                'title' => $this->lmsg('buttonTitleRemove'),
                'description' => $this->lmsg('buttonDescriptionRemove'),
                'class' => 'sb-remove-selected',
                'execGroupOperation' => [
                    'skipConfirmation' => false,
                    'subtype' => 'delete',
                    'locale' => ['confirmOnGroupOperation' => $this->lmsg('confirmMessageToRemoveKeys')],
                    'url' => $view->getHelper('baseUrl')->moduleUrl(['action' => 'remove-secret-keys']),
                ],
            ],
        ]);

        $this->setDataUrl(['action' => 'secret-keys-list-data']);
        $keysManager = new Modules_SecretKeysManager_Manager();
        try {
            $this->setData($keysManager->getAllSecretKeys());
        } catch (Exception $e) {
            $view->status->addMessage('error', $e->getMessage());
        }
    }
}
