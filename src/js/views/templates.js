var select = new SlimSelect({
    select: '#boardSelect'
})
const loader = document.getElementById('loader');
const error = document.getElementById('error');

document.getElementById('boardSelect').addEventListener("change", (event) => {
    loader.classList.remove('hidden');
    error.classList.add('hidden');
    // alert(select.getSelected());
    fetch(getUrl()+"admin/contracts?board_id="+select.getSelected(), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // select.disable();
            var html = "";
            data.data.forEach(contract => {
                if (contract.column_values[0].text) {
                    html += `
                        <tr>
                            <td>${contract.name}</td>
                            <td>existe</td>
                        </tr>
                    `
                } else {
                    html += `
                        <tr>
                            <td>${contract.name}</td>
                            <td>no existe</td>
                        </tr>
                    `
                }
            });

            document.getElementById('bodyTable').innerHTML = html;
            document.querySelector('.table').classList.remove('hidden');

        }else{

            error.innerHTML = data.message;
            error.classList.remove('hidden');

        }
        loader.classList.add('hidden');
    })
    .catch(error => {
        error.innerHTML = error;
        error.classList.remove('hidden');
        loader.classList.add('hidden');
    });
});