<?php

require __DIR__ . '/../src/Navinator/Collection.php';
require __DIR__ . '/../src/Navinator/Node.php';
require __DIR__ . '/../src/Navinator/Exception.php';

function renderSimpleNav($nodes, $depth = 1){
    ?>
    <ul class="depth-<?= $depth ?>">
        <?php foreach($nodes as $node):

            $isFirstChild = $node['is_first_child'];
            $isLastChild = $node['is_last_child'];

            $cssClasses = array('item');

            if($node['is_first_child']){
                $cssClasses[] = 'first-child';
            }

            if($node['is_last_child']){
                $cssClasses[] = 'last-child';
            }

            if($node['is_current'] || $node['is_current_root'] || $node['is_current_ancestor']){
                $cssClasses[] = 'active';
            }


            ?>
            <li class=" <?= implode(' ', $cssClasses) ?>">
                <a href="<?= $node['url'] ?>"><?= $node['display_name'] ?></a>
                <?php
                if($node['children']){
                    renderSimpleNav($node['children'], $depth + 1);
                }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

use \Navinator\Collection;
use \Navinator\Node;

$collection = new Collection();

// create a node passing the node's path
// note: a node with path 'my-favorite-sites' has not been added to the collection yet and does not need to be
$node = new Node('my-favorite-sites/google');
$node->url = 'http://google.com';
$node->display_name = 'Google Search';
$collection->addNode($node);

$node = new Node('my-favorite-sites');
$node->display_name = 'My Favorite Sites';
// if $node->url is not set, the node path is used: same as $node->url = '/my-favorite-sites/';
$collection->addNode($node);

// create a node object from an array
$node = new Node(array(
    'path'         => 'my-favorite-sites/github',
    'url'          => 'http://github.com',
    'display_name' => 'Github'
    ));
$collection->addNode($node);

$node = new Node(array(
    'path' => 'my-favorite-sites/github/gist',
    'url'  => 'http://gist.github.com'
    ));
// if $node->display_name (array key or property) is not set, the last segment of the the node path is used: same as $node->display_name = 'gist';
$collection->addNode($node);

$node = new Node(array(
    'path' => 'my-favorite-sites/google/maps',
    'url'  => 'https://www.google.com/maps/'
    ));
// the display order of a node in relation to it's siblings can be set as the optional second param of $collection->addNode()
$collection->addNode($node, 2);

$node = new Node(array(
    'path' => 'my-favorite-sites/google/gmail',
    'url'  => 'https://mail.google.com'
    ));
$collection->addNode($node, 1);

$templateData = $collection->prepareForNavTemplate();

?>
<style>

    body, * {
        font-family: Arial, sans-serif;
        font-size: 13px;
    }


    ul, li {
        list-style:none;
        margin:0;
        padding:0;
    }

    .nav {
        float:left;
        padding: 5px;
        background: #eee;
        border: 1px solid #ddd;
    }

    .nav ul.depth-1 {
        padding-left: 0;
    }

    .nav li {
        padding:0;
        margin:0;
    }

    .nav li a,
    .nav li a:visited {
        display:block;
        background: #eee;
        color: #333;
        text-decoration:none;
        padding: 3px 20px;
        line-height: 1.5;
    }

    .nav li a:hover {
        background: #ddd;
    }

    .nav li.last-child a{
    }

    .nav .depth-1 li a{
        font-weight: bold;
        font-size: 15px;
        padding-left: 0px;
    }
    .nav .depth-2 li a{
        font-weight: bold;
        font-size: 13px;
        padding-left: 20px;
    }
    .nav .depth-3 li a{
        font-weight: normal;
        padding-left: 40px;
    }







</style>


<div class="nav">
<?
renderSimpleNav($templateData);
?>
</div>