<?php

namespace App\Imports;

use App\Models\vehiclecolor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class vehiclecolorImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new vehiclecolor([
                     'colorid' => $row['colorid'],   // Maps 'vmake_id' in Excel to 'niipvmid' in DB
            'color' => $row['colorname'], // Maps 'vmake' in Excel to 'vmake' in DB
        ]);
    }
}
