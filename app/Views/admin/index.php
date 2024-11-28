<h3>Admin MDS <?= get_user_data()['client_name']?></h3>
<div class="board-tables">
    <table class="styled-table board">
        <thead>
            <tr>
                <th>User</th>
                <th>User active</th>
            </tr>
        </thead>
        <tbody>
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
    <table class="styled-table board">
        <thead>
            <tr>
                <th>Info docusign</th>
                <th>Info</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Account name</td>
                <td><?= $docusign['name']?></td>
            </tr>
            <tr>
                <td>Plan name</td>
                <td><?= $docusign['plan_name']?></td>
            </tr>
            <tr>
                <td>Billing period end date</td>
                <td><?php $date= new DateTime($docusign['billing_period_end_date']); echo $date->format('d-m-Y H:i:s')?></td>
            </tr>
            <tr>
                <td>Billing period envelopes allowed</td>
                <td><?= $docusign['billing_period_envelopes_allowed']?></td>
            </tr>
            <tr>
                <td>Billing period envelopes sent</td>
                <td><?= $docusign['billing_period_envelopes_sent']?></td>
            </tr>
            <tr>
                <td>Billing period envelopes available</td>
                <td><?= $docusign['billing_period_envelopes_available']?></td>
            </tr>
        </tbody>
    </table>
</div>