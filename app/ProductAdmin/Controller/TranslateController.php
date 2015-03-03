<?php
namespace ProductAdmin\Controller;

use Application\Result;
use BasicLogger\Writers\MysqlWriter;
use Controller\ApplicationController;
use Model\Mysql\Categories;
use Model\Mysql\Languages;
use Model\Mysql\Manufacturers;

/**
 * Class TranslateController
 * @package ProductAdmin\Controller
 * @method mixed paginationLinksMaker($count, $current, $byPage, $pageLinks = 5)
 */
class TranslateController extends ApplicationController
{
    const SUCCESS_SAVE_MSG_TPL = 'Saved product with id %s for language %s';
    const ERROR_SAVE_MSG_TPL = 'Error occurred! Product with id %s for language %s did not saved';

    protected $baseLanguage;
    protected $translateLanguage;
    protected $languages;

    protected  $defaultLimit = 10;
    private $logger;

    public function __construct($action = 'index')
    {
        $this->logger = new \BasicLogger(
            array(
                new MysqlWriter(
                    $this->getModel('EditorLog'),
                    \Macaw::getConfig('mysql_log_editor')
                )
            )
        );
        parent::__construct($action);
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $this->setLanguages();
    }

    public function indexAction()
    {
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $list = $this->getProductsDescriptionModel()->getTranslateList(
            array(
                'base_language_id' => $this->baseLanguage,
                'translate_language_id' => $this->translateLanguage,
                'limit' => $limit,
                'start' => $offset * $limit,
            )
        );
        $paginationLinksMaker = $this->paginationLinksMaker(
            $list['count'],
            $offset + 1,
            $limit
        );
        $this->render(
            array(
                'list' => $list,
                'limit' => $limit,
                'languages' => $this->languages,
                'categories' => $this->getCategoriesList(),
                'brands' => $this->getBrandsList(),
                'paginationLinks' => $paginationLinksMaker,
            ), '', $this->getLayout()
        );
    }

    public function getListAction()
    {
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $checkTranslated = (int)$this->defaultValue($_POST, 'check_translated');
        $list = $this->getProductsDescriptionModel()->getTranslateList(
            array(
                'base_language_id' => $this->baseLanguage,
                'translate_language_id' => $this->translateLanguage,
                'category_id' => (int)$this->defaultValue($_POST, 'category_id'),
                'brand_id' => (int)$this->defaultValue($_POST, 'brand_id'),
                'names_not_translated' => (int)$this->defaultValue($_POST, 'not_translated_name'),
                'descriptions_not_translated' => (int)$this->defaultValue($_POST, 'not_translated_description'),
                'taglines_not_translated' => (int)$this->defaultValue($_POST, 'not_translated_tagline'),
                'not_exist_translate' => $checkTranslated === 1,
                'exist_translate' => $checkTranslated === 2,
                'limit' => $limit,
                'start' => $offset * $limit
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

    public function saveAction()
    {
        $productId = (int)$this->defaultValue($this->request, 'id');
        $translateLanguageId = (int)$this->defaultValue($this->request, 'translate_language_id');

        $data = array(
            'name' => $this->defaultValue($this->request, 'name'),
            'description' => $this->defaultValue($this->request, 'description'),
            'tagline' => $this->defaultValue($this->request, 'tagline')
        );
        $result = $this->getProductsDescriptionModel()->saveTranslatedProduct(
            $productId,
            $translateLanguageId,
            $data
        );
        $languageName = $this->getLanguageName($translateLanguageId);
        $message = $result
            ? sprintf(
                self::SUCCESS_SAVE_MSG_TPL,
                $productId,
                $languageName
            )
            : sprintf(
                self::ERROR_SAVE_MSG_TPL,
                $productId,
                $languageName
            );

        $this->logger->info(array(
            MysqlWriter::TEXT => $message,
            MysqlWriter::LABEL => 'translate_products',
            MysqlWriter::USER => $this->getUser(),
            MysqlWriter::UNIQUE_ID => sprintf('%d_%d', $productId, $translateLanguageId),
            MysqlWriter::ADDITIONAL => $data
        ));
        $this->renderJson(
            Result::create(array(
                Result::PARAM_SUCCESS => $result,
                Result::PARAM_MESSAGE => $message
                )
            )
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function getLanguageName($id)
    {
        /** @var Languages $languages */
        $languages = $this->getModel('Languages');
        $language = $languages->getLanguage($id);
        return $language['name'];
    }

    protected function setTitle()
    {
        switch ($this->action) {
            case 'index':
                $this->title = 'index';
                break;
            case 'specials':
                $this->title = 'other';
                break;
            default:
                $this->title = '';
                break;
        }
    }

    /** @return Categories */
    protected function getCategoriesModel()
    {
        return $this->getModel('Categories');
    }

    /** @return Manufacturers */
    protected function getManufacturersModel()
    {
        return $this->getModel('Manufacturers');
    }

    protected function setLanguages()
    {
        /** @var Languages $langModel */
        $langModel = $this->getModel('Languages');

        foreach ($langModel->getList() as $lang) {
            $this->languages[$lang['id']] = array_merge(
                $lang,
                array('is_selected_translate' => false, 'is_selected_base' => false)
            );
        }

        $languageId = (int)$this->defaultValue($_POST, 'base_language_id', 0);
        $this->baseLanguage = isset($this->languages[$languageId])
            ? $languageId
            : \Macaw::getConfig('translate_base_language_id');

        $this->languages[$this->baseLanguage]['is_selected_base'] = true;

        $translateLanguageId = (int)$this->defaultValue($_POST, 'translate_language_id', 0);
        $this->translateLanguage = isset($this->languages[$translateLanguageId])
            ? $translateLanguageId
            : \Macaw::getConfig('translate_language_id');
        $this->languages[$this->translateLanguage]['is_selected_translate'] = true;
    }

    /**
     * @return array|null
     */
    private function getCategoriesList()
    {
        $categories = $this->getCategoriesModel()->getList();
        array_unshift(
            $categories,
            array('id' => 0, 'long_name' => '')
        );
        return $categories;
    }

    /**
     * @return array|null
     */
    private function getBrandsList()
    {
        $brands = $this->getManufacturersModel()->getList();
        array_unshift(
            $brands,
            array('id' => 0, 'name' => '')
        );
        return $brands;
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : 'unknown';
    }
}