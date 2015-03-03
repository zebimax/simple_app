<?php
/**
 * Created by PhpStorm.
 * User: comp3
 * Date: 12.01.15
 * Time: 14:44
 */

namespace Redirect;


use Model\Mysql\Manufacturers;

class ManufacturerRedirect extends AbstractRedirect implements ValidateRedirectInterface
{
    const SHORTS_WORDS_LIMIT = 3;
    /**
     * @var Manufacturers
     */
    private $manufacturersModel;
    private $options = array(
        'SEO_CHAR_CONVERT_SET' => array(),
        'SEO_REMOVE_ALL_SPEC_CHARS' => '',
        'SEO_URLS_FILTER_SHORT_WORDS' => 0
    );

    public function __construct(Manufacturers $model)
    {
        $this->manufacturersModel = $model;
        $this->options['SEO_CHAR_CONVERT_SET'] = defined('SEO_CHAR_CONVERT_SET') && SEO_CHAR_CONVERT_SET
            ? $this->expand(SEO_CHAR_CONVERT_SET)
            : array();
        $this->options['SEO_REMOVE_ALL_SPEC_CHARS'] =
            defined('SEO_REMOVE_ALL_SPEC_CHARS') && SEO_REMOVE_ALL_SPEC_CHARS == 'true'
                ? "([^[:alnum:]])"
                : "/[^a-z0-9- ]/i";
        $this->options['SEO_URLS_FILTER_SHORT_WORDS'] =
            defined('SEO_URLS_FILTER_SHORT_WORDS') && (int)SEO_URLS_FILTER_SHORT_WORDS
                ? (int)SEO_URLS_FILTER_SHORT_WORDS
                : false;
    }

    public function validateRedirect($requestUri)
    {
        preg_match('#^(.*)-mm-(.*).html$#', ltrim($requestUri, '/'), $matches);
        if ($matches[1] && $matches[2]) {
            if ($brand = $this->manufacturersModel->getBrandById($matches[2])) {
                $realName = $this->canonicalizeName($brand['manufacturers_name']);
                if ($realName != $matches[1]) {
                    $this->redirect(sprintf('/%s-mm-%d.html', $realName, $matches[2]));
                }
            }
        }
    }

    private function expand($set)
    {
        $container = array();
        foreach (explode(',', $set) as $index => $valuePair){
            $pair = explode('=>', $valuePair);
            $container[trim($pair[0])] = trim($pair[1]);
        }
        return $container;
    }

    private function canonicalizeName($name)
    {
        $str = strtr(strtolower($name), $this->options['SEO_CHAR_CONVERT_SET']);
        return $this->shortName(
            preg_replace(
                array('/((&#39))/', $this->options['SEO_REMOVE_ALL_SPEC_CHARS'], '([[:space:]]|[[:blank:]])'),
                array('-', '', '-'),
                $str
            )
        );
    }

    private function shortName($str, $limit = self::SHORTS_WORDS_LIMIT)
    {
        if ($this->options['SEO_URLS_FILTER_SHORT_WORDS']) {
            $limit = $this->options['SEO_URLS_FILTER_SHORT_WORDS'];
        }
        $exploded = explode('-', $str);
        $container = '';
        foreach($exploded as $index => $value) {
            switch (true){
                case (strlen($value) <= $limit):
                    continue;
                default:
                    $container[] = $value;
                    break;
            }
        }
        return (sizeof($container) > 1) ? implode('-', $container) : $str ;
    }

}