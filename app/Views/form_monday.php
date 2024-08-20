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
            if (!empty($_SESSION['error'])) {?>
                <span class="error help"><?= $_SESSION['error']?></span>
            <?php }
            session_destroy();
        ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="">UserId - Monday</label>
                <input name="user_id" type="text" value="35497500">
            </div>
            <div class="form-group">
                <label for="">ApiKey - Monday</label>
                <input name="api_key" type="password" type="text" value="eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjM3NDc5NjAwMCwiYWFpIjoxMSwidWlkIjozNTQ5NzUwMCwiaWFkIjoiMjAyNC0wNi0yMFQxNzoxNDoyNS4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTAyMDk1OTMsInJnbiI6InVzZTEifQ.Kap9bbJUy0P7BqvYZsgXk8cywgocFYYfyY2yVZZzLao">
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