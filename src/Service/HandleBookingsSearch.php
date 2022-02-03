<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class HandleBookingsSearch
{
    public function setParams(Request $request): array
    {
        $params = [];

        if ($request->getMethod() === 'POST') {
            $yearStr =  $request->get('year');
            if (!is_string($yearStr)) {
                throw new Exception('Date is not in string format');
            }

            $monthStr =  $request->get('month');
            if (!is_string($monthStr)) {
                throw new Exception('Date is not in string format');
            }

            $todayStr =  $request->get('day');
            if (!is_string($todayStr)) {
                throw new Exception('Date is not in string format');
            }

            $year = (int) $yearStr;
            $month = (int) $monthStr;
            $today = (int) $todayStr;
        } else {
            $year = (int) date("Y");
            $month = (int) date("m");
            $today = (int) date("d");
        }

        $params['year'] = $year;
        $params['month'] = $month;
        $params['today'] = $today;

        return $params;
    }
}
