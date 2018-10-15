<?php

class RecordCsv
{
    private $path = 'importAll.csv';
    private $pathUpdate = 'updateImages.csv';
    private $pathUpdateProperty = 'updatepropertyPrice.csv';
    private $pathUpdatePropertybizha = 'updatepropertybizha.csv';
    private $pathusers = 'users.csv';
    private $pathSize = 'updatesizeshoes.csv';
    const HEAD = ['IE_XML_ID', 'IE_NAME', 'IE_ACTIVE', 'IE_PREVIEW_TEXT', 'IE_PREVIEW_PICTURE', 'IE_DETAIL_TEXT','IE_DETAIL_PICTURE', 'IP_PROP15', 'IP_PROP16', 'IE_CODE', 'IC_GROUP0', 'IC_GROUP1', 'IC_GROUP2', 'CP_QUANTITY', 'CV_PRICE_1', 'CV_CURRENCY_1'];
    const HEADERUSERS = ['LOGIN','PASSWORD','ACTIVE','NAME','LAST_NAME','EMAIL'];
    const HEADUPD = ['IE_XML_ID','IE_NAME','IE_ACTIVE','IP_PROP19','CP_QUANTITY','CV_PRICE_1','CV_CURRENCY_1'];
    const HEADPROP = ['IE_XML_ID','IE_ACTIVE','IP_PROP20','CP_QUANTITY','CV_PRICE_1','CV_CURRENCY_1'];
    const HEADPROPBIZH = ['IE_XML_ID','IE_ACTIVE','IP_PROP21','IP_PROP22','IP_PROP23','IP_PROP24','CP_QUANTITY','CV_PRICE_1','CV_CURRENCY_1'];
    const HEADPROPPRICE = ['IE_XML_ID','IE_ACTIVE','CP_QUANTITY','CV_PRICE_1','CV_CURRENCY_1'];
    const HEADSIZE = ['IE_XML_ID','IP_PROP_19'];

    public function record($category)
    {
        $i = 0;
        $fp = fopen($this->path, 'w');
        fputcsv($fp, self::HEAD, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }

    public function updateimages($category)
    {
        $i = 0;
        $fp = fopen($this->pathUpdate, 'w');
        fputcsv($fp, self::HEADUPD, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }

    public function recordPropertyBizhu($category)
    {
        $i = 0;
        $fp = fopen($this->pathUpdatePropertybizha, 'w');
        fputcsv($fp, self::HEADPROPBIZH, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }

    public function recordProperty($category)
    {
        $i = 0;
        $fp = fopen($this->pathUpdateProperty, 'w');
        fputcsv($fp, self::HEADPROP, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }

    public function recordCategoty($category, $name)
    {
        $i = 0;
        $path = '.\import_' . $name . '.csv';
        $fp = fopen($path, 'w');
        fputcsv($fp, self::HEAD, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }
    public function recordUsers($users)
    {
        $i = 0;
        $fp = fopen($this->pathusers, 'w');
        fputcsv($fp, self::HEADERUSERS, ';');
        foreach ($users as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }

    public function recordSize($category){
        $i = 0;
        $fp = fopen($this->pathSize, 'w');
        fputcsv($fp, self::HEADSIZE, ';');
        foreach ($category as $row) {
            if (fputcsv($fp, $row, ';')) {
                $i++;
            } else {
                continue;
            }
        }
        fclose($fp);
        echo "Added " . $i . " lines";
    }
}

