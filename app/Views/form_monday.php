<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to MD (Integration Monday & Docusign)</h1>
        <div class="images">
            <img src="src/monday.svg" alt="monday">
            <span>+</span>
            <img src="src/docusign.png" alt="">
        </div>
        <span class="help">* To continue you must know the data that appear in the following form (these are necessary for any change in the integration).</span>
        <?php
            if (is_error_message()) {?>
                <span class="error help"><?= error_message()?></span>
            <?php }
        ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="">UserId - Monday</label>
                <input name="user_id" type="text">
            </div>
            <div class="form-group">
                <label for="">ApiKey - Monday</label>
                <input name="api_key" type="password" type="text">
            </div>
            <div class="submit-btn">
                <button type="submit">Continue</button>
            </div>
        </form>
    </div>
    <div class="container-help">
    <a href="documentation/monday" target="_blank">
        <div class="help-btn">
            ?
        </div>
    </a>
    </div>
</body>
</html>