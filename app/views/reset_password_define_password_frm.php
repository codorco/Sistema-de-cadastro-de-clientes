<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6 col-sm-8 col-10">
            <div class="card p-4">

                <div class="d-flex align-items-center justify-content-center my-4">
                    <img src="assets/images/logo_64.png" class="img-fluid me-3">
                    <h2><strong><?= APP_NAME ?></strong></h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-8">

                        <form action="?ct=main&mt=reset_define_password_submit&id=<?= aes_encrypt($id) ?>" method="post" novalidate>

                            <p class="mb-3">Introduza os dados da <strong>nova password</strong>.</p>

                            <div class="mb-3">
                                <label for="text_new_password" class="form-label">Nova password</label>
                                <input type="password" name="text_new_password" id="text_new_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="text_repeat_new_password" class="form-label">Repetir a nova password</label>
                                <input type="password" name="text_repeat_new_password" id="text_repeat_new_password" class="form-control" required>
                            </div>

                            <div class="mb-3 text-center">
                                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-key me-2"></i>Definir password</button>
                            </div>

                            <?php if (!empty($validation_error)) : ?>
                                <div class="alert alert-danger p-2 text-center">
                                    <?= $validation_error ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($server_error)) : ?>
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