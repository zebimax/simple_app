<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 27.02.15
 * Time: 15:09
 */

namespace Admin\Controller;


use Admin\Application\Form\LanguageForm;
use Admin\Application\Form\OrdersForm;
use Admin\Application\Model\Mysql\OrdersStats;
use Controller\ApplicationController;
use Form\Component\Field\Input;
use Form\Component\Field\Select;
use Form\Component\TextComponent;
use messageStack;
use Model\Mysql\Languages;

class AdminController extends ApplicationController
{
    protected $isRemoteUser;
    protected $userAdmins;
    protected $remoteUser;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->isRemoteUser = \Macaw::getKey('is_remote_user');
        $this->userAdmins = \Macaw::getKey('admin_users', array());
        $this->remoteUser = $this->defaultValue($_SERVER, 'REMOTE_USER');
    }

    /**
     * @param array $parameters
     * @param string $view
     * @param string $layout
     */
    public function render(array $parameters = array(), $view = '', $layout = 'main')
    {
        $messages = '';
        /** @var messageStack $messageStack */
        $messageStack = \Macaw::getKey('message_stack');
        if (is_object($messageStack) && $messageStack->size > 0) {
            $messages = $messageStack->output();
        }
        $ordersForms = $this->createOrdersForms();
        parent::render(
            array_merge($parameters, array(
                'ordersForms' => $ordersForms,
                'languagesForm' => $this->createLanguagesForm()->make(),
                'messages' => $messages,
                'ordersStats' => $this->getOrdersStats()
            )),
            $view,
            $layout
        );
    }

    /**
     * @return string
     */
    protected function createOrdersForms()
    {
        $inputTextParams = array(
            Input::COMPONENT_NAME => 'oID',
            Input::FIELD_TYPE => Input::TEXT_TYPE,
            Input::FIELD_ATTRIBUTES => array('size' => '12')
        );
        $inputHiddenParams = array(
            Input::COMPONENT_NAME => 'action',
            Input::FIELD_TYPE => Input::HIDDEN_TYPE,
            Input::FIELD_ATTRIBUTES => array('value' => 'edit')
        );
        $ordersForm = new OrdersForm(array(
            array('name' => OrdersForm::TITLE, 'params' => array(TextComponent::TEXT => 'oID: ')),
        ), 'orders.php','get');
        $ordersForm
            ->addComponent(new Input($inputTextParams))
            ->addComponent(new Input($inputHiddenParams));

        return implode(array(
            $ordersForm->make(),
            $ordersForm->removeComponent()->changeComponent(2, 'nameip')->make(),
            $ordersForm->changeComponent(2, 'postcode')->make(),
            $ordersForm->changeComponent(2, 'email')->make(),
            $ordersForm->changeComponent(2, 'tel')->make(),
            $ordersForm->changeComponent(2, 'tt')->make(),
        ));
    }

    /**
     * @return LanguageForm
     */
    protected function createLanguagesForm()
    {
        $options = array(
            array(
                'name' => LanguageForm::LANGUAGES,
                'params' => array(
                    Select::SELECT_OPTIONS => $this->getLanguagesModel()->getLanguagesOptions(),
                    Select::SELECT_SELECTED => $this->langCode
                )
            )
        );
        return new LanguageForm($options, FILENAME_DEFAULT, 'get');
    }

    /**
     * @return Languages
     */
    protected function getLanguagesModel()
    {
        return $this->getModel('Languages');
    }

    protected function getOrdersStats()
    {
        $ordersStats = '';
        if ($this->isRemoteUser && in_array($this->remoteUser, $this->userAdmins)) {
            /** @var OrdersStats $ordersStatsModel */
            $ordersStatsModel = $this->getModel('OrdersStats');
            $ordersStats = implode(' | ', $ordersStatsModel->getOrderStats(function ($row) {
                return sprintf($row['format'], $row['value']);
            }));
        }
        return $ordersStats ? $ordersStats . ' | ' : '';
    }
}