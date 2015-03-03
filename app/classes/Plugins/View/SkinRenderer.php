<?php

namespace Plugins\View;


class SkinRenderer
{
    private $skin;
    private $parts = array();
    private $part;
    private $skinWhiteHeaderArray = array(
        'menzis', 'xls', 'predictor', 'biodavitymea', 'biodermal', 'wartner', 'gehwol', 'kerst',
        'waterpokken', 'waterwratjes', 'wratweg', 'nailner', 'unicare', 'dermalex', 'cattier',
        'nuon', 'sabai', 'eyefresh', 'easydiet', 'ibdtrade', 'prettyeyes', 'clearasil', 'veet',
        'strepsils', 'gaviscon', 'scholl', 'durexplay', 'durex', 'dettol', 'skintags', 'heltiq',
        'niveamen', 'kernpharm', 'ehbg', 'nhs', 'megared', 'bional', 'cranmed', 'lactacyd',
        'kawaeyes', 'azaron', 'tantum', 'prevalin', 'jfm', 'leidapharm', 'actimove', 'zantac', 'feestdagen'
    );

    public function __construct()
    {
        $this->parts = array(
            'header_top' => array(
                'main' => array('content' => 'file'),
                'classic' => ''
            ),
            'header_middle' => array(
                'main' => array('content' => 'file'),
                'classic' => array(
                    'content' => 'file',
                    'additional' => array(
                        'skinWhiteHeaderArray' => $this->skinWhiteHeaderArray
                    )
                )
            ),
            'brand_ad' => array(
                'skins/limisan' => array('content' => 'file'),
                'skins/niveamen' => array('content' => '<div id="niveamen_ad"></div>'),
                'skins/ehbg' => array('content' => '<div id="ehbg_ad_1"></div>'),
                'skins/actimove' => array('content' => 'file')
            )
        );
    }

    /**
     * @param $part
     * @param $skin
     * @param array $additional
     * @return string
     */
    public function __invoke($part, $skin, array $additional = array())
    {
        $this->part = $part;
        $this->skin = $skin;
        return $this->getPart($part, $skin, $additional);
    }

    /**
     * @param $part
     * @param $skin
     * @param array $additional
     * @return string
     */
    private function getPart($part, $skin, array $additional = array())
    {
        $partContent = '';
        if (isset($this->parts[$part][$skin])) {
            $params = isset($this->parts[$part][$skin]['additional']) && is_array($this->parts[$part][$skin]['additional'])
                ? array_merge($this->parts[$part][$skin]['additional'], $additional)
                : $additional;
            $partContent = $this->makePartContent($this->parts[$part][$skin], $params);
        }
        return $partContent;
    }

    /**
     * @param $params
     * @param array $additional
     * @return string
     */
    private function makePartContent($params, array $additional)
    {
        $content = '';
        switch (true) {
            case is_string($params):
                $content = $params;
                break;
            case isset($params['content']):
                if ($params['content'] === 'file') {
                    $fileName = MVC_VIEW_DIR . $this->skin . DIRECTORY_SEPARATOR . $this->part . '.phtml';
                    if (is_file($fileName)) {
                        ob_start();
                        extract($additional);
                        include $fileName;
                        $content = ob_get_contents();
                        ob_end_clean();
                    }
                } elseif (is_callable($params['content'])) {
                    $content = $params['content']();
                } elseif (is_string($params['content'])) {
                    $content = $params['content'];
                }
                break;
            default:
                break;
        }
        return $content;
    }
}