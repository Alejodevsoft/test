<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="container">
        <h1>Welcome to MDS (Integration monday & Docusign)</h1>
        <div class="images">
            <img src="src/mds_white.png" alt="">
        </div>
        <?php if(is_error_message()){?>
            <span class="error-message"><?= error_message()?></span>
        <?php }?>
        <span class="help">* To continue you must know the data that appear in the following form (these are necessary for any change in the integration).</span>
        <form action="" method="post">
            <div class="form-group">
                <label for="">UserId (Admin) - monday</label>
                <input name="user_id" type="text" placeholder="UserId (Admin) - monday">
            </div>
            <div class="form-group">
                <label for="">ApiKey - monday</label>
                <input name="api_key" type="password" type="text" placeholder="ApiKey - monday">
            </div>
            <div class="submit-btn">
                <button type="submit">Continue</button>
            </div>
            <div class="active-mds">
                <a href="<?= base_url()?>" type="submit">Go back</a>
            </div>
        </form>
    </div>
</body>
</html>