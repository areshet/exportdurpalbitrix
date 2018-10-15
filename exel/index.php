<?php
require_once 'PHPExcel.php';
require_once 'PHPExcel/Writer/Excel5.php';

class PriceList{
    private $host = 'localhost';
    private $user = 'root';
    private $db = 'admin_ptt';
    private $pas = '';


    public function main(){
    }

    private function dbconnect(){
        $db = new mysqli($this->host, $this->user, $this->pas, $this->db) or die ($db->connect_error);
        $db->set_charset('utf8');
        return $db;
    }

    public function getListProduct(){
        $class = new PriceList();
        $db = $class->dbconnect();
        $arr = [];
        $query = "SELECT t1.product_id,t2.name,t1.price FROM oc_product AS t1 LEFT JOIN oc_product_description AS t2 ON (t2.product_id=t1.product_id)";
        $result = $db->query($query);
        $i = 1;
        while ($row = $result->fetch_assoc()){
            $arr[$row['product_id']] =  ['ID' => $i];
            $arr[$row['product_id']] += ['NAME' => $row['name']];
            $arr[$row['product_id']] += ['PRICE' => $row['price']];
            $i++;
        }
        return $arr;
    }

    public function getCategoryList($catid){
        $class = new PriceList();
        $db = $class->dbconnect();
        $arr = [];
        $query = "SELECT t1.product_id,t2.name,t3.price,t4.name as catname from oc_product_to_category AS t1 inner join oc_product_description AS t2 ON (t2.product_id=t1.product_id) inner join oc_product AS t3 ON(t3.product_id=t1.product_id) inner join oc_category_description AS t4 ON (t4.category_id = t1.category_id ) where t1.category_id = $catid";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()){
            $arr[$row['product_id']]  = ['ID' => $row['product_id']];
            $arr[$row['product_id']] += ['NAME' => $row['name']];
            $arr[$row['product_id']] += ['PRICE' => $row['price']];
            $arr[$row['product_id']] += ['SECTION' => $row['catname']];
        }

