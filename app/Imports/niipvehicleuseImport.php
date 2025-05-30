<?php

namespace App\Imports;

use App\Models\niipvehicleuse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class niipvehicleuseImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new niipvehicleuse([
             'niipuseid' => $row['niipuseid'],  // Maps 'niipuseid' in Excel to 'niipuseid' in DB
            'usename' => $row['use'], // Maps 'use' in Excel to 'usename' in DB
        ]);
    }
}
