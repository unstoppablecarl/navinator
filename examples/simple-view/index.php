<?php
require_once 'code.php';
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Simple View</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="main.css">
    </head>
    <body>
        <div class="nav">
            <?php renderSimpleNav($templateData); ?>
        </div>
        <div class="breadcrumb">
            <ul>
                <?php renderSimpleBreadcrumb($breadcrumbTemplateData) ?>
            </ul>
        </div>

    </body>
</html>
