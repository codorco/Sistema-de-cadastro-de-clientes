<div class="container-fluid">
    <div class="row justify-content-center">

        <!-- os meus clientes -->
        <div class="col-12 p-5 bg-white">

            <div class="row">
                <div class="col">
                    <h5><i class="fa-solid fa-user-tie me-2"></i>Agente: <strong><?= $user->name ?></strong></h5>
                </div>
                <div class="col text-end">
                    <a href="#" class="btn btn-secondary"><i class="fa-solid fa-upload me-2"></i></i>Carregar ficheiro</a>
                    <a href="?ct=agent&mt=new_client_frm" class="btn btn-secondary"><i class="fa-solid fa-plus me-2"></i>Novo cliente</a>
                </div>
            </div>

            <hr>

            <?php if(empty($clients)): ?>
                <p class="my-5 text-center opacity-75">Não existem clientes registados.</p>
            <?php else: ?>
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Sexo</th>
                            <th class="text-center">Data nascimento</th>
                            <th>Email</th>
                            <th class="text-center">Telefone</th>
                            <th>Interesses</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clients as $client): ?>
                        <tr>
                            <td><?= $client->name ?></td>
                            <td class="text-center"><?= $client->gender ?></td>
                            <td class="text-center"><?= $client->birthdate ?></td>
                            <td><?= $client->email ?></td>
                            <td class="text-center"><?= $client->phone ?></td>
                            <td><?= $client->interests ?></td>
                            <td class="text-end">
                                <a href="?ct=agent&mt=edit_client&id=<?= $client->id ?>"><i class="fa-regular fa-pen-to-square me-2"></i>Editar</a>
                                <span class="mx-2 opacity-50">|</span>
                                <a href="?ct=agent&mt=delete_client&id=<?= $client->id ?>"><i class="fa-solid fa-trash-can me-2"></i>Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    
                <div class="row">
                    <div class="col">
                        <p class="mb-5">Total: <strong><?= count($clients) ?></strong></p>
                    </div>
                    <div class="col text-end">
                        <a href="#" class="btn btn-secondary">
                            <i class="fa-regular fa-file-excel me-2"></i>Exportar para XLSX
                        </a>
                    </div>
                </div>
            <?php endif;?>

            
        </div>
    </div>
</div>