<?php

class OperationDb
{
    // $engin(よくわからんけどmysql以外使うかつpdoで食えるものがあるときよう）
    private $engine = 'mysql';

    // ホスト名(default:localhost)
    private $host = 'localhost';
    
    // 接続先データベース名
    private $databese = '';

    // user名
    private $user = '';

    // password
    private $pass = '';

    // 接続テーブル名
    private $table='';

    // pdoObject
    private $dbh = null;

    // 条件式とか入れとくやつ
    private $condition = '';

    // コンストラクタ
    public function __construct( $database, $user, $pass, $host = '' ){
        // host名が入ってたら入れ直す（まぁ、多分使わん）
        if ( '' != $host ){ $this->host = $host; }
        $this->database = $database;
        $this->user     = $user;
        $this->pass     = $pass;

        try{
            $dsn = $this->engine . ':dbname=' . $this->database . ";host=" . $this->host; 
            $this->dbh = new PDO($dsn, $this->user, $this->pass); 
        }catch (PDOException $e){
            print('Error:'.$e->getMessage());
            die();
        }
    }

    /* 実行 */
    public function execute( $type, $data = array() )
    {
        $sqlType = 'execute' . ucfirst($type);
        $result  = $this->$sqlType($data);
        return $result;
    }   

    // table名設定
    public function setTable( $name ){
        $this->table = $name;
    }

    /* 条件式 */
    public function setCondition( $type, $key, $comparisonOperator = '=' , $value )
    {
        $setMethod = 'set' . ucfirst($type);
        $this->$setMethod($key, $value, $comparisonOperator);
    }

    /* 条件式クリア */
    public function clearCondition()
    {
        $this->condition = '';
    }

    // selectの取得フィールド作成
    public function makeKey( $fieldList )
    {
        // keyが空の場合*を返却
        if ( empty($fieldList) ){ return '*'; }
        $targetKey = '';

        // quote
        $fieldList = $this->makeQuote($fieldList);
        // 引数にあわせて取得キー作成
        if ( is_array($fieldList) ){
            // 引数が配列の場合、カンマ区切り
            $targetKey = implode(',', $fieldList);
        }else{
            // 引数がstringの場合、そのまんま
            $targetKey = $fieldList;
        }
        return $targetKey;
    }

    // key,value設定(「,」で区切る)
    public function makeKeyValue( $data )
    {
        $targetKey = '';
        $targetValue = '';
        if ( empty($data) ){ return array($targetKey, $targetValue); }
        foreach( $data as $key => $value ){
            $tmpKey[] = $key;
            $tmpValue[] = $this->makeQuote($value);
        }
        $targetKey = implode(',', $tmpKey);
        $targetValues = implode(',', $tmpValue);
        return array($targetKey, $targetValues);
    }

    // quote関数
    public function makeQuote( $data )
    {
        if ( is_array($data) ) {
            foreach( $data as $key => $val ){
                $rtnData[ $key ] = $this->dbh->quote($val);
            }
        } else {
            $rtnData = $this->dbh->quote($data);
        }

        return $rtnData;
    }

    /* 比較句作成 */
    public function comparePhrase( $key, $value, $comparisonOperator )
    {
        // 対象のkeyの数とvalueの数があってない場合はreturn
        if ( count($key) != count($value) ){ return 0; }
      
        // 比較句内作成
        // todo 比較演算子の種類増やす（引数判断）
        $compareArray = array();
        if ( is_array($key) ){
            // 引数が配列の場合
            for( $i=0; $i<count( $key ); $i++ ){
                $compareArray[] = $key[$i] . $comparisonOperator . $this->makeQuote($value[$i]);
            }
        }else{
            $compareArray[] = $key . $comparisonOperator . $this->makeQuote($value);
        }
        return implode(',', $compareArray);
    }

    /* Select */
    public function executeSelect( $fieldList = array() )
    {
        $sql = '';
        $targetKey = $this->makeKey($fieldList);
        $sql = 'SELECT ' . $targetKey . ' from ' . $this->table . $this->condition;
        $stmt = $this->dbh->query($sql);
        $data = $this->fetch($stmt);
        return $data;
    }

    /* Insert分作成 */
    public function executeInsert( $data = array() )
    {
        // dataが空の場合はreturn
        if ( empty($data) ){ return false; }
        list($targetKey, $targetValue) = $this->makeKeyValue($data);
        $sql = 'INSERT INTO ' . $this->table . ' (' . $targetKey . ') values (' . $targetValue . ')';
        $stmt = $this->dbh->prepare($sql);
        $bool = $stmt->execute();
        return $bool;
    }

    /* Update分作成 */
    public function executeUpdate( $data = array() )
    {
        // dataが空の場合はreturn
        if ( empty($data) ){ return false; }
        foreach( $data as $key => $value ){
            $compareArray[] = $key . " = '" . $value . "'";
        }
        $updateColumns = implode(',', $compareArray);
        $sql = 'UPDATE ' . $this->table . ' SET ' . $updateColumns . $this->condition;
        $stmt = $this->dbh->prepare($sql);
        $bool = $stmt->execute();

        return $bool;
    }

    /* Delete分作成 */
    public function executeDelete()
    {
        $sql = 'DELETE FROM ' . $this->table . $this->condition;
        $count = $this->dbh->exec($sql);
        return $count;
    }

    /* where $key=$valueを作成 */
    public function setWhere( $key, $value, $comparisonOperator )
    {
        // keyとvalueの数が同じ場合に設定
        if ( count($key) == count($value) ){
            $wherePhrase = $this->comparePhrase($key, $value, $comparisonOperator);
            $this->condition = ' where ' . $wherePhrase;
        }
    }

    /* データ取得 */
    public function fetch( $stmt )
    {
         $rtnData = array();
         while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
             $rtnData[] = $data;
         }
         return $rtnData;
    }

}
