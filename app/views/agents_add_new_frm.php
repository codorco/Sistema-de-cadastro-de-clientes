<div class="container-fluid mt-5 mb-5">
    <div class="row justify-content-center pb-5">
        <div class="col-lg-4 col-md-10">
            <div class="card p-4">

                <div class="row justify-content-center">
                    <div class="col-10">

                        <h4><strong>Adicionar novo agente</strong></h4>

                        <hr>

                        <form action="?ct=admin&mt=new_agent_submit" method="post" novalidate>

                            <div class="mb-3">
                                <label for="text_name" class="form-label">Nome do agente</label>
                                <input type="email" name="text_name" id="text_name" value="" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="select_profile" class="form-label">Perfil</label>
                                <select name="select_profile" id="select_profile" class="form-control" required>
                                    <option value="admin">Administrador</option>
                                    <option value="agent">Agente</option>
                                </select>
                            </div>
                            
                            <div class="mb-3 text-center">
                                <a href="#" class="btn btn-secondary"><i class="fa-solid fa-xmark me-2"></i>Cancelar</a>
                                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-user-plus me-2"></i>Criar agente</button>
                            </div>

                            <?php if(!empty($validation_error)): ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <?= $validation_error ?>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($server_error)): ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <?= $server_error ?>
                                </div>
                            <?php endif; ?>
                            
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>