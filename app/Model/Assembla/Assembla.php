<?php
namespace Model\Assembla;

use Application\Result;
use Exception;
use Filters\Values\AbstractValueFilter;

class Assembla
{
    /**
     * @var AbstractValueFilter[]
     */
    private $filters = array();
    private $knownFilters = array('eq', 'like');
    private $api;
    private $cachedTickets = array();

    /**
     * @param AssemblaApi $api
     */
    public function __construct(AssemblaApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param array $params
     * @return Result
     * @throws Exception
     */
    public function updateTickets(array $params)
    {
        $result = Result::create(array(
            Result::PARAM_SUCCESS => true,
        ));

        if (!isset($params['conditions']) || !is_array($params['conditions'])) {
            $result->setSuccess(false);
            $result->setMessage('Invalid configs: conditions key must exist and be an array!');
        }
        if (!isset($params['updates']) || !is_array($params['updates'])) {
            $result->setSuccess(false);
            $result->setMessage($result->getMessage() . PHP_EOL . 'Invalid configs: updates key must exist and be an array!');
        }
        $tickets = array();

        foreach ($params['conditions'] as $label => $labelParams) {
            $tickets[$label] = array();
            $filterParams = array();
            if (isset($labelParams['filters'])) {
                $filterParams = $labelParams['filters'];
                try {
                    $this->makeFilters($filterParams);
                } catch (Exception $e) {
                    return $this->returnBadResult($result, $e);
                }
                unset($labelParams['filters']);
            }
            try {
                $labelTickets = $this->getTickets($labelParams);
            } catch (Exception $e) {
                return $this->returnBadResult($result, $e);
            }
            foreach ($labelTickets as $labelTicket) {
                if ($this->filter($labelTicket, $this->filters, $filterParams)) {
                    $tickets[$label][] = $labelTicket;
                }
            }
            $result->setMessage(
                $result->getMessage() . sprintf(
                    '%sFound %d tickets for label %s', PHP_EOL, count($tickets[$label]), $label
                )
            );
        }
        foreach ($params['updates'] as $labelUpdate => $updateParams) {
            $updateResult = $postedResult = 0;
            $numbers = '';
            if (isset($tickets[$labelUpdate]) && isset($params['conditions'][$labelUpdate]['space'])) {
                $space = $params['conditions'][$labelUpdate]['space'];
                if (isset($updateParams['put'])) {
                    $updateResult = $this->api->put(
                        'Tickets',
                        array(
                            'tickets' => $tickets[$labelUpdate],
                            'params' => $updateParams['put'],
                            'space' => $space
                        )
                    );
                }
                if (isset($updateParams['post'])) {
                    $postedResult = $this->api->post(
                        'TicketsComments',
                        array(
                            'tickets' => $tickets[$labelUpdate],
                            'params' => $updateParams['post'],
                            'space' => $space
                        )
                    );
                }
                $breakCounter = 0;
                $numbers = rtrim(array_reduce(
                    $tickets[$labelUpdate],
                    function($carry, $item) use ($breakCounter) {
                        $breakCounter++;
                        $add = $breakCounter % 15 == 0 ? ", \n" : ', ';
                        return "{$carry}#{$item['number']}{$add}";
                    }, ''
                ), ', ');
            }
            $result->setMessage(
                $result->getMessage() . sprintf(
                    '%sUpdated %d tickets for label %s', PHP_EOL, $updateResult, $labelUpdate
                ) .
                sprintf(
                    '%sPosted %d items(comments) for tickets for label %s', PHP_EOL, $postedResult, $labelUpdate
                ).
                sprintf(
                    '%sTickets for label %s:%s%s', PHP_EOL, $labelUpdate, $numbers, PHP_EOL
                )

            );
        }

        return $result;
    }

    /**
     * @param $type
     * @param $key
     * @return AbstractValueFilter
     * @throws Exception
     */
    private function createFilter($type, $key)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->knownFilters)) {
            throw new Exception(
                sprintf('Invalid filter type. Known filters: %s', implode(',', $this->knownFilters))
            );
        }
        $filterClassName = ucfirst($type) . 'Filter';
        $filterClass = "Filters\\Values\\{$filterClassName}";
        return new $filterClass($key);
    }

    /**
     * @param array $item
     * @param array $filters
     * @param array $filterParams
     * @return bool
     */
    private function filter(array $item, array $filters = array(), array $filterParams = array())
    {
        foreach ($filterParams as $filterParam) {
            $filter = isset($filters[$filterParam['type']][$filterParam['key']])
                ? $filters[$filterParam['type']][$filterParam['key']]
                : null;
            if ($filter instanceof AbstractValueFilter) {
                $filter->setValue($filterParam['value']);
                $filterValue = isset($item[$filter->getKey()]) ? $item[$filter->getKey()] : '';
                if (!$filter->filterValue($filterValue)) {
                    return false;
                };
            }
        }
        return true;
    }

    /**
     * @param array $filtersParams
     * @throws Exception
     */
    private function makeFilters(array $filtersParams)
    {
        foreach ($filtersParams as $filtersParam) {
            if (!isset($this->filters[$filtersParam['type']][$filtersParam['key']])) {
                $this->filters[$filtersParam['type']][$filtersParam['key']] =
                    $this->createFilter($filtersParam['type'], $filtersParam['key']);
            }
        }
    }

    /**
     * @param Result $result
     * @param Exception $e
     * @return Result
     */
    private function returnBadResult(Result $result, Exception $e)
    {
        $result->setSuccess(false);
        $result->setMessage($e->getMessage());
        return $result;
    }

    public function getTickets(array $params)
    {
        $cached = $this->tryGetFromCachedTickets($params['range']);
        $params['range'] = $cached['not_found'];
        $tickets = $this->api->get('Tickets', $params);
        $this->cacheTickets($tickets);
        return array_merge($tickets, $cached['cached']);
    }

    private function tryGetFromCachedTickets(array $range)
    {
        $cached = array_intersect_key($this->cachedTickets, array_flip($range));
        return array(
            'cached' => $cached,
            'not_found' => array_diff($range, array_keys($cached))
        );
    }

    private function cacheTickets(array $tickets)
    {
        $this->cachedTickets = array_reduce($tickets, function($carry, $item) {
            if (!isset($carry[$item['number']])) {
                $carry[$item['number']] = $item;
            }
            return $carry;
        }, $this->cachedTickets);
    }
}