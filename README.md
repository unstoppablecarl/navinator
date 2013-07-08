Navinator
=========

A php package for fexibly managing navigation data for views.

Navinator is a light weight navigation helper providing simple collection and node classes designed to allow you to generate navigation tree data without complication.

Design Goals
-----

*  Add nodes to a collection tree without complication
 *  Add nodes in any order
 *  Parents do not have to be added before children
 *  Nodes do not need to know anything about children or parents
*  Use a simple path to describe parent child relationships
*  Act as a helper that could be used for any view
 *  Generate data ready to be used by a view requiring minimal logic and  function calls
 *  Let the view handle the html


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
  $node = new Node('my-favorite-sites/goolge');
  $node->url = 'http://google.com';
  $node->display_name = 'Google Search';
  $collection->addNode($node);
  
  $node = new Node('my-favorite-sites');
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

```

License
-

MIT
