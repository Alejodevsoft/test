<div class="login-body">
    <div class="container">
        <h1>Update Docusign Data</h1>
        <?php if(is_error_message()){?>
            <span class="error-message"><?= error_message()?></span>
        <?php }?>
        <span class="help" style="Color:red">* The data entered below must be from the production environment, do not enter test server data, if you do not have this data, please contact support.</span>
        <form action="<?= base_url()?>admin/update-docusign" method="post">
            <input type="hidden" value="<?php echo $monday_id?>" name="monday_id">
            <div class="form-group">
                <label for="server_type">Server Docusign</label>
                <select name="server_type" id="server_type">
                    <option value="0">DEMO</option>
                    <option value="1">NA1</option>
                    <option value="2">NA2</option>
                    <option value="3">NA3</option>
                    <option value="4">NA4</option>
                    <option value="5">CA</option>
                    <option value="6">AU</option>
                    <option value="7">EU</option>
                </select>
            </div>
            <div class="form-group">
                <label for="claveIntegracion">ClientId</label>
                <input type="text" id="claveIntegracion" name="client_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxxxxxxxxxxxxxxx" maxlength="36" required>
            </div>
            <div class="form-group">
                <label for="uuid-input">UserId</label>
                <input type="text" id="uuid-input" name="user_id" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" maxlength="36" required>
            </div>
            <div class="form-group">
                <label for="private_key">PrivateKey</label>
                <textarea name="private_key" id="private_key" required></textarea>
            </div>
            <p class="form-group">⚠️ You need to have "https://monday.com" set in your RedirectURL</p>
            <div class="submit-btn">
                <button type="submit">Continue</button>
            </div>
        </form>
    </div>
    <script>
        const textarea = document.getElementById('private_key');
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
        <?php if (is_reverify()) {
            
        }?>
    </script>
</div>