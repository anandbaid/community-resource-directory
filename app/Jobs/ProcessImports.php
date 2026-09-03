<?php

namespace App\Jobs;

use App\Http\Controllers\CommonFunction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $filePath;
    protected $offset;
    protected $batchSize;
    protected $batchNo;
    protected $totalBatches;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $offset,  $batchSize, $batchNo, $totalBatches)
    {
        $this->filePath = $filePath;
        $this->offset = $offset;
        $this->batchSize = $batchSize;
        $this->batchNo = $batchNo;
        $this->totalBatches = $totalBatches;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load the data from the XLSX file
        $csvData = Excel::toArray([], $this->filePath)[0];  // Load first sheet
        $trimmedCsvData = array_map('trim', $csvData[0]);
        $response = CommonFunction::importOrganization(array_slice($csvData, $this->offset, $this->batchSize), $trimmedCsvData, $this->offset);
        $status = 'running';
        if ($this->batchNo == $this->totalBatches) {
            $status = 'completed';
            unlink($this->filePath);
        }
        $responseData = [
            'status' => $status,
            'total' => $response['total'],
            'imported' => $response['imported'],
            'error' => $response['error'],
        ];

        $importResponse = storage_path('app/public/import/lastimported.json');
        if (file_exists($importResponse)) {
            $oldresponse = json_decode(file_get_contents($importResponse), true) ?? [];
            if (count($oldresponse) > 0) {
                $responseData = [
                    'status' => $status,
                    'total' => $response['total'] + $oldresponse['total'],
                    'imported' => $response['imported'] + $oldresponse['imported'],
                    'error' => array_replace_recursive($oldresponse['error'], $response['error']),
                ];
            }
        }
        file_put_contents($importResponse, json_encode($responseData));
        Log::info('Processed batch ' . $this->batchNo . ' of ' . $this->totalBatches);
    }
}
