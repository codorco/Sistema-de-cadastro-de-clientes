<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-8 col-10">
            <div class="card p-4">

                <h1 class="text-center text-info mb-3"><i class="fa-regular fa-circle-question"></i></h1>

                <p class="text-center mb-3">Deseja recuperar o agente?</p>
                
                <h4 class="mb-3 text-center"><strong><?= $agent->name ?></strong></h4>

                <p class="text-center">Total clientes: <strong><?= $agent->total_clients ?></strong></p>

                <div class="text-center my-3">
                    <a href="?ct=admin&mt=agents_management" class="btn btn-secondary px-4"><i class="fa-solid fa-xmark me-2"></i>Não</a>
                    <a href="?ct=admin&mt=recover_agent_confirm&id=<?= aes_encrypt($agent->id) ?>" class="btn btn-secondary px-4"><i class="fa-solid fa-check me-2"></i>Sim</a>
                </div>
                
            </div>
        </div>
    </div>
</div>