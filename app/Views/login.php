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
        <h1>Welcome to MDS (Integration Monday & Docusign)</h1>
        <div class="images">
            <img src="src/mds_white.png" alt="">
        </div>
        <?php if(is_error_message()){?>
            <span class="error-message"><?= error_message()?></span>
        <?php }?>
        <form action="" method="post">
            <div class="form-group">
                <label for="">UserId (Admin) - Monday</label>
                <input name="user_id" type="text" placeholder="UserId (Admin) - Monday">
            </div>
            <div class="form-group">
                <label for="">Password</label>
                <input name="password" type="password" type="text" placeholder="Password">
            </div>
            <div class="submit-btn">
                <button type="submit">Continue</button>
            </div>
            <div class="active-mds">
                <a href="<?= base_url()?>active" type="submit">Active mds</a>
            </div>
        </form>
    </div>
</body>
</html>