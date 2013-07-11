<?php
require_once __DIR__ . '/../bootstrap.php';

function renderSimpleNav($nodes, $depth = 1){
    ?>
    <ul class="depth-<?= $depth ?>">
        <?php foreach($nodes as $node):

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
            <li class="<?= implode(' ', $cssClasses) ?>">
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

function renderSimpleBreadcrumb($nodes){
    ?>
        <?php
        $count = count($nodes);
        $i = 1;
        foreach($nodes as $node): ?>
            <li>
                <a href="<?= $node['url'] ?>"><?= $node['display_name'] ?></a>

            <?php if($i !== $count): ?>
                /
            <?php endif; ?>
            </li>
        <?php
        $i++;
        endforeach; ?>
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


$node = new Node('my-favorite-sites/programming');
$collection->addNode($node);

$node = new Node('my-favorite-sites/programming/js');
$collection->addNode($node);

$node = new Node('my-favorite-sites/programming/css');
$collection->addNode($node);

$node = new Node('my-favorite-sites/programming/php');
$collection->addNode($node);


// manually set the current url or fallback to $_SERVER['REQUEST_URI'];
$currentUrl = '/my-favorite-sites/programming/php/';

$templateData = $collection->prepareForNavTemplate($currentUrl);
$breadcrumbTemplateData = $collection->prepareForBreadcrumbTemplate($currentUrl);


