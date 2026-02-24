<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 p-5 bg-white">

            <div class="row">
                <div class="col">
                    <h4>Gestão de agentes</h4>
                </div>
                <div class="col text-end">
                    <a href="#" class="btn btn-secondary"><i class="fa-solid fa-user-plus me-2"></i>Novo agente</a>
                </div>
            </div>

            <hr>

            <?php if (count($agents) == 0) : ?>

                <p class="my-5 text-center opacity-75">Não existem agentes registados.</p>

                <div class="mb-5 text-center">
                    <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                </div>

            <?php else : ?>

                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Perfil</th>
                            <th class="text-center">Último login</th>
                            <th class="text-center">Criado em</th>
                            <th class="text-center">Atualizado em</th>
                            <th class="text-center">Eliminado em</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent) : ?>
                            <tr>
                                <td><?= $agent->name?></td>
                                <td class="text-center"><?= $agent->profile ?></td>
                                <td class="text-center"><?= $agent->last_login ?></td>
                                <td class="text-center"><?= $agent->created_at ?></td>
                                <td class="text-center"><?= $agent->updated_at ?></td>
                                <td class="text-center"><?= $agent->deleted_at ?></td>
                                <td class="text-end">
                                    <a href="#"><i class="fa-regular fa-pen-to-square me-2"></i>Editar</a>
                                    <span class="mx-2 opacity-50">|</span>
                                    <a href="#"><i class="fa-solid fa-trash-can me-2"></i>Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row">
                    <div class="col">
                        <p class="mb-5">Total: <strong> <?= count($agents) ?></strong></p>
                    </div>
                    <div class="col text-end">
                        <a href="#" class="btn btn-secondary px-4"><i class="fa-regular fa-file-excel me-2"></i>Exportar para XLSX</a>
                        <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>