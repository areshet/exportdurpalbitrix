<?php

class DbTCsv
{
    private $host = '192.168.0.106';
    private $user = 'root';
    private $password = '';
    private $dbname = 'lady_charm_old';

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
        return $images[0];
    }

    /**
     * @param $id category
     * @return mixed
     */
    public static function getNameCategory($id)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $query = "SELECT name FROM taxonomy_term_data WHERE tid =".$id;
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
       // $ids = array_unique($ids);
        /* foreach ($ids as $id){
                    $mass[$id] = self::getNameCategory($id);
                }*/
        return $ids;
    }


    public static function getAllProducts()
    {
        $arr = [];
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $query = "SELECT vid FROM uc_products";
        $result = $db->query($query);
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['vid'];
        }
        foreach ($ids as $id) {
            $catid = self::category();
            if(empty($catid[$id])) continue;
            $arr[$id] = ['IE_XML_ID' => $id];
            $arr[$id] += ['IE_NAME' => self::getName($id)];
            $arr[$id] += ['IE_ACTIVE' => 'Y'];
            $arr[$id] += ['IE_PREVIEW_TEXT' => self::getDescription($id)];
            $arr[$id] += ['IE_PREVIEW_PICTURE' => self::getImage($id)];
            $arr[$id] += ['IE_DETAIL_TEXT' => ' '];
            $arr[$id] += ['IP_PROP15' => self::getNameCategory($catid[$id]) . $id];
            $arr[$id] += ['IP_PROP16' => self::getBrand($id)];
            $arr[$id] += ['IE_CODE' => self::getName($id) . $id];
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
            355 => ['�����', '������'],
            357 => ['�����', '��������'],
            358 => ['�����', '�������'],
            359 => ['�����', '���������'],
            360 => ['�����', '������� ����'],
            361 => ['�����', '�����������'],
            362 => ['�����', '����������'],
            363 => ['�����', '���������'],
            364 => ['�����', '��������'],
            //  ����� �����-����
            339 => ['�����', '���������'],
            366 => ['�����', '�����'],
            367 => ['�����', '������'],
            368 => ['�����', '��������'],
            369 => ['�����', '����'],
            370 => ['�����', '���������'],
            371 => ['�����', '������'],
            372 => ['�����', '���������'],
            373 => ['�����', '����������'],
            //  ������� ������
            402 => ['������� ������', '���������'],
            403 => ['������� ������', '����� � �������'],
            404 => ['������� ������', '��������, �������, ��������'],
            405 => ['������� ������', '������� �����'],
            406 => ['������� ������', '��������'],
            407 => ['������� ������', '����'],
            408 => ['������� ������', '������ ��������� �����'],
            409 => ['������� ������', '������� �������������� ����'],
            410 => ['������� ������', '�����'],
            // ��������� ��� �����
            380 => ['���������', '��������� ��� �����'],
            382 => ['���������', '��������� ��� �����'],
            383 => ['���������', '��������� ��� �����'],
            426 => ['���������', '��������� ��� �����'],
            //  ����� ��������
            // 205 =>  ['����� ��������', ''],
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
            // ������
            300 => ['��������, ��������, �������', ''],
            418 => ['�����, �����, �����', ''],
            /**
             *
             * [259] => ���������
             * [381] => �������
             * [382] => �������
             * [383] => �������
             *
             */
        ];
        return $path[$id];
    }

    public static function branToMachine()
    {
        return;
    }

    public static function getProductsCategoty($catid)
    {
        $class = new DbTCsv();
        $db = $class->dbconnect();
        $ids = [];
        $arr = [];
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
            $arr[$id] += ['IP_PROP15' => self::getNameCategory($catid) . $id];
            $arr[$id] += ['IP_PROP16' => self::getBrand($id)];
            $arr[$id] += ['IE_CODE' => self::getName($id) . $id];
            $arr[$id] += ['IC_GROUP0' => self::categoryPath($catid)[0]];
            $arr[$id] += ['IC_GROUP1' => self::categoryPath($catid)[1]];
            $arr[$id] += ['IC_GROUP2' => ' '];
            $arr[$id] += ['CP_QUANTITY' => 0];
            $arr[$id] += ['CV_PRICE_1' => self::getPrice($id)];
            $arr[$id] += ['CV_CURRENCY_1' => 'RUB'];
        }
        return $arr;
    }
}

require_once 'RecordCsv.php';
$record = new RecordCsv();
$record->record(DbTCsv::getAllProducts());
