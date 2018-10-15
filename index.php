<?php

class DbTCsv
{
    private $host = '127.0.0.1';
    private $user = 'root';
    private $password = '';
    private $dbname = 'ladydb';

    private function dbconnect()
    {
        $db = new mysqli($this->host, $this->user, $this->password, $this->dbname) or die ($db->connect_error);
        $db->set_charset('cp1251');
        return $db;
    }

    public static function getName($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT title from node WHERE vid = $id";
        $result = $db->query($query);
        $result = $result->fetch_assoc();
        return $result['title'];
    }

    public static function getBrand($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT name from taxonomy_term_data WHERE tid = (SELECT field_brand_tid FROM field_revision_field_brand WHERE entity_id='$id');";
        $result = $db->query($query);
        $result = $result->fetch_assoc();
        return $result['name'];
    }

    public static function getMaterial($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT name from taxonomy_term_data WHERE tid = (SELECT field_fabricbag_tid FROM field_revision_field_fabricbag WHERE entity_id='$id');";
        $result = $db->query($query);
        $result = $result->fetch_assoc();
        return $result['name'];
    }

    public static function getImage($id)
    {
        $path = '/upload/images/';
        $mass = [];
        $images = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT fid from file_usage WHERE id = $id";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $mass[] = $row['fid'];
        }

