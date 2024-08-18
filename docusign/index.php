<?php
require_once('DocClass.php');

$doc_class  = new DocClass();
$doc_class->validateRequest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docusign Integracion</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Config data integration Docusign</h1>
        <div class="images">
            <img src="../src/docusign.png" alt="">
        </div>
        <span class="help" style="Color:red">* The data entered below must be from the production environment, do not enter test server data, if you do not have this data, please contact support.</span>
        <form action="" method="post">
            <div class="form-group">
                <label for="">Server Docusign</label>
                <select name="" id="">
                    <option value="">DEMO</option>
                    <option value="">NA1</option>
                    <option value="">NA2</option>
                    <option value="">NA3</option>
                    <option value="">NA4</option>
                    <option value="">CA</option>
                    <option value="">AU</option>
                    <option value="">EU</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">ClientId</label>
                <input type="text" id="claveIntegracion" placeholder="xxxxxxxx-xxxx-xxxx-xxxxxxxxxxxxxxxxx" maxlength="36">
            </div>
            <div class="form-group">
                <label for="">UserId</label>
                <input type="text" id="uuid-input" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" maxlength="36">
            </div>
            <div class="form-group">
                <label for="">PrivateKey</label>
                <textarea name="" id="textarea"></textarea>
            </div>
            <div class="submit-btn">
                <button type="submit">Continue</button>
            </div>
        </form>
    </div>
    <a href="../documentation/docusign" target="_blank">
        <div class="help-btn">
            ?
        </div>
    </a>
    <script>
        const textarea = document.getElementById('textarea');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        textarea.dispatchEvent(new Event('input'));


        
        const input = document.getElementById('claveIntegracion');

        input.addEventListener('input', function(e) {

        let valor = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
        let formattedValue = '';

        if (valor.length > 0) formattedValue += valor.substring(0, 8);
        if (valor.length > 8) formattedValue += '-' + valor.substring(8, 12);
        if (valor.length > 12) formattedValue += '-' + valor.substring(12, 16);
        if (valor.length > 16) formattedValue += '-' + valor.substring(16, 20);
        if (valor.length > 20) formattedValue += '-' + valor.substring(20, 32);

        e.target.value = formattedValue;
        });

        document.getElementById('uuid-input').addEventListener('input', function(e) {
            let input = e.target.value;
            
            input = input.replace(/[^a-zA-Z0-9]/g, '');
            input = input.replace(/-/g, '');
            

            if (input.length > 8) {
                input = input.slice(0, 8) + '-' + input.slice(8);
            }
            if (input.length > 13) {
                input = input.slice(0, 13) + '-' + input.slice(13);
            }
            if (input.length > 18) {
                input = input.slice(0, 18) + '-' + input.slice(18);
            }
            if (input.length > 23) {
                input = input.slice(0, 23) + '-' + input.slice(23, 36);
            }
            
            
            e.target.value = input;
        });
    </script>   
</body>
</html>