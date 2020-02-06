<?php

defined("APP_DESC") OR exit('No direct script access allowed');

class Subscriber {

    public function __construct() {
        global $database;

        $this->db = $database;
        $this->table_name = "subscribers";
    }

    /**
     * retrieve all subscribers
     * 
     * @param string $state
     * @return array
     */
    public function getAll($state = "") {
        $optWhere = [];

        if (!empty($optWhere)) {
            $optWhere = [
                "state" => $state
            ];
        }

        $records = $this->db->select($this->table_name, "*", $optWhere);
        $recordCount = count($records);
        
        $states = SUBSCRIBER_STATES;
        for ($x=0; $x<$recordCount; $x++) {
            $records[$x]["state_label"] = $states[$records[$x]["state"]];
        }
        
        $data = array(
            "total" => $recordCount,
            "data" => $records
        );

        return $data;
    }

    /**
     * add subscriber form data
     */
    public function add($inputData) {
        // example data
        /*
          $inputData = array(
          "email" => "demo@mailerlite.com",
          "name" => "john",
          "fields" => array(
          "company" => "MailerLite"
          )
          );
         * 
         */

        // validation
        if (($emailSignPosition = strpos($inputData["email"], "@")) !== false) {
            $domain = substr($inputData["email"], $emailSignPosition + 1);
        }

        if (!filter_var($inputData["email"], FILTER_VALIDATE_EMAIL)) {
            // error
            return [
                "error" => 1,
                "message" => "Invalid email address"
            ];
        } else if (gethostbyname($domain) == $domain) {
            // error
            return [
                "error" => 1,
                "message" => "Invalid email domain"
            ];
        } else if (!is_string($inputData["name"])) {
            // error
            return [
                "error" => 1,
                "message" => "Name value is not valid"
            ];
        } else if (array_key_exists("fields", $inputData) && !is_array($inputData["fields"])) {
            // error
            return [
                "error" => 1,
                "message" => "Field data should be array"
            ];
        }

        foreach ($inputData["fields"] as $fieldKey => $fieldValue) {
            if (!is_string($fieldValue)) {
                return [
                    "error" => 1,
                    "message" => "Invalid value for {$fieldKey}"
                ];
            } else if (empty($fieldValue)) {
                // remove empty entry
                unset($inputData["fields"][$fieldKey]);
            }
        }

        $fetch = new Fetch;
        list($responseError, $responseData) = $fetch->process($this->table_name, [
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($inputData)
        ]);

        if ($responseError) {
            return [
                "error" => 1,
                "message" => $responseError
            ];
        }

        // insert to DB
        $subscriberInfo = $inputData;
        $fieldInfo = [];

        if (array_key_exists("fields", $subscriberInfo)) {
            $fieldInfo = $subscriberInfo["fields"];
            unset($subscriberInfo["fields"]);
        }
        $subscriberCreated = $this->create($inputData);

        if ($subscriberCreated === false) {
            return [
                "error" => 1,
                "message" => "Check your email address."
            ];
        }

        if (!empty($fieldInfo)) {
            $fieldInfoToInsert = [];
            $field = new Field;

            // find IDs and map
            $fieldList = $field->getAll();

            foreach ($fieldList["data"] as $fieldKey => $fieldValue) {
                if (array_key_exists($fieldValue["keyword"], $fieldInfo)) {
                    $fieldInfoToInsert[] = [
                        "subscriber_id" => $subscriberCreated,
                        "field_id" => $fieldValue["id"],
                        "value" => $fieldInfo[$fieldValue["keyword"]],
                        "created_at" => date("Y-m-d H:i:s"),
                        "updated_at" => date("Y-m-d H:i:s")
                    ];
                }
            }

            $fieldCreated = $field->create($fieldInfoToInsert);

            if (!$fieldCreated) {
                return [
                    "error" => 1,
                    "message" => "Field entry could not be created in DB. Please contact admin."
                ];
            }

            return [
                "error" => 0,
                "message" => "Successfully created"
            ];
        } else {
            return [
                "error" => 0,
                "message" => "Successfully created"
            ];
        }

        return [
            "error" => 1,
            "message" => "Something went wrong. Please try again."
        ];
    }

    /**
     * insert an entry to database table
     * 
     * @return boolean 
     */
    private function create($inputData) {
        if (!is_array($inputData) || empty($inputData)) {
            return false;
        }

        $insertData = array(
            "email" => $inputData["email"],
            "name" => $inputData["name"],
            "state" => array_key_exists("state", $inputData) ? $inputData["state"] : SUBSCRIBER_DEFAULT_STATE,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        );

        $inserted = $this->db->insert($this->table_name, $insertData);

        if ($inserted->rowCount()) {
            return $this->db->id();
        }

        return false;
    }

}
