<?php

defined("APP_DESC") OR exit('No direct script access allowed');

class Field {

    public function __construct() {
        global $database;

        $this->db = $database;
        $this->table_name = "fields";
        $this->table_name_values = "field_values";
    }

    /**
     * retrieve all field data
     * 
     * @return array
     */
    public function getAll() {
        $records = $this->db->select($this->table_name, "*");

        $recordCount = count($records);
        
        $fieldDataTypes = FIELD_DATATYPES;
        for ($x=0; $x<$recordCount; $x++) {
            $records[$x]["datatype"] = $fieldDataTypes[$records[$x]["type"]];
        }
        
        $data = array(
            "total" => $recordCount,
            "data" => $records
        );

        return $data;
    }

    /**
     * insert an entry to database table
     * 
     * @return boolean 
     */
    public function create($inputData) {
        if (!is_array($inputData) || empty($inputData)) {
            return false;
        }

        $inserted = $this->db->insert($this->table_name_values, $inputData);

        if ($inserted->rowCount()) {
            return true;
        }

        return false;
    }

}