        return $arr;
    }

    /**
     * record excel file
     */

    public function recordAll($products){
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Прайс-лист');


        /*         * Логотип

                  $imagePath = 'logo.png';
                if (file_exists($imagePath)) {
                    $logo = new PHPExcel_Worksheet_Drawing();
                    $logo->setPath($imagePath);
                    $logo->setCoordinates("B2");
                    $logo->setOffsetX(0);
                    $logo->setOffsetY(0);
                    $sheet->getRowDimension(2)->setRowHeight(70);
                    $logo->setWorksheet($sheet);
                }*/

        //Заголовок
        $sheet->setCellValue('B1','ООО "Пермторгтехника"');
        $sheet->getStyle('B1')->getFont()->setSize(30);
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('B1:E1');

        $sheet->setCellValue('B3','e-mail: pttirina@perm.ru, ptttatyana@perm.ru http://www.ptt.perm.ru/')->getStyle('C3')->getFont()->setSize(10);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B5','Адрес: 614990, Россия, Пермский край, город Пермь');
        $sheet->mergeCells('B5:E5');
        $sheet->setCellValue('B6','улица Лодыгина, дом 5 (литер А, А1), этаж 3, офис 3');
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B7','Телефоны: (342) 242-85-18, 241-50-82, 240-17-50');
        $sheet->mergeCells('B7:E7');

        $stylefont = array(
            'font' => array(
                'name' => 'Arial',
                'italic' => true,
                'size' => 11,
            ),
            'alignment' => array (
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        );
        $sheet->getStyle('B5:C7')->applyFromArray($stylefont);


        $sheet->setCellValue('B9','Прайс-лист');
        $sheet->getStyle('B9')->getFont()->setSize(24);
        $sheet->getStyle('B9')->getFont()->setBold(true);
        $sheet->getStyle('B9')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B9:C9');

        $date = date("d.m.Y H:i:s");
        $sheet->setCellValue('B10','От '.$date);
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B10:C10');


        // Название столбцов
        $sheet->setCellValue('B12','№');
        $sheet->setCellValue('C12','Название');
        $sheet->setCellValue('D12','Цена (руб)');
        // заливка строки с названиями
        $sheet->getStyle('B12:D12')->getFill()->
        setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('B12:D12')->getFill()->
        getStartColor()->applyFromArray(array('rgb' => 'C2FABD'));
        // установка ширины столбцам
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('D')->setWidth(12);

        //Запись таблицы
        $row = 12; // строка с которой начнется запись
        foreach ($products as $key => $product){
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $product['ID']);
            $sheet->setCellValueByColumnAndRow(2, $row, $product['NAME']);
            $sheet->setCellValueByColumnAndRow(3, $row, $product['PRICE']);
        }
        // установка бордюра и выравнивая
        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'fff'),
                ),
            ),
            'alignment' => array (
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
        );
        $sheet->getStyle('B12:D'.$row)->applyFromArray($styleArray);

        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('price.xls');
    }

    public function recordCat($array = null){
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Прайс-лист');


        /*         * Логотип

                  $imagePath = 'logo.png';
                if (file_exists($imagePath)) {
                    $logo = new PHPExcel_Worksheet_Drawing();
                    $logo->setPath($imagePath);
                    $logo->setCoordinates("B2");
                    $logo->setOffsetX(0);
                    $logo->setOffsetY(0);
                    $sheet->getRowDimension(2)->setRowHeight(70);
                    $logo->setWorksheet($sheet);
                }*/

        //Заголовок
        $sheet->setCellValue('B1','ООО "Пермторгтехника"');
        $sheet->getStyle('B1')->getFont()->setSize(30);
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('B1:E1');

        $sheet->setCellValue('B3','e-mail: pttirina@perm.ru, ptttatyana@perm.ru http://www.ptt.perm.ru/')->getStyle('C3')->getFont()->setSize(10);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B5','Адрес: 614990, Россия, Пермский край, город Пермь');
        $sheet->mergeCells('B5:E5');
        $sheet->setCellValue('B6','улица Лодыгина, дом 5 (литер А, А1), этаж 3, офис 3');
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B7','Телефоны: (342) 242-85-18, 241-50-82, 240-17-50');
        $sheet->mergeCells('B7:E7');

        $stylefont = array(
            'font' => array(
                'name' => 'Arial',
                'italic' => true,
                'size' => 11,
            ),
            'alignment' => array (
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        );
        $sheet->getStyle('B5:C7')->applyFromArray($stylefont);


        $sheet->setCellValue('B9','Прайс-лист');
        $sheet->getStyle('B9')->getFont()->setSize(24);
        $sheet->getStyle('B9')->getFont()->setBold(true);
        $sheet->getStyle('B9')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B9:C9');

        $date = date("d.m.Y H:i:s");
        $sheet->setCellValue('B10','От '.$date);
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('B10:C10');

        // Название столбцов
        $sheet->setCellValue('B12','№');
        $sheet->setCellValue('C12','Название');
        $sheet->setCellValue('D12','Цена (руб)');
        // заливка строки с названиями
        $sheet->getStyle('B12:D12')->getFill()->
        setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('B12:D12')->getFill()->
        getStartColor()->applyFromArray(array('rgb' => 'C2FABD'));
        // установка ширины столбцам
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('D')->setWidth(12);

        $class = new PriceList();
        $arrs = [];
        foreach ($array as $key => $catid){
            $arrs[$key] = $class->getCategoryList($catid);
        }
        $row = 12; // строка с которой начнется запись
        $categoryNameLast = '';
        $number = 0;
        foreach ($arrs as $arr){
            foreach ($arr as $value){
                    $row++;$number++;
                    if($value['SECTION'] != $categoryNameLast){
                        $sheet->setCellValueByColumnAndRow(1, $row, $value['SECTION']);
                        $sheet->getStyleByColumnAndRow(1,$row)->getFill()->
                        setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $sheet->getStyleByColumnAndRow(1,$row)->getFill()->
                        getStartColor()->applyFromArray(array('rgb' => '808080'));
                        $sheet->mergeCellsByColumnAndRow(1,$row,3,$row);
                        $row++;
                    }
                    $sheet->setCellValueByColumnAndRow(1, $row, $number);
                    $sheet->setCellValueByColumnAndRow(2, $row, $value['NAME']);
                    $sheet->setCellValueByColumnAndRow(3, $row, $value['PRICE']);
                    $categoryNameLast = $value['SECTION'];
            }
        }

        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'fff'),
                ),
            ),
            'alignment' => array (
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ),
        );

        $sheet->getStyle('B12:D'.$row)->applyFromArray($styleArray);

        //защита от записи
        $xls->getActiveSheet()->getProtection()->setSheet(true);
        // запись в файл
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('priceCategoty.xls');
    }
    public function getCategoty(){
        $class = new PriceList();
        $db = $class->dbconnect();
        $query = "SELECT category_id from oc_category";
        $result = $db->query($query);
        while($row = $result->fetch_assoc()){
            $ids[] = $row['category_id'];
        }
        return $ids;
    }
}

$class =  new PriceList();
$cat = [65,59];
echo "<pre>";

$class->recordCat($class->getCategoty());
$class->recordAll($class->getListProduct());