<?php

namespace App\Imports;

use App\Models\states;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class stateImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new states([
            'stateid' => $row['stateid'],   // Maps 'vmake_id' in Excel to 'niipvmid' in DB
            'statename' => $row['statename'], // Maps 'vmake' in Excel to 'vmake' in DB
        ]);
    }
}
