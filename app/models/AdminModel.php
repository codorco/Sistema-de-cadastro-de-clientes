<?php

namespace scc\Models;

use scc\Models\BaseModel;

class AdminModel extends BaseModel
{
    // =======================================================
    public function get_all_clients()
    {
        // Obtem todos os clientes de todos os agentes.
        $this->db_connect();
        $results = $this->query(
            "SELECT " . 
            "p.id, " . 
            "AES_DECRYPT(p.name, '" . MYSQL_AES_KEY . "') name, " . 
            "p.gender, " . 
            "p.birthdate, " . 
            "AES_DECRYPT(p.email, '" . MYSQL_AES_KEY . "') email, " . 
            "AES_DECRYPT(p.phone, '" . MYSQL_AES_KEY . "') phone, " . 
            "p.interests, " . 
            "p.created_at, " . 
            "AES_DECRYPT(a.name, '" . MYSQL_AES_KEY . "') agent " . 
            "FROM persons p LEFT JOIN agents a " . 
            "ON p.id_agent = a.id " . 
            "WHERE p.deleted_at IS NULL " . 
            "ORDER BY created_at DESC"
        );
        return $results;
    }
}