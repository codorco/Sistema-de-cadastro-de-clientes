<?php

namespace scc\Models;

use scc\Models\BaseModel;

class Agents extends BaseModel
{
    // =======================================================
    public function check_login($username, $password)
    {
        // Verifique se o login é válido.
        $params = [
            ':username' => $username
        ];

        // verificar se existe um usuário no banco de dados
        $this->db_connect();
        $results = $this->query(
            "SELECT id, passwrd FROM agents " .
                "WHERE AES_ENCRYPT(:username, '" . MYSQL_AES_KEY . "') = name " .
                "AND deleted_at IS NULL",
            $params);

        // Se não houver usuário, retorna falso.
        if ($results->affected_rows == 0) {
            return [
                'status' => false
            ];
        }

        // Existe um usuário com esse nome (nome de usuário)
        // Verifique se a senha está correta
        if (!password_verify($password, $results->results[0]->passwrd)) {
            return [
                'status' => false
            ];
        }

        // login esta ok
        return [
            'status' => true
        ];
    }
    // =======================================================
    public function get_user_data($username)
    {
        // Obter todos os dados do usuário necessários para inserir na sessão
        $params = [
            ':username' => $username
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT " .
                "id, " .
                "AES_DECRYPT(name, '" . MYSQL_AES_KEY . "') name, " .
                "profile " .
                "FROM agents " .
                "WHERE AES_ENCRYPT(:username, '" . MYSQL_AES_KEY . "') = name",
            $params
        );

        return [
            'status' => 'success',
            'data' => $results->results[0]
        ];
    }

      // =======================================================
     public function set_user_last_login($id)
    {
        // Atualiza o último login do usuário.
        $params = [
            ':id' => $id
        ];
        $this->db_connect();
        $results = $this->non_query(
            "UPDATE agents SET " . 
            "last_login = NOW() " . 
            "WHERE id = :id",
        $params);
        return $results;
    }
    // =======================================================
    public function get_agent_clients($id_agent)
    {
        // Obtenha todos os clientes do agente com o id_agent especificado.
        $params = [
            ':id_agent' => $id_agent
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT " .
                "id, " .
                "AES_DECRYPT(name, '" . MYSQL_AES_KEY . "') name, " .
                "gender, " .
                "birthdate, " .
                "AES_DECRYPT(email, '" . MYSQL_AES_KEY . "') email, " .
                "AES_DECRYPT(phone, '" . MYSQL_AES_KEY . "') phone, " .
                "interests, " .
                "created_at, " .
                "updated_at " .
                "FROM persons " .
                "WHERE id_agent = :id_agent " .
                "AND deleted_at IS NULL",
            $params
        );

        return [
            'status' => 'success',
            'data' => $results->results
        ];
    }
    public function check_if_client_exists($post_data)
    {
        // Verifique se já existe um cliente com o mesmo nome.
        $params = [
            ':id_agent' => $_SESSION['user']->id,
            ':client_name' => $post_data['text_name']
        ];

        $this->db_connect();
        $results = $this->query(
            "SELECT id FROM persons " . 
            "WHERE AES_ENCRYPT(:client_name, '" . MYSQL_AES_KEY . "') = name " . 
            "AND id_agent = :id_agent",
            $params
        );

        if($results->affected_rows == 0){
            return [
                'status' => false
            ];
        } else {
            return [
                'status' => true
            ];
        }
    }
 // =======================================================
    public function add_new_client_to_database($post_data)
    {
        // Adicionar novo cliente ao banco de dados

        $birthdate = new \DateTime($post_data['text_birthdate']);

        $params = [
            ':name' => $post_data['text_name'],
            ':gender' => $post_data['radio_gender'],
            ':birthdate' => $birthdate->format('Y-m-d H:i:s'),
            ':email' => $post_data['text_email'],
            ':phone' => $post_data['text_phone'],
            ':interests' => $post_data['text_interests'],
            ':id_agent' => $_SESSION['user']->id
        ];

        $this->db_connect();
        $this->non_query(
            "INSERT INTO persons VALUES(" .
                "0, " .
                "AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'), " .
                ":gender, " .
                ":birthdate, " .
                "AES_ENCRYPT(:email, '" . MYSQL_AES_KEY . "'), " .
                "AES_ENCRYPT(:phone, '" . MYSQL_AES_KEY . "'), " .
                ":interests, " .
                ":id_agent, " .
                "NOW(), " .
                "NOW(), " .
                "NULL" .
                ")",
            $params
        );
    }
// =======================================================
    public function get_client_data($id_client)
    {
        // Obter dados do cliente por ID
        $params = [
            ':id_client' => $id_client
        ];

        $this->db_connect();
        $results = $this->query(
            "SELECT " . 
            "id, " . 
            "AES_DECRYPT(name, '" . MYSQL_AES_KEY . "') name, " . 
            "gender, " . 
            "birthdate, " . 
            "AES_DECRYPT(email, '" . MYSQL_AES_KEY . "') email, " . 
            "AES_DECRYPT(phone, '" . MYSQL_AES_KEY . "') phone, " . 
            "interests " . 
            "FROM persons " . 
            "WHERE id = :id_client"
        , $params);

        if($results->affected_rows == 0){
            return [
                'status' => 'error'
            ];
        }

        return [
            'status' => 'success',
            'data' => $results->results[0]
        ];
    }
// =======================================================
    public function check_other_client_with_same_name($id, $name)
    {
        // Verificar se já existe outro cliente do mesmo agente com o mesmo nome.
        $params = [
            ':id' => $id,
            ':name' => $name,
            ':id_agent' => $_SESSION['user']->id
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT id " .
                "FROM persons " .
                "WHERE id <> :id " .
                "AND id_agent = :id_agent " .
                "AND AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "') = name",
            $params
        );

        if ($results->affected_rows != 0) {
            return ['status' => true];
        } else {
            return ['status' => false];
        }
    }

