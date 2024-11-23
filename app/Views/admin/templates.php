<script src="<?= base_url()?>src/js/libs/slimselect.min.js"></script>

<div class="templates">
    <div class="title">
        <h1 >Select your board:</h1>
        <select name="" id="boardSelect">
            <option data_placeholder="true" disabled selected style="display:none;">Select your board</option>
            <?php foreach ($boards['data'] as $board) {
                if ($board->type == "board") { ?>
                    <option value="<?= $board->id ?>"><?= $board->name ?></option>
                <?php }
            } ?>
        </select>
    </div>
    <div id="loader" class="hidden container">
        <div class="loader"></div>
    </div>
    <div id="error" class="hidden"></div>
    <div class="hidden table">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Contracts</th>
                    <th>Docusign Template</th>
                </tr>
            </thead>
            <tbody id="bodyTable">
            </tbody>
        </table>
    </div>
</div>

<script src="<?= base_url()?>src/js/views/templates.js"></script>