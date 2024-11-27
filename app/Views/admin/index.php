<h3>Admin MDS <?= get_user_data()['client_name']?></h3>
<table class="styled-table users">
    <thead>
        <tr>
            <th>User</th>
            <th>User active</th>
        </tr>
    </thead>
    <tbody id="bodyTable">
        <?php foreach ($users as $user) {?>
            <tr>
                <td><?= $user['name']?></td>
                <td>
                    <a monday-id="<?= $user['id']?>" href="#"><?= $user['active']?'Active':'Unactive'?></a>
                </td>
            </tr>
        <?php }?>
    </tbody>
</table>