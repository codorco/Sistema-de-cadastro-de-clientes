<div class="container-fluid mt-5 mb-5">
    <div class="row justify-content-center pb-5">
        <div class="col-lg-8 col-md-10">
            <div class="card p-4">

                <div class="row justify-content-center">
                    <div class="col-10">

                        <h4 class="mb-4"><strong>Carregar ficheiro de clientes</strong></h4>

                        <p class="text-center">Carregar ficheiro em formato XLSX ou CSV. Se não tem o template do ficheiro, faça download <a href="assets/file_template/template.xlsx">AQUI</a></p>

                        <hr>

                        <form action="?ct=agent&mt=upload_file_submit" method="post" enctype="multipart/form-data">

                            <div class="mb-4">
                                <label for="clients_file" class="form-label">Ficheiro de clientes</label>
                                <input type="file" name="clients_file" id="clients_file" value="" class="form-control" required>
                            </div>

                            <div class="mb-4 text-center">
                                <a href="?ct=agent&mt=my_clients" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-upload me-2"></i>Carregar</button>
                            </div>

                            <?php if (isset($server_error)) : ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <?= $server_error ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($report)) : ?>
                                <ul class="alert alert-success ps-5">
                                    <li><?= 'Ficheiro: ' .$report['filename'] ?></li>
                                    <li><?= 'Total: ' .$report['total'] ?></li>
                                    <li><?= 'Carregados: ' .$report['total_carregados'] ?></li>
                                    <li><?= 'Não carregados: ' .$report['total_nao_carregados'] ?></li>
                                </ul>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>