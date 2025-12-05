<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assets = [
            [
                'name' => '10 ft Open Trailer',
                'model' => null,
                'type' => 'equipment_trailer',
                'identifier' => '5LVBU10119A019583',
                'status' => 'active',
                'purchase_price' => 2000,
                'notes' => null,
            ],
            [
                'name' => '16 ft Enclosed Trailer',
                'model' => 'QW8X16TA3',
                'type' => 'enclosed_trailer',
                'identifier' => '53EP8G1620KF047964',
                'status' => 'active',
                'purchase_price' => 6500,
                'notes' => null,
            ],
            [
                'name' => '20 ft Enclosed Trailer',
                'model' => 'JV85X20TE3',
                'type' => 'enclosed_trailer',
                'identifier' => '53BPTEB27G0U17206',
                'status' => 'active',
                'purchase_price' => 7500,
                'notes' => null,
            ],
            [
                'name' => 'Iron Bull 20\' Trailer',
                'model' => 'EWB8320-072',
                'type' => 'equipment_trailer',
                'identifier' => '3EUGB2029N1005036',
                'status' => 'active',
                'purchase_price' => 7900,
                'notes' => '14000 GVW',
            ],
            [
                'name' => 'Bobcat Mini Skid Steer',
                'model' => 'MT-100',
                'type' => 'skid_steer',
                'identifier' => 'B52P32931',
                'status' => 'active',
                'purchase_price' => 38000,
                'notes' => 'Attachments Available',
            ],
            [
                'name' => 'Ford Crew Truck',
                'model' => 'F-250',
                'type' => 'crew_truck',
                'identifier' => '1FT7W2BAXPED06148',
                'status' => 'active',
                'purchase_price' => 54000,
                'notes' => 'Crew Cab',
            ],
            [
                'name' => 'Ford Dump Truck',
                'model' => 'F-350',
                'type' => 'dump_truck',
                'identifier' => '1FDRF3GT2PEC59119',
                'status' => 'active',
                'purchase_price' => 77000,
                'notes' => 'Regular Cab',
            ],
            [
                'name' => 'Big Tex Dump Trailer',
                'model' => '16LP-16BXK-P4',
                'type' => 'dump_trailer',
                'identifier' => '16V1D2122P5288237',
                'status' => 'active',
                'purchase_price' => 19500,
                'notes' => '17500 GVW',
            ],
            [
                'name' => 'GMC Crew Truck',
                'model' => 'Sierra 2500',
                'type' => 'crew_truck',
                'identifier' => '1GT11EEG7JF165851',
                'status' => 'active',
                'purchase_price' => 54000,
                'notes' => 'Crew Cab',
            ],
            [
                'name' => 'GMC Dump Truck',
                'model' => 'Sierra 3500',
                'type' => 'dump_truck',
                'identifier' => '1GD42VC86GF258052',
                'status' => 'active',
                'purchase_price' => 62000,
                'notes' => 'Crew Cab',
            ],
            [
                'name' => 'P J 18\' Trailer',
                'model' => 'C5202',
                'type' => 'equipment_trailer',
                'identifier' => '4P5C52022A22146858',
                'status' => 'active',
                'purchase_price' => 3500,
                'notes' => '7000 GVW',
            ],
            [
                'name' => 'CAT Skid Steer',
                'model' => '259D',
                'type' => 'skid_steer',
                'identifier' => 'FTL23451',
                'status' => 'active',
                'purchase_price' => 60500,
                'notes' => 'Attachments Available',
            ],
            [
                'name' => 'CAT Mini Excavator',
                'model' => '302.7-07CR',
                'type' => 'excavator',
                'identifier' => 'C2G03450',
                'status' => 'active',
                'purchase_price' => 64000,
                'notes' => 'GVW 6725',
            ],
            [
                'name' => '48" Gravely Stander #1',
                'model' => '994132',
                'type' => 'mowers',
                'identifier' => '30042',
                'status' => 'active',
                'purchase_price' => 10500,
                'notes' => 'mulching',
            ],
            [
                'name' => '48" Gravely Stander #2',
                'model' => '994132',
                'type' => 'mowers',
                'identifier' => '000176',
                'status' => 'active',
                'purchase_price' => 10500,
                'notes' => 'mulching',
            ],
        ];

        foreach ($assets as $assetData) {
            Asset::create($assetData);
        }
    }
}

