<?php

namespace elegisandi\AWSElasticsearchService;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Trait ElasticSearchHelper
 * @package elegisandi\AWSElasticsearchService\Traits
 */
trait ElasticSearchHelper
{
    /**
     * @var string
     */
    protected $config = 'elasticsearch';

    /**
     * @param Request $request
     * @param array $defaults
     * @param string $type
     * @return array|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function setSearchParams(Request $request, $defaults = [], $type = 'click')
    {
        $fillables = array_keys(config($this->config)['mappings'][$type]['properties']);
        $query = array_filter($request->only($fillables));

        if ($query && !empty($query['src']) && $query['src'] == 'all') {
            unset($query['src']);
        }

        // set date range
        $date_range = [];
        $range = $request->input('range', 'all-time');

        if ($range == 'custom') {
            $start = $request->input('start');
            $end = $request->input('end');
        } elseif ($range && $range != 'all-time') {
            extract($this->getDateRange($range));
        }

        // set default values
        $defaults = array_merge([
            'sort' => 'timestamp',
            'order' => 'desc',
            'size' => 30
        ], $defaults);

        // set sorting and paging options
        $sort = $request->input('sort', $defaults['sort']);
        $order = $request->input('order', $defaults['order']);
        $size = (int)$request->input('size', $defaults['size']);

        // parse and set start date if valid
        if (!empty($start)) {
            try {
                $date_range['timestamp']['gte'] = Carbon::parse($start)->startOfDay()->toDateTimeString();
            } catch (Exception $e) {
                $invalid_start = true;
                $start = Carbon::now()->subWeek()->toDateString();
            }
        }

        // parse and set end date if valid
        if (!empty($end)) {
            try {
                $date_range['timestamp']['lte'] = Carbon::parse($end)->endOfDay()->toDateTimeString();
            } catch (Exception $e) {
                $invalid_end = true;
                $end = Carbon::yesterday()->toDateString();
            }
        }

        if (!empty($date_range['timestamp'])) {
            $date_range['timestamp']['format'] = 'yyyy-MM-dd HH:mm:ss';
            $date_range['timestamp']['time_zone'] = config('app.timezone');
        }

        // validate sort
        if (($invalid_sort = !in_array($sort, $fillables))) {
            $sort = $defaults['sort'];
        }

        // validate order
        if ($invalid_order = !in_array($order, ['desc', 'asc'])) {
            $order = $defaults['order'];
        }

        // validate size
        if ($invalid_size = $size < 1) {
            $size = $defaults['size'];
        }

        $filters = array_filter(compact('range', 'start', 'end', 'sort', 'order', 'size'));

        // redirect if has invalid value(s)
        if (!empty($invalid_start) || !empty($invalid_end) || !empty($invalid_sort) || !empty($invalid_order) || !empty($invalid_size)) {
            return redirect($request->fullUrlWithQuery($filters));
        }

        // search_after
        if (($search_after = $request->input('search_after')) && !is_array($search_after)) {
            $search_after = explode(',', $search_after);
        }

        $options = array_merge(
            array_filter(compact('search_after', 'size')),
            [
                'from' => -1,
                'sort' => [
                    $sort => compact('order'),
                    '_uid' => [
                        'order' => 'asc'
                    ]
                ]
            ]
        );

        return compact('query', 'date_range', 'filters', 'options');
    }

    /**
     * @param $range
     * @return array
     */
    protected function getDateRange($range)
    {
        switch ($range) {
            case 'today':
                $start = Carbon::now()->startOfDay()->toDateString();
                $end = Carbon::now()->endOfDay()->toDateString();
                break;

            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay()->toDateString();
                $end = Carbon::yesterday()->endOfDay()->toDateString();
                break;

            case 'this-month':
                $start = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->endOfDay()->toDateString();
                break;

            case 'last-month':
                $start = Carbon::now()->subMonth()->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            case 'last-2-months':
                $start = Carbon::now()->subMonths(2)->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            case 'last-3-months':
                $start = Carbon::now()->subMonths(3)->startOfMonth()->startOfDay()->toDateString();
                $end = Carbon::now()->subMonth()->endOfMonth()->endOfDay()->toDateString();
                break;

            default: // last-7-days
                $start = Carbon::now()->subWeek()->startOfDay()->toDateString();
                $end = Carbon::yesterday()->endOfDay()->toDateString();
                break;
        }

        return compact('start', 'end');
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    protected function setAggregationDailyDateRanges($start, $end)
    {
        $date_ranges = [];

        try {
            $from = Carbon::parse($start)->startOfDay()->timestamp;
            $to = Carbon::parse($end)->endOfDay()->timestamp;

            while ($from <= $to) {
                $date_ranges[] = [
                    'from' => Carbon::createFromTimestamp($from)->format('M d, Y'),
                    'to' => Carbon::createFromTimestamp($from += 24 * 60 * 60)->format('M d, Y')
                ];
            }
        } catch (Exception $e) {
        }

        return $date_ranges;
    }

    /**
     * @return array
     */
    protected function defaultAggregationNames()
    {
        return config($this->config)['default_aggregation_names'];
    }
}
