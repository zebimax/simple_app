<?php
namespace Admin\Controller;

use Application\Result;

/**
 * Class ExpensiveController
 * @package Admin\Controller
 * @method mixed paginationLinksMaker($count, $current, $byPage, $pageLinks = 5)
 */
class ExpensiveUnipharmaController extends AdminController
{

    protected $defaultLimit = 50;

    public function IndexAction()
    {
        $this->title = 'Expensive than retail advice price';
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $list = $this->getProductsModel()->getUnipharmaExpensiveList(
            array(
                'limit' => $limit,
                'start' => $offset * $limit,
            )
        );
        $this->render(array(
            'list' => $list,
            'paginationLinks' => $this->paginationLinksMaker($list['count'], $offset + 1, $limit)
        ));
    }

    public function getListAction()
    {
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $list = $this->getProductsModel()->getUnipharmaExpensiveList(
            array(
                'limit' => $limit,
                'start' => $offset * $limit,
            )
        );
        $this->renderJson(
            Result::create(array(
                    Result::PARAM_SUCCESS => true,
                    Result::PARAM_DATA => array(
                        'list' => $list,
                        'pagination' => $this->paginationLinksMaker(
                            $list['count'],
                            $offset + 1,
                            $limit
                        ),
                    )
                )
            )
        );
    }

    /**
     * @return \Model\Mysql\Products
     */
    private function getProductsModel()
    {
        return $this->getModel('Products');
    }
}