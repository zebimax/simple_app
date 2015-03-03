<?php

namespace Controller\Console;

use Controller\AbstractConsoleController;
use Model\Assembla\Assembla;
use Model\Assembla\AssemblaApi;

class ConsoleController extends AbstractConsoleController
{
    public function parseReviewsXmlAction()
    {
        $file = $this->getParam('file');
        if (!is_file($file)) {
            throw new \Exception('No file found');
        }
        if ($content = file_get_contents($file)) {
            $csvFile = MVC_APP_PATH . '/files/csv/reviews_' . date('Y-m-d_H:i:s') . '.csv';
            $f = fopen($csvFile, 'w');
            fputcsv($f, array(
                'review_id',
                'customer_name',
                'customer_email',
                'customer_place',
                'date',
                'total_score',
                'recommendation'
            ));
            $dom = new \DOMDocument();
            $dom->loadXML($content);
            /** @var \DOMNodeList $reviwes */
            foreach ($dom->getElementsByTagName('review') as $review) {
                /** @var \DomElement $review */
                /** @var \DomElement $customer */
                $customer = $review->getElementsByTagName('customer')->item(0);
                fputcsv($f, array(
                    $review->getElementsByTagName('id')->item(0)->nodeValue,
                    $customer->getElementsByTagName('name')->item(0)->nodeValue,
                    $customer->getElementsByTagName('email')->item(0)->nodeValue,
                    $customer->getElementsByTagName('place')->item(0)->nodeValue,
                    $customer->getElementsByTagName('date')->item(0)->nodeValue,
                    $review->getElementsByTagName('total_score')->item(0)->nodeValue,
                    $review->getElementsByTagName('recommendation')->item(0)->nodeValue
                ));
            }
            echo sprintf('file saved in %s', $csvFile);
            return;
        }
        throw new \Exception('Can\'t get file content');
    }

    public function updateAssemblaTickets()
    {
        $xApiKey = $this->getParam('x-api-key');
        $xApiSecret = $this->getParam('x-api-secret');

        $config = $this->getAssemblaParams($xApiKey, $xApiSecret, $this->getParam('action'), 'update');

        $assembla = $this->getAssembla($xApiKey, $xApiSecret);
        $result = $assembla->updateTickets($config);
        $color = $result->getSuccess() ? 'green' : 'red';
        exit($this->getColorizedString($result->getMessage(), $color) . PHP_EOL);
    }

    public function getInfoAssemblaTickets()
    {
        $xApiKey = $this->getParam('x-api-key');
        $xApiSecret = $this->getParam('x-api-secret');

        $config = $this->getAssemblaParams($xApiKey, $xApiSecret, $this->getParam('action'), 'get_info');
        $fileName = $config['file_name'];
        $fieldsParams = $config['fields'];
        unset($config['file_name'], $config['fields']);
        $assembla = $this->getAssembla($xApiKey, $xApiSecret);
        $this->createTicketsCsv($fileName, $assembla->getTickets($config), $fieldsParams);
        exit($this->getColorizedString("results saved in file {$fileName}", 'green') . PHP_EOL);
    }

    /**
     * @param $xApiKey
     * @param $xApiSecret
     * @return Assembla
     */
    private function getAssembla($xApiKey, $xApiSecret)
    {
        $assembla = new Assembla(
            new AssemblaApi(
                array(
                    'url' => \Macaw::getConfig('assembla_api_url'),
                    'x-api-key' => $xApiKey,
                    'x-api-secret' => $xApiSecret
                )));
        return $assembla;
    }

    /**
     * @param $xApiKey
     * @param $xApiSecret
     * @param $action
     * @param $job
     * @return null
     */
    private function getAssemblaParams($xApiKey, $xApiSecret, $action, $job)
    {
        if (!$xApiKey || !$xApiSecret || !$action) {
            exit($this->getColorizedString('X-Api-Key or X-Api-Secret or action are not provided!', 'red'));
        }

        $config = \Macaw::getInsertedConfig("assembla_tickets/{$job}/{$action}");
        if (empty($config) || !is_array($config)) {
            exit($this->getColorizedString('No configs found for this action!', 'red'));
        }
        if (is_callable($config['range'])) {
            $config['range'] = $config['range']();
        }
        return $config;
    }

    /**
     * @param $fileName
     * @param $tickets
     * @param $fieldsParams
     */
    private function createTicketsCsv($fileName, $tickets, $fieldsParams)
    {
        $fp = fopen($fileName, 'w');

        foreach ($tickets as $ticket) {
            $fields = array();
            foreach ($fieldsParams as $fieldName => $fieldValue) {
                $fields[$fieldName] = is_callable($fieldValue) ? $fieldValue($ticket) : $ticket[$fieldValue];
            }
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }
}