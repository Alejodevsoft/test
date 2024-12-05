var usersMain   = document.querySelectorAll('.user-main');
usersMain.forEach(element => {
    element.addEventListener('click',(e)=>{
        e.preventDefault();
        element.setAttribute('disabled','');
        setStatus(element);
        const formData = new FormData();
        formData.append('monday_id', element.getAttribute('monday-id'));
        formData.append('active', element.classList.contains('actived'));
        fetch(getUrl()+'admin/set-user-active',{
            method:'POST',
            body:formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            element.removeAttribute('disabled');
            if (!data.success) {
                setStatus(element);
            }
        }).catch(error =>{
            element.removeAttribute('disabled');
            setStatus(element);
        });
    })
});

function setStatus(element){
    element.classList.toggle('actived');
    element.classList.toggle('unactived');
    if (element.textContent === 'Actived') {
        element.textContent = 'Unactived';
    } else {
        element.textContent = 'Actived';
    }
}