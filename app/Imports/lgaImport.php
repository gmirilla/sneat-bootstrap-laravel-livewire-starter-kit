<?php

namespace App\Imports;

use App\Models\lga;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class lgaImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new lga([
            'lgaid' => $row['lgaid'],   // Maps 'lgaid' in Excel to 'niipvmid' in DB
            'lganame' => $row['lganame'], // Maps 'lganame' in Excel to 'vmake' in DB
            'stateid' => $row['stateid'], // Maps 'stateid' in Excel to 'stateid' in DB
        ]);
    }
}