    // =======================================================
    public function update_client_data($id, $post_data)
    {
        // Atualiza os dados do cliente do agente no banco de dados.
        $birthdate = new \DateTime($post_data['text_birthdate']);
        $params = [
            ':id' => $id,
            ':name' => $post_data['text_name'],
            ':gender' => $post_data['radio_gender'],
            ':birthdate' => $birthdate->format('Y-m-d H:i:s'),
            ':email' => $post_data['text_email'],
            ':phone' => $post_data['text_phone'],
            ':interests' => $post_data['text_interests'],
        ];
        $this->db_connect();
        $this->non_query(
            "UPDATE persons SET " . 
            "name = AES_ENCRYPT(:name, '" . MYSQL_AES_KEY . "'), " .
            "gender = :gender, " . 
            "birthdate = :birthdate, " . 
            "email = AES_ENCRYPT(:email, '" . MYSQL_AES_KEY . "'), " .
            "phone = AES_ENCRYPT(:phone, '" . MYSQL_AES_KEY . "'), " .
            "interests = :interests, " . 
            "updated_at = NOW() " . 
            "WHERE id = :id"
        , $params);
    }
     public function delete_client($id_client)
    {
        // Exclui o cliente do banco de dados (exclusão permanente).
        $params = [
            ':id' => $id_client
        ];
        $this->db_connect();
        $this->non_query("DELETE FROM persons WHERE id = :id", $params);
    }
// =======================================================
    public function check_current_password($current_password)
    {
        // Verifica se a senha atual é igual à senha armazenada no banco de dados.
        $params = [
            ':id_user' => $_SESSION['user']->id
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT passwrd " .
                "FROM agents " .
                "WHERE id = :id_user",
            $params
        );

        if (password_verify($current_password, $results->results[0]->passwrd)) {
            return [
                'status' => true
            ];
        } else {
            return [
                'status' => false
            ];
        }
    }

    // =======================================================
    public function update_agent_password($new_passwrd)
    {
        // Atualiza a senha do usuário atual
        $params = [
            ':passwrd' => password_hash($new_passwrd, PASSWORD_DEFAULT),
            ':id' => $_SESSION['user']->id
        ];

        $this->db_connect();
        $this->non_query(
            "UPDATE agents SET " . 
            "passwrd = :passwrd, " . 
            "updated_at = NOW() " . 
            "WHERE id = :id"
        , $params);
    }

  // =======================================================
    public function check_new_agent_purl($purl)
    {
        // Verifica se há um novo agente com este purl
        $params = [
            ':purl' => $purl
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT id FROM agents WHERE purl = :purl"
        , $params);

        if($results->affected_rows == 0){
            return [
                'status' => false
            ];
        } else {
            return [
                'status' => true,
                'id' => $results->results[0]->id
            ];
        }
    }
    // =======================================================
    public function set_agent_password($id, $new_passwrd)
    {
        // Atualiza a senha do usuário atual
        $params = [
            ':passwrd' => password_hash($new_passwrd, PASSWORD_DEFAULT),
            ':id' => $id
        ];

        $this->db_connect();
        $this->non_query(
            "UPDATE agents SET " . 
            "passwrd = :passwrd, " . 
            "purl = NULL, " .
            "updated_at = NOW() " . 
            "WHERE id = :id"
        , $params);
    }
    // =======================================================
    public function set_code_for_recover_password($username)
    {
        // Define um código para recuperar a senha, se a conta existir.
        $params = [
            ':username' => $username
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT id FROM agents " . 
            "WHERE AES_ENCRYPT(:username, '" . MYSQL_AES_KEY . "') = name " . 
            "AND passwrd IS NOT NULL " .
            "AND deleted_at IS NULL"
        , $params);

        // Verifica se nenhum agente foi encontrado.
        if($results->affected_rows == 0){
            return [
                'status' => 'error'
            ];
        }

        // O agente foi encontrado.

        // Gera código.
        $code = rand(100000, 999999);
        $id = $results->results[0]->id;
        $params = [
            ':id' => $id,
            ':code' => $code
        ];

        $results = $this->non_query(
            "UPDATE agents SET " . 
            "code = :code " . 
            "WHERE id = :id"
        , $params);

        return [
            'status' => 'success',
            'id' => $id,
            'code' => $code
        ];
    }

    // =======================================================
    public function check_if_reset_code_is_correct($id, $code)
    {
        // Verifica se o código de reset é igual ao código armazenado na linha do agente.
        $params = [
            ':id' => $id,
            ':code' => $code
        ];
        $this->db_connect();
        $results = $this->query(
            "SELECT id FROM agents " . 
            "WHERE id = :id AND code = :code"
        , $params);
        
        if($results->affected_rows == 0){
            return [
                'status' => false
            ];
        } else {
            return [
                'status' => true
            ];
        }
    }
    // =======================================================
    public function change_agent_password($id, $new_passwrd)
    {
        // Atualiza a senha do usuário atual.
        $params = [
            ':id' => $id,
            ':passwrd' => password_hash($new_passwrd, PASSWORD_DEFAULT)
        ];

        $this->db_connect();
        $this->non_query(
            "UPDATE agents SET " . 
            "passwrd = :passwrd, " . 
            "updated_at = NOW() " . 
            "WHERE id = :id"
        , $params);
    }
}
