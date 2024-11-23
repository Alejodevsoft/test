var select = new SlimSelect({
    select: '#boardSelect'
});
const loader = document.getElementById('loader');
const errordiv = document.getElementById('error');

document.getElementById('boardSelect').addEventListener("change", (event) => {
    document.getElementById('bodyTable').innerHTML = "";
    document.querySelector('.table').classList.add('hidden');
    loader.classList.remove('hidden');
    errordiv.classList.add('hidden');
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
        var dataMonday = data;
        if (dataMonday.success) {
            var html = "";

            fetch(getUrl()+"admin/list-templates", {
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
                var option_html = "";
                data.data.forEach(template => {
                    if (template.template_name) {
                        option_html += `<option value="${template.template_id}">${template.template_name}</option>`
                    } else {
                        option_html += `<option value="${template.template_id}">${template.template_id}</option>`
                    }
                });
                dataMonday.data.forEach(contract => {
                    if (contract.column_values[0].text) {
                        html += `
                            <tr>
                                <td>${contract.name}</td>
                                <td class="color_table">Template Assigned ✅</td>
                            </tr>
                        `
                    } else {
                        html += `
                            <tr>
                                <td>${contract.name}</td>
                                <td>
                                    <select id="select-${contract.id}" id-contract="${contract.id}">
                                        <option data_placeholder="true" disabled selected style="display:none;">Select your template</option>
                                        ${option_html}
                                    </select>
                                </td>
                            </tr>
                        `
                    }
                });
                document.getElementById('bodyTable').innerHTML = html;
                dataMonday.data.forEach(contract => {
                    const slimSelect = new SlimSelect({
                        select: '#select-'+contract.id,
                        events: {
                            afterChange: (newVal) => {
                              slimSelect.disable();
                              assignTemplate(slimSelect.selectEl.getAttribute("id-contract"), newVal[0].value);
                            }
                          }
                    });
                });
                document.querySelector('.table').classList.remove('hidden');
                loader.classList.add('hidden');
            })
            .catch(error => {
                errordiv.innerHTML = error;
                errordiv.classList.remove('hidden');
                loader.classList.add('hidden');
            });

        }else{

            errordiv.innerHTML = data.message;
            errordiv.classList.remove('hidden');
            loader.classList.add('hidden');

        }
    })
    .catch(error => {
        errordiv.innerHTML = error;
        errordiv.classList.remove('hidden');
        loader.classList.add('hidden');
    });
});

function assignTemplate(idContratc,idTemplate) {
    // alert("se esta creando el contrato "+idContratc+" con la identificacion de template "+idTemplate);
}