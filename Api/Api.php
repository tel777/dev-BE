<?php
require_once("../db/OperationDb.php");

Class test
{
    protected $_dao = null;
    
    function _init(){
        //xmlファイルを読み込み、配列へ変換
        $xml = simplexml_load_file('Config.xml');
        $conf = json_decode(json_encode($xml), true);
        $user = $conf['Yuzukosyo']['user'];
        $pass = $conf['Yuzukosyo']['pass'];
        $database = $conf['Yuzukosyo']['database'];
        $table = 'tags';
        $keys = array('tag_id');
       
        $this->_dao = new OperationDb($database,$user,$pass);
        $this->_dao->setTable($table);
    }
    
    function main(){
        $this->_init();
        $contents=$this->_dao->execute($_GET['type']);
        $contents=$this->_createXml( $contents );
    }

    function _createXml( $contents ){
        // インスタンスの生成
        $dom = new DomDocument('1.0', 'UTF-8');
        $results = $dom->appendChild($dom->createElement('Results'));
        
        // code 属性の追加
        //$pref->setAttribute('code', '01');
        // 要素ノードを追加してテキストを入れる
        foreach( $contents as $key => $value ){
            $result = $results->appendChild($dom->createElement('Result'));
            foreach( $value as $keyname => $val ){
                $result->appendChild($dom->createElement($keyname, $val));
            }
        }
        //XML を整形（改行・字下げ）して出力
        $dom->formatOutput = true;
        //保存（上書き）
        $contents = $dom->saveXml();
header("Content-Type: text/xml; charset=utf-8");
echo $contents;
    }
}
