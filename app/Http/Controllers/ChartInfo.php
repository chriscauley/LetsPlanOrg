<?php

namespace App\Http\Controllers;

use App\Models\BldgPermit;
use App\Models\Parcel;
use App\Models\RealEstateTx;
use App\Models\Traits\DefinesLandUseZones;
use Illuminate\Http\Request;

class ChartInfo extends Controller
{
    use DefinesLandUseZones;

    public function zoningChartInfo(Request $request, $rcoId)
    {
        $table = 'project_parcels_2';
        if ($rcoId == null) {
            $table = 'project_parcels_2';
        }
        $parcels = \DB::table($table)
            ->select([
                \DB::raw("COUNT($table.id) as cnt"),
                \DB::raw("RTRIM(zoning_land_use.zoning) as zoning"),
//                \DB::raw("(CASE RTRIM(\"zoning\") WHEN 'RSA5', 'RDA1' THEN 1 END) as \"residential\" ")
            ])
            ->leftJoin('atlas_data', 'atlas_data.parcel_id', $table . '.parcel_id')
            ->leftJoin('zoning_land_use', 'zoning_land_use.opa_account_num', 'atlas_data.opa_account_num')
            ->groupBy([
                'zoning_land_use.zoning',
            ])
            ->cursor();

        //total count of all properties that have a zoning value
        $totalCount = $parcels->sum(function ($item) {
            return $item->zoning ? $item->cnt : 0;
        });
        $industrialPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$industrial) ? $item->cnt : 0;
        });
        $industrialPct = round($industrialPct / $totalCount * 100) / 100;

        $residentialLowPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$residential) ? $item->cnt : 0;
        });
        $residentialLowPct = round($residentialLowPct / $totalCount * 100) / 100;

        $residentialHighPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$residentialHigh) ? $item->cnt : 0;
        });
        $residentialHighPct = round($residentialHighPct / $totalCount * 100) / 100;

        $commercialLowPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$commercial) ? $item->cnt : 0;
        });
        $commercialLowPct = round($commercialLowPct / $totalCount * 100) / 100;

        $commercialHighPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$commercialHigh) ? $item->cnt : 0;
        });
        $commercialHighPct = round($commercialHighPct / $totalCount * 100) / 100;

        $specialPct = $parcels->sum(function ($item) {
            return in_array($item->zoning, self::$special) ? $item->cnt : 0;
        });
        $specialPct = round($specialPct / $totalCount * 100) / 100;


        return response()->json([
            'data' => [
                'type' => 'dataset',
                'id'   => 'zoning-x-axis',
                'attributes' => array_merge(
                    [
                        'labels' => ['Industrial', 'Lo Res', 'Hi Res', 'Lo Com', 'Hi Com', 'Special'],
                        'data' => [
                            $industrialPct,
                            $residentialLowPct,
                            $residentialHighPct,
                            $commercialLowPct,
                            $commercialHighPct,
                            $specialPct
                        ],
                    ]
                ),
            ]
        ]);
    }

    public function salesChartInfo(Request $request, $rcoId)
    {
        $table = 'project_parcels_2';
        if ($rcoId == null) {
            $table = 'project_parcels_2';
        }

        $parcels = \DB::table($table)
            ->select([
                \DB::raw("AVG(sale_price_adj)"),
                'sale_year',
            ])
            ->leftJoin('atlas_data', 'atlas_data.parcel_id', $table . '.parcel_id')
            ->leftJoin('real_estate_tx', 'real_estate_tx.opa_account_num', 'atlas_data.opa_account_num')
            ->groupBy([
                'real_estate_tx.sale_year',
            ])
            ->whereNotNull('sale_year')
            ->orderBy('sale_year', 'ASC')
            ->get();


        return response()->json([
            'data' => [
                'type' => 'dataset',
                'id'   => 'sales-x-axis',
                'attributes' => array_merge(
                    [
                        'labels' => $parcels->pluck('sale_year'),
                        'data' => $parcels->pluck('avg'),
                    ]
                ),
            ]
        ]);
    }

    public function permitsChartInfo(Request $request, $rcoId)
    {
        return response()->json([
            'data' => [
                'type' => 'dataset',
                'id'   => 'permits-c-x-axis',
                'attributes' => array_merge(
                    [
                        'labels' => ['2011', '2012', '2013', '2014', '2015'],
                        'data' => [
                            100, 125, 200, 225, 500,
                        ],
                    ]
                ),
            ]
        ]);
    }

    public function adjustmentPermitsChartInfo(Request $request, $rcoId)
    {
        return response()->json([
            'data' => [
                'type' => 'dataset',
                'id'   => 'permits-a-x-axis',
                'attributes' => array_merge(
                    [
                        'labels' => ['2011', '2012', '2013', '2014', '2015'],
                        'data' => [
                            200, 225, 375, 525, 715,
                        ],
                    ]
                ),
            ]
        ]);
    }
}
