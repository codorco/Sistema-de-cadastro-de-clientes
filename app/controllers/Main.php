<?php

namespace scc\Controllers;

use scc\Controllers\BaseController;
use scc\Models\Agents;
use scc\System\SendEmail;

class Main extends BaseController
{
    // =======================================================
    public function index()
    {
        // Verifique se não há nenhum usuário ativo na sessão.
        if(!check_session())
        {
            $this->login_frm();
            return;
        }

        $data['user'] = $_SESSION['user'];

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('homepage', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    // LOGIN
    // =======================================================
    public function login_frm()
    {
        // Verificar se já existe um usuário na sessão.
        if(check_session())
        {
            $this->index();
            return;
        }
            
        // Verificar se há erros (após login_submit)
        $data = [];
        if(!empty($_SESSION['validation_errors']))
        {
            $data['validation_errors'] = $_SESSION['validation_errors'];
            unset($_SESSION['validation_errors']);
        }
        //A verificação indica que o login é inválido.
        if(!empty($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        // Exibir formulário de login
        $this->view('layouts/html_header');
        $this->view('login_frm', $data);
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function login_submit()
    {
        // Verifique se já existe uma sessão ativa.
        if(check_session()){
            $this->index();
            return;
        }

        // verificar se houve uma solicitação de postagem
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // validação de formulário
        $validation_errors = [];
        if(empty($_POST['text_username']) || empty($_POST['text_password'])){
            $validation_errors[] = "Username e password são obrigatórios.";
        }

        // check if there are validation errors
        if(!empty($validation_errors)){
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // get form data
        $username = $_POST['text_username'];
        $password = $_POST['text_password'];

        // Verifique se o nome de usuário é um e-mail válido e se tem entre 5 e 50 caracteres.
        if(!filter_var($username, FILTER_VALIDATE_EMAIL))
        {
            $validation_errors[] = 'O username tem que ser um email válido.';
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // Verifique se o nome de usuário tem entre 5 e 50 caracteres.
        if(strlen($username) < 5 || strlen($username) > 50){
            $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        // Verifique se a senha é válida.
        if(strlen($password) < 6 || strlen($password) > 12){
            $validation_errors[] = 'A password deve ter entre 6 e 12 caracteres.';
            $_SESSION['validation_errors'] = $validation_errors;
            $this->login_frm();
            return;
        }

        $model = new Agents();
        $result = $model->check_login($username, $password);
        if(!$result['status']){

            // logger
            logger("$username - login invalido", 'error');

            // Login Invalido
            $_SESSION['server_error'] = 'Login inválido.';
            $this->login_frm();
            return;

        }

        // logger
        logger("$username - Login com sucesso");

        // Carregar informações do usuário para a sessão
        $results = $model->get_user_data($username);

        // Adiciona o usuario a sessão 
        $_SESSION['user'] = $results['data'];

        // Atualiza a data do último login no bd
        $results = $model->set_user_last_login($_SESSION['user']->id);

        // Ir para a página principal
        $this->index();
    }

    // =======================================================
    public function logout()
    {
        // disable direct access to logout
        if(!check_session()){
            $this->index();
            return;
        }

        // logger
        logger($_SESSION['user']->name . ' - Fez logout');

        // clear user from session
        unset($_SESSION['user']);

        // go to index (login form)
        $this->index();
    }

   
 // =======================================================
    // alteração de senha do perfil
    // =======================================================
    public function change_password_frm()
    {
        if(!check_session()){
            $this->index();
            return;
        }

        $data['user'] = $_SESSION['user'];

        // verificar erros de validação
        if(!empty($_SESSION['validation_errors'])){
            $data['validation_errors'] = $_SESSION['validation_errors'];
            unset($_SESSION['validation_errors']);
        }

        // verificar erros do servidor
        if(!empty($_SESSION['server_errors'])){
            $data['server_errors'] = $_SESSION['server_errors'];
            unset($_SESSION['server_errors']);
        }

        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('profile_change_password_frm', $data);
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function change_password_submit()
    {
        if(!check_session()){
            $this->index();
            return;
        }

        // verificar se houve uma solicitação de postagem
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // erros de validação
        $validation_errors = [];

        // Verifica se os campos de entrada estão preenchidos.
        if(empty($_POST['text_current_password'])){
            $validation_errors[] = "Password atual é de preenchimento obrigatório.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }
        if(empty($_POST['text_new_password'])){
            $validation_errors[] = "A nova password é de preenchimento obrigatório.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }
        if(empty($_POST['text_repeat_new_password'])){
            $validation_errors[] = "A repetição da nova password é de preenchimento obrigatório.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }

        // obtem os valores de entrada
        $current_password = $_POST['text_current_password'];
        $new_password = $_POST['text_new_password'];
        $repeat_new_password = $_POST['text_repeat_new_password'];

        // Verifica se todas as senhas têm mais de 6 e menos de 12 caracteres.
        if(strlen($current_password < 6 || strlen($current_password) > 12)){
            $validation_errors[] = "A password atual deve ter entre 6 e 12 caracteres.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }

        if(strlen($new_password) < 6 || strlen($new_password) > 12){
            $validation_errors[] = "A nova password deve ter entre 6 e 12 caracteres.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }
        
        if(strlen($repeat_new_password) < 6 || strlen($repeat_new_password) > 12){
            $validation_errors[] = "A repetição da nova password deve ter entre 6 e 12 caracteres.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }

        // Verifica se todas as senhas têm, pelo menos, uma letra maiúscula, uma letra minúscula e um dígito.
        
        // Obriga a usar um padrao de senha mais seguro.
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $current_password)){
            $validation_errors[] = "A password atual deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $new_password)){
            $validation_errors[] = "A nova password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $repeat_new_password)){
            $validation_errors[] = "A repetição da nova password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }

        // Verifica se a nova senha e a nova senha repetida têm valores iguais.
        if($new_password != $repeat_new_password){
            $validation_errors[] = "A password e a sua repetição não são iguais.";
            $_SESSION['validation_errors'] = $validation_errors;
            $this->change_password_frm();
            return;
        }

        // Verifica se a senha atual é igual à senha do banco de dados.
        $model = new Agents();
        $results = $model->check_current_password($current_password);

        // Verifica se a senha atual está correta.
        if(!$results['status']){

            // A senha atual não corresponde à senha existente no banco de dados.
            $server_errors[] = "A password atual não está correta.";
            $_SESSION['server_errors'] = $server_errors;
            $this->change_password_frm();
            return;
        }

        // Os dados do formulário estão corretos. A senha é atualizada no banco de dados.
        $model->update_agent_password($new_password);

        // logger
        $username = $_SESSION['user']->name;
        logger("$username - password alterada com sucesso no perfil de utilizador.");

        // Exibir visualização com informações de sucesso
        $data['user'] = $_SESSION['user'];
        $this->view('layouts/html_header');
        $this->view('navbar', $data);
        $this->view('profile_change_password_success');
        $this->view('footer');
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function define_password($purl = '')
    {
        // Se houver uma sessão aberta, saia!
        if(check_session()){
            $this->index();
            return;
        }
        
        // verifica se o purl é válido
        if(empty($purl) || strlen($purl) != 20){
            die('Erro nas credenciais de acesso.');
        }
        
        // Verifica se há um novo agente com este purl
        $model = new Agents();
        $results = $model->check_new_agent_purl($purl);
        
        if(!$results['status']){
            die('Erro nas credenciais de acesso.');
        }

        // verificar erros de validação
        if(isset($_SESSION['validation_error'])){
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }

        $data['purl'] = $purl;
        $data['id'] = $results['id'];
        
        // Exibe a visualização de definição de senha
        $this->view('layouts/html_header');
        $this->view('new_agent_define_password', $data);
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function define_password_submit()
    {
        // Se houver uma sessão aberta, saia!
        if(check_session()){
            $this->index();
            return;
        }

        // verificar se houve uma postagem
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // Validação de formulário - verificar campos ocultos
        if(empty($_POST['purl']) || empty($_POST['id']) || strlen($_POST['purl']) != 20){
            $this->index();
            return;
        }

        // obter campos ocultos
        $id = aes_decrypt($_POST['id']);
        $purl = $_POST['purl'];
        
        // verificar se o ID é válido
        if(!$id){
            $this->index();
            return;
        }

        // Validação de formulário - verificar a estrutura da senha
        if(empty($_POST['text_password'])){
            $_SESSION['validation_error'] = "Password é de preenchimento obrigatório.";
            $this->define_password($purl);
            return;
        }
        if(empty($_POST['text_repeat_password'])){
            $_SESSION['validation_error'] = "Repetir a password é de preenchimento obrigatório.";
            $this->define_password($purl);
            return;
        }

        // obter os valores de entrada
        $password = $_POST['text_password'];
        $repeat_password = $_POST['text_repeat_password'];

        if(strlen($password) < 6 || strlen($password) > 12){
            $_SESSION['validation_error'] = "A password deve ter entre 6 e 12 caracteres.";
            $this->define_password($purl);
            return;
        }
        if(strlen($repeat_password < 6 || strlen($repeat_password) > 12)){
            $_SESSION['validation_error'] = "A repetição da password deve ter entre 6 e 12 caracteres.";
            $this->define_password($purl);
            return;
        }

        // Verifique se todas as senhas têm, pelo menos, uma letra maiúscula, uma letra minúscula e um dígito.

        // Use uma perspectiva positiva para o futuro.
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $password)){
            $_SESSION['validation_error'] = "A password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $this->define_password($purl);
            return;
        }
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $repeat_password)){
            $_SESSION['validation_error'] = "A repetição da password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $this->define_password($purl);
            return;
        }

        // Verifique se a senha e a senha repetida têm valores iguais.
        if($password != $repeat_password){
            $_SESSION['validation_error'] = "A nova password e a sua repetição não são iguais.";
            $this->define_password($purl);
            return;
        }

        // Atualiza o banco de dados com a senha do agente.
        $model = new Agents();
        $model->set_agent_password($id, $password);

        // logger
        logger("Foi definida com sucesso a password para o agente ID = $id (purl: $purl)");

        // Exibir a visualização com a página de sucesso.
        $this->view('layouts/html_header');
        $this->view('reset_password_define_password_success');
        $this->view('layouts/html_footer');        
    }
// =======================================================
    public function reset_password()
    {
        // Se houver uma sessão aberta, saia!
        if(check_session()){
            $this->index();
            return;
        }

        $data = [];

        // Verifica erros de validação.
        if(isset($_SESSION['validation_error'])){
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }
        
        // Verifica erros do servidor.
        if(isset($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        // Exibe a visualização do formulário de recuperação de senha.
        $this->view('layouts/html_header');
        $this->view('reset_password_frm', $data);
        $this->view('layouts/html_footer'); 
    }

    // =======================================================
    public function reset_password_submit()
    {
        // Se houver uma sessão aberta, saia!
        if(check_session()){
            $this->index();
            return;
        }

        // Verifica se houve uma postagem.
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // Validação de formulário.
        if(empty($_POST['text_username'])){
            $_SESSION['validation_error'] = "Utilizador é de preenchimento obrigatório.";
            $this->reset_password();
            return;
        }
        if(!filter_var($_POST['text_username'], FILTER_VALIDATE_EMAIL)){
            $_SESSION['validation_error'] = "Utilizador tem que ser um email válido.";
            $this->reset_password();
            return;
        }

        $username = $_POST['text_username'];

        // Define um código para recuperar a senha, envia um email e exibe a página do código.
        $model = new Agents();
        $results = $model->set_code_for_recover_password($username);

        if($results['status'] == 'error'){

            // logger
            logger("Aconteceu um erro na criação do código de recuperação da password. User: $username", 'error');

            $_SESSION['validation_error'] = "Aconteceu um erro inesperado. Por favor tente novamente.";
            $this->reset_password();
            return;
        }

        $id = $results['id'];
        $code = $results['code'];

        // O código está armazenado. Enviar email com o código.
        $email = new SendEmail();
        $results = $email->send_email(APP_NAME . ' Código para recuperar a password', 'codigo_recuperar_password', ['to' => $username, 'code' => $results['code']]);

        if($results['status'] == 'error')
        {
            // logger
            logger("Aconteceu um erro no envio do email com o código de recuperação da password. User: $username", 'error');

            $_SESSION['validation_error'] = "Aconteceu um erro inesperado. Por favor tente novamente.";
            $this->reset_password();
            return;
        }

        // logger
        logger("Email com código de recuperação de password enviado com sucesso. User: $username | Code: $code");

        // O email foi enviado. Exibe a próxima visualização.
        $this->insert_code(aes_encrypt($id));
    }

   // =======================================================
    public function insert_code($id = '')
    {
        // if there is a open session, gets out!
        if(check_session()){
            $this->index();
            return;
        }

        // check if id is valid
        if(empty($id)){
            $this->index();
            return;
        }

        $id = aes_decrypt($id);
        if(!$id){
            $this->index();
            return;
        }

        $data['id'] = $id;

        // check for validation errors or server errors
        if(isset($_SESSION['validation_error'])){
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }

        if(isset($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        // display the view
        $this->view('layouts/html_header');
        $this->view('reset_password_insert_code', $data);
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function insert_code_submit($id = '')
    {
        // if there is a open session, gets out!
        if(check_session()){
            $this->index();
            return;
        }

        // check if id is valid
        if(empty($id)){
            $this->index();
            return;
        }

        $id = aes_decrypt($id);
        if(!$id){
            $this->index();
            return;
        }

        // check if his a post
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // form validation
        if(empty($_POST['text_code'])){
            $_SESSION['validation_error'] = "Código é de preenchimento obrigatório.";
            $this->insert_code(aes_encrypt($id));
            return;
        }
        
        $code = $_POST['text_code'];
        
        if(!preg_match("/^\d{6}$/", $code)){
            $_SESSION['validation_error'] = "O código é constituído por 6 números.";
            $this->insert_code(aes_encrypt($id));
            return;
        }

        // check if the code is the same that is stored in the database
        $model = new Agents();
        $results = $model->check_if_reset_code_is_correct($id, $code);
        
        if(!$results['status']){

            $_SESSION['server_error'] = "Código incorreto.";
            $this->insert_code(aes_encrypt($id));
            return;

        }

        // the code is correct. Let's define the password
        $this->reset_define_password(aes_encrypt($id));
    }
        // =======================================================
    public function reset_define_password($id = '')
    {
        // if there is a open session, gets out!
        if(check_session()){
            $this->index();
            return;
        }

        // check if id is valid
        if(empty($id)){
            $this->index();
            return;
        }

        $id = aes_decrypt($id);
        if(!$id){
            $this->index();
            return;
        }

        $data['id'] = $id;

        // check for validation error
        if(isset($_SESSION['validation_error'])){
            $data['validation_error'] = $_SESSION['validation_error'];
            unset($_SESSION['validation_error']);
        }

        // check for server error
        if(isset($_SESSION['server_error'])){
            $data['server_error'] = $_SESSION['server_error'];
            unset($_SESSION['server_error']);
        }

        // display the form to define de new password
        $this->view('layouts/html_header');
        $this->view('reset_password_define_password_frm', $data);
        $this->view('layouts/html_footer');
    }

    // =======================================================
    public function reset_define_password_submit($id = '')
    {
        // if there is a open session, gets out!
        if(check_session()){
            $this->index();
            return;
        }

        // check if id is valid
        if(empty($id)){
            $this->index();
            return;
        }

        $id = aes_decrypt($id);
        if(!$id){
            $this->index();
            return;
        }

        // check if there was a post
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            $this->index();
            return;
        }

        // form validation
        if(empty($_POST['text_new_password'])){
            $_SESSION['validation_error'] = "Nova password é de preenchimento obrigatório.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }
        if(empty($_POST['text_repeat_new_password'])){
            $_SESSION['validation_error'] = "A repetição da nova password é de preenchimento obrigatório.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }

        // get the input values
        $new_password = $_POST['text_new_password'];
        $repeat_new_password = $_POST['text_repeat_new_password'];
        
        // check if all passwords have more than 6 and less than 12 characters
        if(strlen($new_password) < 6 || strlen($new_password) > 12){
            $_SESSION['validation_error'] = "A nova password deve ter entre 6 e 12 caracteres.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }
        if(strlen($repeat_new_password) < 6 || strlen($repeat_new_password) > 12){
            $_SESSION['validation_error'] = "A repeição da nova password deve ter entre 6 e 12 caracteres.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }

        // check if all password have, at least one upper, one lower and one digit
        
        // use positive look ahead
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $new_password)){
            $_SESSION['validation_error'] = "A nova password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }
        if(!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/", $repeat_new_password)){
            $_SESSION['validation_error'] = "A repetição da nova password deve ter, pelo menos, uma maiúscula, uma minúscula e um dígito.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }
        
        // check if both passwords are equal
        if($new_password != $repeat_new_password){
            $_SESSION['validation_error'] = "As nova password e a sua repetição devem ser iguais.";
            $this->reset_define_password(aes_encrypt($id));
            return;
        }

        // updates the agent's password in the database
        $model = new Agents();
        $model->change_agent_password($id, $new_password);

        // logger
        logger("Foi alterada com sucesso a password do user ID: $id após pedido de reset da password.");

        // display success page
        $this->view('layouts/html_header');
        $this->view('profile_change_password_success');
        $this->view('layouts/html_footer');
    }
}