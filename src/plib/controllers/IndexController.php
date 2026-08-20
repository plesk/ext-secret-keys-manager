<?php
// Copyright 1999-2026. WebPros International GmbH.

use Plesk\CommonPanel\Validate\IpAddress\IpAddress;

class IndexController extends pm_Controller_Action
{
    protected $_accessLevel = ['admin'];

    protected function _checkAccessLevel()
    {
        parent::_checkAccessLevel();

        if (!Modules_SecretKeysManager_Visibility::isAccessAllowed()) {
            throw new \pm_Exception($this->lmsg('restrictedMode.accessDeniedMessage'));
        }
    }

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

    public function createAction()
    {
        $this->view->pageTitle = $this->lmsg('pageTitleCreateSecretKey');

        $keysManager = new Modules_SecretKeysManager_Manager();
        $accountOptions = $keysManager->getAccountOptions();

        $form = new pm_Form_Simple();
        $form->addElement('select', 'owner', [
            'label' => $this->lmsg('keyOwner'),
            'multiOptions' => $accountOptions,
            'validators' => [
                new Zend_Validate_InArray([
                    'haystack' => array_keys($accountOptions),
                    'strict' => true,
                ]),
            ],
        ]);
        $form->addElement('text', 'ipAddress', [
            'label' => $this->lmsg('ipAddressRestriction'),
            'validators' => [
                [new IpAddress(), true],
            ],
        ]);
        $form->addElement('text', 'keyDescription', [
            'label' => $this->lmsg('keyDescription'),
        ]);

        $form->addControlButtons([
            'cancelLink' => pm_Context::getBaseUrl(),
        ]);

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $secretKey = $keysManager->createSecretKey(
                $form->getValue('ipAddress'),
                $form->getValue('keyDescription'),
                $form->getValue('owner'),
            );

            $this->_status->addMessage(
                'info',
                $this->lmsg('createdSecretKey', ['key' => htmlspecialchars($secretKey)]),
                true
            );
            $this->_helper->json(['redirect' => pm_Context::getBaseUrl()]);
        }

        $this->view->form = $form;
    }
}
