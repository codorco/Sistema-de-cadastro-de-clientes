<div class="container-fluid">
    <div class="row justify-content-center">

        <div class="col-12 p-5 bg-white">

            <h4 class="mb-3">Clientes registados</h4>

            <hr>

            <?php if (count($clients) == 0) : ?>
                
                <p class="my-4 text-center opacity-75">Não existem clientes registados.</p>

                <div class="text-center mb-5">
                    <a href="#" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                </div>

            <?php else: ?>
                
                <table class="table table-striped table-bordered" id="table_clients">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Sexo</th>
                            <th class="text-center">Data nascimento</th>
                            <th>Email</th>
                            <th class="text-center">Telefone</th>
                            <th>Interesses</th>
                            <th>Agente</th>
                            <th>Data de registo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clients as $client): ?>
                            <tr>
                                <td><?= $client->name ?></td>
                                <td class="text-center"><?= $client->gender?></td>
                                <td class="text-center"><?= $client->birthdate ?></td>
                                <td><?= $client->email ?></td>
                                <td class="text-center"><?= $client->phone ?></td>
                                <td><?= $client->interests ?></td>
                                <td><?= $client->agent ?></td>
                                <td><?= $client->created_at ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    
                <div class="row mt-3">
                    <div class="col">
                        <p class="mb-5">Total: <strong><?= count($clients) ?></strong></p>
                    </div>
                    <div class="col text-end">
                        <a href="?ct=admin&mt=export_clients_xlsx" class="btn btn-secondary px-4"><i class="fa-regular fa-file-excel me-2"></i>Exportar para XLSX</a>
                        <a href="?ct=main&mt=index" class="btn btn-secondary px-4"><i class="fa-solid fa-chevron-left me-2"></i>Voltar</a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // datatable
        $('#table_clients').DataTable({
            pageLength: 10,
            pagingType: "full_numbers",
            language: {
                decimal: "",
                emptyTable: "Sem dados disponíveis na tabela.",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registos",
                infoEmpty: "Mostrando 0 até 0 de 0 registos",
                infoFiltered: "(Filtrando _MAX_ total de registos)",
                infoPostFix: "",
                thousands: ",",
                lengthMenu: "Mostrando _MENU_ registos por página.",
                loadingRecords: "Carregando...",
                processing: "Processando...",
                search: "Filtrar:",
                zeroRecords: "Nenhum registro encontrado.",
                paginate: {
                    first: "Primeira",
                    last: "Última",
                    next: "Seguinte",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending: ": ative para classificar a coluna em ordem crescente.",
                    sortDescending: ": ative para classificar a coluna em ordem decrescente."
                }
            }
        });
    })
</script>