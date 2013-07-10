Navinator
=========

[![Build Status](https://travis-ci.org/unstoppablecarl/navinator.png)](https://travis-ci.org/unstoppablecarl/navinator)
[![Coverage Status](https://coveralls.io/repos/unstoppablecarl/navinator/badge.png?branch=master)](https://coveralls.io/r/unstoppablecarl/navinator?branch=master)

**Navinator** is a php package for fexibly managing navigation data for views.

Navinator is a light weight navigation tree helper providing simple collection and node classes designed to allow you to generate navigation tree data without complication.

## Installing

- Install [Composer](http://getcomposer.org)
- Add `unstoppablecarl/navinator` to your project's `composer.json` file:

```json
{
    "require": {
        "unstoppablecarl/navinator": "1.*"
    }
}
```

- Install/update

```bash
$ cd my_project
$ php composer.phar install
```

Look at the **example files** in `examples/` to see all the cool things you can do with Navinator.

Design Goals
-----

*  Add nodes to a collection tree without complication
 *  Add nodes in any order, parents do not have to be added before children
 *  Nodes do not need to know anything about children or parents
*  Use a simple directory-like path to describe the position of a node in the tree
*  Act as a helper that could be used for any view
 *  Generate data ready to be used by a view requiring minimal logic and  function calls
 *  Let the view handle the html

Node Path and Node Url
-----

Each node has a directory-like **path** that describes where the node is in the tree. In most cases the url of the nav node will match the path. By default slashes are added to the path and set as the url.

```php
    use \Navinator\Node;

    $path = 'articles';
    $node = new Node($path);
    $url = $node->url; // /articles/
    
    $path = 'articles/tags';
    $node = new Node($path);
    $url = $node->url; // /articles/tags/
    
    $path = 'articles/tags/news';
    $node = new Node($path);
    // set the url manually
    $node->url = 'http://www.theonion.com';
```

Usage
-----

1.  Create a collection
2.  Add nodes to the collection in any order
3.  Generate the data to be passed to your view.

The following example generates a simple navigation tree.

* my-favorite-sites
    - google search
        * maps
        * gmail
    - github
        * gist

```php
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
  // if $node->url is not set, the node path is used: 
  // same as: $node->url = '/my-favorite-sites/';
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

  print_r($templateData);

// output
// the following node array keys are replaced by [...] to make this example easier to read:
//  - template_data
//  - is_first_child
//  - is_last_child
//  - is_current_root
//  - is_current
//  - is_current_ancestor

//  Array
//(
//    [0] => Array
//        (
//            [url] => /my-favorite-sites/
//            [path] => my-favorite-sites
//            [display_name] => my-favorite-sites
//            [depth] => 1
//            [...],
//            [children] => Array
//                (
//                    [0] => Array
//                        (
//                            [url] => http://google.com
//                            [path] => my-favorite-sites/google
//                            [display_name] => Google Search
//                            [depth] => 2
//                            [...],
//                            [children] => Array
//                                (
//                                    [0] => Array
//                                        (
//                                            [url] => https://mail.google.com
//                                            [path] => my-favorite-sites/google/gmail
//                                            [display_name] => gmail
//                                            [depth] => 3
//                                            [...],
//                                            [children] => Array()
//                                            [display_order] => 1
//                                        )
//                                    [1] => Array
//                                        (
//                                            [url] => https://www.google.com/maps/
//                                            [path] => my-favorite-sites/google/maps
//                                            [display_name] => maps
//                                            [depth] => 3
//                                            [...],
//                                            [children] => Array()
//                                            [display_order] => 2
//                                        )
//                                )
//                            [display_order] => 1
//                        )
//                    [1] => Array
//                        (
//                            [url] => http://github.com
//                            [path] => my-favorite-sites/github
//                            [display_name] => Github
//                            [depth] => 2
//                            [...]
//                            [children] => Array
//                                (
//                                    [0] => Array
//                                        (
//                                            [url] => http://gist.github.com
//                                            [path] => my-favorite-sites/github/gist
//                                            [display_name] => gist
//                                            [depth] => 3
//                                            [...],
//                                            [children] => Array()
//                                            [display_order] => 1
//                                        )
//                                )
//                            [display_order] => 2
//                        )
//                )
//        )
//)
```

Views
-----

The output template data can be rendered with a very simple view function.

```php
function renderSimpleNav($nodes, $depth = 1){
    ?>
    <ul class="depth-<?= $depth ?>">
        <?php foreach($nodes as $node): 
            $isFirstChild = 
        ?>
            <li>
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
```

A more extensive example of this simple view is available in [examples/simple-view.php](examples/simple-view.php)

License
-

MIT
