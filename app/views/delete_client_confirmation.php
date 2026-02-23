<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-sm-8 col-10">
            <div class="card p-4">

                <h1 class="text-center text-warning mb-4"><i class="fa-solid fa-triangle-exclamation"></i></h1>

                <p class="text-center mb-4">Deseja eliminar o cliente?</p>
                
                <h4 class="mb-4 text-center"><strong><?= $client->name ?></strong></h4>
                
                <div class="text-center mb-4">
                    <span><i class="fa-solid fa-at me-2"></i>Email: <strong><?= $client->email ?></strong></span>
                    <span class="mx-5"></span>
                    <span><i class="fa-solid fa-phone me-2"></i>Telefone: <strong><?= $client->phone ?></strong></span>
                </div>

                <div class="text-center my-3">
                    <a href="?ct=agent&mt=my_clients" class="btn btn-outline-secondary px-5"><i class="fa-solid fa-xmark me-2"></i>Não</a>
                    <a href="?ct=agent&mt=delete_client_confirm&id=<?= aes_encrypt($client->id) ?>" class="btn btn-secondary px-5"><i class="fa-solid fa-check me-2"></i>Sim</a>
                </div>
                
            </div>
        </div>
    </div>
</div>