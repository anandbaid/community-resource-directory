<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrganizationExport implements FromView
{
    private $data, $header;
    public function __construct(array $data, array $header)
    {
        $this->data = $data;
        $this->header = $header;
    }
    public function getData()
    {
        return $this->data;
    }
    public function getHeader()
    {
        return $this->header;
    }
    public function view(): View
    {
        return view('common.organization-exports', [
            'exportData' => $this->getData(),
            'exportHeader' => $this->getHeader()
        ]);
    }
}
