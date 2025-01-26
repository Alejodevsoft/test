<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title?></title>
    <link rel="stylesheet" href="<?= base_url()?>src/css/libs/slimselect.css">
    <link rel="stylesheet" href="<?= base_url()?>src/css/general.css">
    <link rel="stylesheet" href="<?= base_url()?>style.css">
</head>
<body>
    <div class="nav">
        <div class="logo">
            <a>
                <img src="<?= base_url()?>src/images/logo horizontal transparente blanco.png" alt="">
            </a>
        </div>
        <nav>
            <ul>
                <li <?= ($select_aside == 10)?'class="active"':"" ?>>
                    <a href="<?= base_url()?>admin">Dashboard</a>
                </li>
                <li <?= ($select_aside == 20)?'class="active"':"" ?>>
                    <a href="<?= base_url()?>admin/templates">Templates Config</a>
                </li>
                <li <?= ($select_aside == 30)?'class="active"':"" ?>>
                    <a href="<?= base_url()?>admin/docusign">Docusign Config</a>
                </li>
                <li <?= ($select_aside == 40)?'class="active"':"" ?>>
                    <a href="<?= base_url()?>admin/change-password">Change Password</a>
                </li>
            </ul>
        </nav>
        <div class="name">
            <span><?= get_user_data()['client_name']?></span>
            <a class="logout" href="<?= base_url().'logout'?>">Logout</a>
        </div>
    </div>
    <div class="body">