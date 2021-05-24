<?php

namespace AHT\TierPrice\Model;

class CsvImportHandler 
{
    /**
    * CSV Processor
    *
    * @var \Magento\Framework\File\Csv
    */
    protected $csvProcessor;

    public function __construct(
        \Magento\Framework\File\Csv $csv
    )
    {
         $this->csv = $csv;
    }

    public function getDataCsv($filename) {
        $arr = [];
        if (file_exists($filename)) {
            $row = 0;
            if (($handle = fopen($filename, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $datanew[] = $data;
                    $arr[$data[0]][] = [
                        'qty' => $data[1],
                        'price' => $data[2]
                    ];
                    $row++;
                }
            fclose($handle);
            }
            
        }
        // echo "<pre>";   
        // print_r($arr);
        // die;
        return $arr;
    }
}