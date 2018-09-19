<?php

class RecordCsv
{
    private $path = '/import3.csv';
    const HEAD = ['IE_XML_ID','IE_NAME','IE_ACTIVE','IE_PREVIEW_TEXT','IE_PREVIEW_PICTURE','IE_DETAIL_TEXT','IP_PROP15','IP_PROP16','IE_CODE','IC_GROUP0','IC_GROUP1','IC_GROUP2','CP_QUANTITY','CV_PRICE_1','CV_CURRENCY_1'];

    public function record($category){
        $i = 0;
        $fp = fopen($this->path, 'w');
        fputcsv($fp,self::HEAD,';');
        foreach ($category as $row) {
            if(fputcsv($fp, $row,';')){ $i++;   }  else {   continue;   }
        }
        fclose($fp);
        echo "Added ".$i." lines";
    }
}