        foreach ($mass as $value) {
            $query = "SELECT filename from file_managed where fid = $value";
            $result = $db->query($query);
            $result = $result->fetch_assoc();
            $images[] = $path . $result['filename'];
        }
        return $images;
    }

    /**
     * @param $id category
     * @return mixed
     */
    public static function getNameCategory($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT name FROM taxonomy_term_data WHERE tid =" . $id;
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        return $row['name'];
    }

    public static function getSection($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $mass = [];
        $counter = 0;
        $category = "";
        $query = "SELECT taxonomy_catalog_tid,delta from field_revision_taxonomy_catalog WHERE entity_id = $id";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $mass[$counter] = ['delta' => $row['delta']];
            $mass[$counter] += ['tid' => $row['taxonomy_catalog_tid']];
            $counter++;
        }
        foreach ($mass as $value) {
            $parents = [];
            $query = "SELECT parent from taxonomy_term_hierarchy WHERE tid=" . $value['tid'];
            $result = $db->query($query);
            $row = $result->fetch_assoc();
            if ($row['parent'] != 0) {
                $parents[] = $row['parent'];
                $parents[] = $value['tid'];
                foreach ($parents as $value) {
                    $category .= '/' . self::getNameCategory($value);
                }
            } else {
                $category .= '/' . self::getNameCategory($value['tid']);
            }
        }

        return $category;
    }

    public static function getCategory()
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $section = [];
        $query = "SELECT * from taxonomy_term_hierarchy";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            if ($row['parent'] != 0) {
                $sectionName = self::getNameCategory($row['parent']);
                if (array_key_exists($sectionName, $section)) {
                    $section[$sectionName] += [$row['tid'] => self::getNameCategory($row['tid'])];
                } else {
                    $section[$sectionName] = [$row['parent'] => self::getNameCategory($row['tid'])];
                }
            }
        }
        echo "<pre>";
        print_r($section);
    }

    public static function category()
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $query = "SELECT entity_id,taxonomy_catalog_tid from field_revision_taxonomy_catalog";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            if ($row['taxonomy_catalog_tid'] == 299) continue;
            $ids[$row['entity_id']] = $row['taxonomy_catalog_tid'];
        }
        // $ids = array_unique( $ids);
        return $ids;
    }


    public static function getAllProducts()
    {
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $chars = ')(+"';
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $key => $id) {
            $article = 10000 + $key;
            $catid = self::category();
            $exception = [206, 205, 339, 38, 97];
            if (empty($catid[$id])) continue;
            if (in_array($catid[$id], $exception)) continue;
            $arr[$id] = ['IE_XML_ID' => $id];
            $arr[$id] += ['IE_NAME' => self::getName($id)];
            $arr[$id] += ['IE_ACTIVE' => 'Y'];
            $arr[$id] += ['IE_PREVIEW_TEXT' => self::getDescription($id)];
            $arr[$id] += ['IE_PREVIEW_PICTURE' => self::getImage($id)];
            $arr[$id] += ['IE_DETAIL_TEXT' => ' '];
            $arr[$id] += ['IE_DETAIL_PICTURE' => self::getImage($id)];
            $arr[$id] += ['IP_PROP15' => $article];
            $arr[$id] += ['IP_PROP16' => self::getBrand($id)];
            $arr[$id] += ['IE_CODE' => strtolower(self::transliterate(preg_replace('/[' . $chars . ']/', '', self::getName($id)) . '-' . $id))];
            $arr[$id] += ['IC_GROUP0' => self::categoryPath($catid[$id])[0]];
            $arr[$id] += ['IC_GROUP1' => self::categoryPath($catid[$id])[1]];
            $arr[$id] += ['IC_GROUP2' => ' '];
            $arr[$id] += ['CP_QUANTITY' => 0];
            $arr[$id] += ['CV_PRICE_1' => self::getPrice($id)];
            $arr[$id] += ['CV_CURRENCY_1' => 'RUB'];
        }
        return $arr;
    }

    public static function getPrice($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT sell_price FROM uc_products WHERE vid = " . $id;
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        return $row['sell_price'];
    }

    public static function getDescription($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT body_value from field_data_body WHERE entity_id = " . $id;
        $result = $db->query($query);
        $row = $result->fetch_assoc();
        return $row['body_value'];
    }


    public static function categoryPath($id)
    {
        $path = [
            // ��������� ���������
            37 => ['���������', '�����'],
            44 => ['���������', '������'],
            45 => ['���������', '������'],
            46 => ['���������', '����� � ��������'],
            47 => ['���������', '��������'],
            108 => ['���������', '���������'],
            // ������������ ���������
            100 => ['���������', '������'],
            99 => ['���������', '����� � ��������'],
            101 => ['���������', '������'],
            103 => ['���������', '������ ������, ��������� ���������'],
            98 => ['���������', '��������'],
            116 => ['���������', '���������'],
            102 => ['���������', '�����'],
            //  ����� �����-����
            356 => ['�����', '������'],
            357 => ['�����', '��������'],
            358 => ['�����', '�������'],
            359 => ['�����', '���������'],
            360 => ['�����', '������� ����'],
            361 => ['�����', '�����������'],
            362 => ['�����', '����������'],
            363 => ['�����', '���������'],
            364 => ['�����', '��������'],
            //  ����� �����-����
            365 => ['�����', '���������'],
            366 => ['�����', '�����'],
            367 => ['�����', '������'],
            368 => ['�����', '��������'],
            369 => ['�����', '����'],
            370 => ['�����', '���������'],
            371 => ['�����', '������'],
            372 => ['�����', '���������'],
            373 => ['�����', '����������'],
            //  ������� ������
            259 => ['������� ������', '���������'],
            403 => ['������� ������', '����� � �������'],
            404 => ['������� ������', '��������, �������, ��������'],
            405 => ['������� ������', '������� �����'],
            406 => ['������� ������', '��������'],
            407 => ['������� ������', '������� ����'],
            408 => ['������� ������', '������ ��������� �����'],
            402 => ['������� ������', '������ ��������� �����'],
            409 => ['������� ������', '������� �������������� ����'],
            410 => ['������� ������', '�����'],
            // ��������� ��� �����
            380 => ['���������', '��������� ��� �����'],
            382 => ['���������', '��������� ��� �����'],
            383 => ['���������', '��������� ��� �����'],
            426 => ['���������', '��������� ��� �����'],
            381 => ['���������', '��������� ��� �����'],
            //  ����� ��������
            // 206 =>  ['����� ��������', ''],
            250 => ['����', ''],
            289 => ['������ ��������� �����', ''],
            //  ����� � �������
            295 => ['����� � �������', '�����'],
            379 => ['����� � �������', '����� ����'],
            427 => ['����� � �������', '�������'],
            //  ����������
            301 => ['����������', '������'],
            307 => ['����������', '����'],
            338 => ['����������', '�����'],
            401 => ['����������', '�������� � �������'],
            // ������
            300 => ['��������, ��������, �������', ''],
            418 => ['����������', '�������� �����'],

            //����������
            /**
             * ���������� �� 206,205,339,97,38
             */
        ];

        return $path[$id];
    }

    public static function branToMachine()
    {
        return;
    }

    public static function transliterate($st)
    {
        $st = strtr($st,
            "����������������������������������������������",
            "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE "
        );
        $st = strtr($st, array(
            '�' => "yo", '�' => "h", '�' => "ts", '�' => "ch", '�' => "sh",
            '�' => "shch", '�' => '', '�' => '', '�' => "yu", '�' => "ya",
            '�' => "Yo", '�' => "H", '�' => "Ts", '�' => "Ch", '�' => "Sh",
            '�' => "Shch", '�' => '', '�' => '', '�' => "Yu", '�' => "Ya",
            ' ' => "-",
        ));
        return $st;
    }

    public static function getArtikle($catid, $id)
    {
        $mansCat = [410, 409, 402, 408, 407, 406, 405, 404, 403, 259];
        $name = self::getName($id);
        if (in_array($catid, $mansCat)) {
            return $name . ' �0' . $id;
        } else {
            return $name . ' �0' . $id;
        }
    }

    public static function getProductsCategoty($catid)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $arr = [];
        $chars = ')(+"';
        $query = "SELECT entity_id from field_revision_taxonomy_catalog WHERE taxonomy_catalog_tid = " . $catid;
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['entity_id'];
        }
        foreach ($ids as $id) {
            $arr[$id] = ['IE_XML_ID' => $id];
            $arr[$id] += ['IE_NAME' => self::getName($id)];
            $arr[$id] += ['IE_ACTIVE' => 'Y'];
            $arr[$id] += ['IE_PREVIEW_TEXT' => self::getDescription($id)];
            $arr[$id] += ['IE_PREVIEW_PICTURE' => self::getImage($id)];
            $arr[$id] += ['IE_DETAIL_TEXT' => ' '];
            $arr[$id] += ['IE_DETAIL_PICTURE' => self::getImage($id)];
            $arr[$id] += ['IP_PROP15' => self::getArtikle($catid, $id)];
            $arr[$id] += ['IP_PROP16' => self::getBrand($id)];
            $arr[$id] += ['IE_CODE' => strtolower(self::transliterate(preg_replace('/[' . $chars . ']/', '', self::getName($id)) . '-' . $id))];
            $arr[$id] += ['IC_GROUP0' => self::categoryPath($catid)[0]];
            $arr[$id] += ['IC_GROUP1' => self::categoryPath($catid)[1]];
            $arr[$id] += ['IC_GROUP2' => ' '];
            $arr[$id] += ['CP_QUANTITY' => 0];
            $arr[$id] += ['CV_PRICE_1' => self::getPrice($id)];
            $arr[$id] += ['CV_CURRENCY_1' => 'RUB'];
        }
        return $arr;
    }

    /**
     * MORE IMAGES
     */

    public static function getCategotyImages()
    {
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $count = 0;
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $key => $id) {
            $catid = self::category();
            $exception = [206,205,339,38,97];
            if(empty($catid[$id])) continue;
            if(in_array($catid[$id], $exception)) continue;


            $images =  self::getImage($id);
            $countImages = count($images);
            for ($i = 1; $i < $countImages; $i++){
                $arr[$count] = ['IE_XML_ID' => $id];
                $arr[$count] += ['IE_NAME' => self::getName($id)];
                $arr[$count] += ['IE_ACTIVE' => 'Y'];
                $arr[$count] += ['IP_PROP19' => $images[$i]];
                $arr[$count] += ['CP_QUANTITY' => 1];
                $arr[$count] += ['CV_PRICE_1' => self::getPrice($id)];
                $arr[$count] += ['CV_CURRENCY_1' => 'RUB'];
                $count++;
            }
        }
        return $arr ;

    }


    public static function updateProperty()
    {
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $count = 0;
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $key => $id) {
            $catid = self::category();
            $exception = [206,205,339,38,97];
            if(empty($catid[$id])) continue;
            if(in_array($catid[$id], $exception)) continue;
            if (empty(self::getMaterial($id))) continue;
                $arr[$count] =  ['IE_XML_ID' => $id];
                $arr[$count] += ['IE_ACTIVE' => 'Y'];
                $arr[$count] += ['IP_PROP20' => self::getMaterial($id)];
                $arr[$count] += ['CP_QUANTITY' => 1001];
                $arr[$count] += ['CV_PRICE_1' => self::getPrice($id)];
                $arr[$count] += ['CV_CURRENCY_1' => 'RUB'];
                $count++;
            }
        return $arr ;
    }


    /**
     * get users ***
     */

    public static function getUsers()
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $users = [];
        $arr = [];
        $i = 0;
        $query = "SELECT * from users";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $users[$row['uid']]['LOGIN'] = $row['name'];
            $users[$row['uid']]['MAIL'] = $row['mail'];
        }

        foreach ($users as $user) {
            if (empty($user['LOGIN']) || $user['LOGIN'] == 'admin') continue;
            $arr[$i] = ['LOGIN' => $user['LOGIN']];
            $arr[$i] += ['PASSWORD' => 'ladycharm'];
            $arr[$i] += ['ACTIVE' => 'Y'];
            $arr[$i] += ['NAME' => $user['LOGIN']];
            $arr[$i] += ['LAST_NAME' => '�� ���������'];
            $arr[$i] += ['EMAIL' => $user['MAIL']];
            $i++;
        }
        return $arr;

    }
 /*
  * bizhuteria
  */
    public static function getCover($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT field_cover_tid FROM field_data_field_cover WHERE entity_id='$id'";
        $result = $db->query($query);
        while($row = $result->fetch_assoc()){
            $tids[] = $row['field_cover_tid'];
        }
        foreach ($tids as $tid){
            $query = "SELECT name from taxonomy_term_data WHERE tid = $tid";
            $result = $db->query($query);
            while($row = $result->fetch_assoc()){
                $arr[] = $row['name'];
            }
        }
        if (count($arr) > 1){
            $result = implode(', ',$arr);
        }else{
            $result = $arr[0];
        }
        return $result;
    }

    public static function getDesing($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT field_design_tid FROM field_data_field_design WHERE entity_id='$id'";
        $result = $db->query($query);
        while($row = $result->fetch_assoc()){
            $tids[] = $row['field_design_tid'];
        }
        foreach ($tids as $tid){
            $query = "SELECT name from taxonomy_term_data WHERE tid = $tid";
            $result = $db->query($query);
            while($row = $result->fetch_assoc()){
                $arr[] = $row['name'];
            }
        }
        if (count($arr) > 1){
            $result = implode(', ',$arr);
        }else{
            $result = $arr[0];
        }
        return $result;
    }


    public static function getStone($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT field_stone_tid FROM field_data_field_stone WHERE entity_id='$id'";
        $result = $db->query($query);
        while($row = $result->fetch_assoc()){
            $tids[] = $row['field_stone_tid'];
        }

        foreach ($tids as $tid){
            $query = "SELECT name from taxonomy_term_data WHERE tid = $tid";
            $result = $db->query($query);
            while($row = $result->fetch_assoc()){
                $arr[] = $row['name'];
            }
        }

        if (count($arr) > 1){
            $result = implode(', ',$arr);
        }else{
            $result = $arr[0];
        }
        return $result;
    }

    public static function getColorStone($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT field_insertion_stone_tid FROM field_data_field_insertion_stone WHERE entity_id='$id'";
        $result = $db->query($query);
        while($row = $result->fetch_assoc()){
            $tids[] = $row['field_insertion_stone_tid'];
        }
        foreach ($tids as $tid){
            $query = "SELECT name from taxonomy_term_data WHERE tid = $tid";
            $result = $db->query($query);
            while($row = $result->fetch_assoc()){
                $arr[] = $row['name'];
            }
        }
        if (count($arr) > 1){
            $result = implode(', ',$arr);
        }else{
            $result = $arr[0];
        }
        return $result;
    }

    public static function updateJewellery(){
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $count = 0;
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $key => $id) {
            $catid = self::category();
            $exception = [206,205,339,38,97];
            if(empty($catid[$id])) continue;
            if(in_array($catid[$id], $exception)) continue;
            if (empty(self::getCover($id)) && empty(self::getDesing($id)) && empty(self::getStone($id)) && empty(self::getColorStone($id))  ) continue;

            $arrCover = count(self::getCover($id));
            $arrDesign = count(self::getDesing($id));
            $arrStone = count (self::getStone($id));
            $arrColorStone = count(self::getColorStone());



            $arr[$count] =  ['IE_XML_ID' => $id];
            $arr[$count] += ['IE_ACTIVE' => 'Y'];
            $arr[$count] += ['IP_PROP21' => self::getCover($id)]; //cover
            $arr[$count] += ['IP_PROP22' => self::getDesing($id)];
            $arr[$count] += ['IP_PROP23' => self::getStone($id)];
            $arr[$count] += ['IP_PROP24' => self::getColorStone($id)];
            $arr[$count] += ['CP_QUANTITY' => 1];
            $arr[$count] += ['CV_PRICE_1' => self::getPrice($id)];
            $arr[$count] += ['CV_CURRENCY_1' => 'RUB'];
            $count++;
        }
       return $arr ;
    }
    public static function property(){
        $ids = [];
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT field_gear_tid from field_data_field_gear";
        $result = $db->query($query);
        while( $row = $result->fetch_assoc()){
            if(!in_array($row['field_gear_tid'],$ids)) {
                $ids[] = $row['field_gear_tid'];
            }
        }
        foreach ($ids as $id){
            $query = "SELECT name from taxonomy_term_data WHERE tid = $id";
            $result = $db->query($query);
            while($row = $result->fetch_assoc()){
                $arr[] = $row['name'];
            }
        }
        return $arr;
    }

    public function updatePrice(){
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $count = 0;
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $key => $id) {
            $arr[$count] =  ['IE_XML_ID' => $id];
            $arr[$count] += ['IE_ACTIVE' => 'Y'];
            $arr[$count] += ['CP_QUANTITY' => 1000];
            $arr[$count] += ['CV_PRICE_1' => self::getPrice($id)];
            $arr[$count] += ['CV_CURRENCY_1' => 'RUB'];
            $count++;
        }
        return $arr ;
    }

    public static function updatesize()
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $arr = [];
        $shoesCat = [356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373];
        foreach ($shoesCat as $catid) {
            $query = "SELECT entity_id from field_revision_taxonomy_catalog WHERE taxonomy_catalog_tid = " . $catid;
            $result = $db->query($query);
            while ($row = $result->fetch_assoc()) {
                $ids[] = $row['entity_id'];
            }
        }
        $count =0;
        $sizes = [35,36,37,38,39,40,41];
        foreach ($ids as $id) {
            foreach ($sizes as $size) {
                $arr[$count] = ['IE_XML_ID' => $id];
                $arr[$count] += ['PROP_20' => $size];
                $count++;
            }
        }
        return $arr;
    }

    public static function getGear($id){
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT name from taxonomy_term_data WHERE tid = (SELECT field_gear_tid FROM field_data_field_gear WHERE entity_id='$id');";
        $result = $db->query($query);
        $result = $result->fetch_assoc();
        return $result['name'];
    }
    public static function updateGear(){
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $count = 0;
        $query = "SELECT entity_id FROM field_data_field_gear";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['entity_id'];
        }
        foreach ($ids as $key => $id) {
            $arr[$count] =  ['IE_XML_ID' => $id];
            $arr[$count] += ['IE_PROP_25' => self::getGear($id)];
            $count++;
        }
        return $arr ;
    }
}

require_once 'RecordCsv.php';
$record = new RecordCsv();

$record->recordSize(DbTCsv::updateGear());


//$record->recordUsers($users);
//$record->record(DbTCsv::getAllProducts());
/*$id = 427;
$record->recordCategoty(DbTCsv::getProductsCategoty($id),$id);*/