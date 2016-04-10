<?php
    require_once('FirePHPCore/FirePHP.class.php');
    $firephp = FirePHP::getInstance(true); // debugger in firefox
    //object oriented programming method in php that perform CRUD
	class SimpleDao {
        private $_table = null;
        private static $_con = null;
		//open connect of database
        public function SimpleDao() {
            if ($this->_con == null) {
                $this->_con = @mysql_connect("hostname", "user", "password");
                if ($this->_con == FALSE) {
                    echo("connect to db server failed.");
                    $this->_con = null;
                    return;
                }
                //$firephp->log("new DAO object");
                @mysql_select_db("databasename", $this->_con);
            }
        }
		//find current existing table in database
        public function table($tablename) {
            $this->_table = $tablename;
            return $this;
        }
		//fetch the array result
        public function query($sql) {
            $result = @mysql_query($sql);
            $ret = [];
            if ($result) {
                while ($row = mysql_fetch_array($result)) {
                    $ret[] = $row;
                }
            }
            return $ret;
        }
		//list out all existing table in db
        public function get($where = null) {
            $sql = "select * from ".$this->_table;
            $sql = $sql.$this->_getWhereString($where);
            //echo "[get]".$sql."<br>";
            return $this->query($sql);
        }
		// insert object into table
        public function insert($params) {
            if ($params == null || !is_array($params)) {
                return -1;
            }
            $keys = $this->_getParamKeyString($params);
            $vals = $this->_getParamValString($params);
            $sql = "insert into ".$this->_table."(".$keys.") values(".$vals.")";
            //echo "[insert]".$sql."<br>";
            $result = @mysql_query($sql);
            if (! $result) {
                return -1;
            }
            return @mysql_insert_id();
        }
		//operate update db
        public function update($params, $where = null) {
            if ($params == null || !is_array($params)) {
                return -1;
            }
            $upvals = $this->_getUpdateString($params);
            $wheres = $this->_getWhereString($where);
            $sql = "update ".$this->_table." set ".$upvals." ".$wheres;
            //echo "[update]".$sql."<br>";
            $result = @mysql_query($sql);
            if (! $result) {
                return -1;
            }
            return @mysql_affected_rows();
        }
		//operate delete selected item.
        public function delete($where) {
            $wheres = $this->_getWhereString($where);
            $sql = "delete from ".$this->_table.$wheres;
            //echo "[delete]".$sql."<br>";
            $result = @mysql_query($sql);
            if (! $result) {
                return -1;
            }
            return @mysql_affected_rows();
        }
		// create return value 
        protected function _getParamKeyString($params) {
            $keys = array_keys($params);
            return implode(",", $keys);
        }
		
        protected function _getParamValString($params) {
            $vals = array_values($params);
            return "'".implode("','", $vals)."'";
        }
		
        private function _getUpdateString($params) {
            //echo "_getUpdateString";
            $sql = "";
            if (is_array($params)) {
                $sql = $this->_getKeyValString($params, ",");
            }
            return $sql;
        }
		
        private function _getWhereString($params) {
            //echo "_getWhereString";
            $sql = "";
            if (is_array($params)) {
                $sql = " where ";
                $where = $this->_getKeyValString($params, " and ");
                $sql = $sql.$where;
            }
            return $sql;
        }
		
        private function _getKeyValString($params, $split) {
            $str = "";
            if (is_array($params)) {
                $paramArr = array();
                foreach($params as $key=>$val) {
                    $valstr = $val;
                    if (is_string($val)) {
                        $valstr = "'".$val."'";
                    }
                    $paramArr[] = $key."=".$valstr;
                }
                $str = $str.implode($split, $paramArr);
            }
            return $str;
        }
        public function release() {
            @mysql_close();
        }//close db
    }
	//refresh table list
    function T($table) {
        return (new SimpleDao())->table($table);
    }
?>