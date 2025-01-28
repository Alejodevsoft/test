<div class="login-body">
    <div class="container">
        <h2 style="margin-top: 30px;margin-bottom: 30px;">Change your access password</h2>
        <span id="error_message"></span>
        <form action="" method="post" id="change_password">
            <div class="form-group">
                <label for="old_password">Old password</label>
                <input name="old_password" type="password" placeholder="Old password" required>
            </div>
            <div class="form-group">
                <label for="">New password</label>
                <input name="new_password" type="password" placeholder="New password" required>
            </div>
            <div class="form-group">
                <label for="">Confirm new password</label>
                <input name="new_password2" type="password" placeholder="Confirm new password" required>
            </div>
            <div class="submit-btn">
                <button type="submit">Save new password</button>
            </div>
        </form>
    </div>
</div>
<script>
    const form  = document.getElementById('change_password');
    form.addEventListener('submit',async (e)=>{
        event.preventDefault();
        
        const formData = new FormData(form);
        const messageHtml   = document.getElementById('error_message');
        
        try {
            const response = await fetch(getUrl()+'admin/update-password', {
                method: "POST",
                body: formData,
            });
            
            const result = await response.json();
            if (result.success) {
                messageHtml.classList.remove('error-message');
                messageHtml.classList.add('success-message');
                messageHtml.innerText = "Succcess: " + result.message;
                form.reset();
            } else {
                messageHtml.classList.remove('success-message');
                messageHtml.classList.add('error-message');
                messageHtml.innerText = "Error: " + result.message;
            }
        } catch (error) {
            messageHtml.classList.remove('success-message');
            messageHtml.classList.add('error-message');
            messageHtml.innerText = "Error de conexión: " + error.message;
        }
        setTimeout(() => {
            messageHtml.innerText = '';
        }, 3000);
    });
</script>