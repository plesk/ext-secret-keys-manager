<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

class IndexController extends pm_Controller_Action
{
    protected $_accessLevel = ['admin'];

    public function indexAction()
    {
        $this->_redirect('index/secret-keys-list');
    }

    public function secretKeysListAction()
    {
        $this->view->pageTitle = $this->lmsg('pageTitleSecretKeysList');
        $this->view->secretKeysList = new Modules_SecretKeysManager_List_SecretKeys($this->view, $this->_request);
    }

    public function secretKeysListDataAction()
    {
        $secretKeysList = new Modules_SecretKeysManager_List_SecretKeys($this->view, $this->_request);
        $this->_helper->json($secretKeysList->fetchData());
    }

    public function removeSecretKeysAction()
    {
        $statusMessages = [];

        $keysManager = new Modules_SecretKeysManager_Manager();
        try {
            $result = $keysManager->removeSecretKey((array)$this->_getParam('ids'));
        } catch (Exception $e) {
            $statusMessages[] = [
                'status' => 'error',
                'content' => $e->getMessage(),
            ];
            $this->_helper->json(['status' => 'success', 'statusMessages' => $statusMessages]);
        }

        // prepare report
        $success = [];
        $error = [];
        foreach ($result as $res) {
            if ('ok' == $res['status']) {
                $success[] = $this->lmsg('successMessageRemoveKey', $res);
            } else {
                $error[] = $this->lmsg('errorMessageRemoveKeyFail', $res);
            }
        }

        if ($success) {
            $statusMessages[] = [
                'status' => 'info',
                'content' => $this->lmsg('successMessageRemoveKeys') . '<br />' .  join('<br />', $success),
            ];
        }
        if ($error) {
            $statusMessages[] = [
                'status' => 'error',
                'content' => $this->lmsg('errorMessageRemoveKeysFail') . '<br />' .  join('<br />', $error),
            ];
        }
        // send report
        $this->_helper->json(['status' => 'success', 'statusMessages' => $statusMessages]);
    }

}
