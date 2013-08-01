<?php

namespace Navinator;

/**
 * Nav collection data object
 *
 * Manages a collection of nav nodes
 *
 * @license MIT
 *
 * @package Navinator
 * @link https://github.com/unstoppablecarl/navinator
 * @author Carl Olsen <unstoppablecarlolsen@gmail.com>
 */
class Collection implements \Countable, \ArrayAccess, \Iterator{

    /**
     * array of nodes indexed by nodePath
     * @var array
     */
    protected $nodes = array();

    /**
     * array of key value pairs storing this collections node display orders indexed by node path
     * @var array
     */
    protected $node_display_order = array();

    /**
     * creates a node collection from an array
     * @param array $array array of node array data
     * @param bool $autoSetDisplayOrder If true and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available number of those siblings.
     * @return \Navinator\Collection
     * @throws \Exception if an array key required to create a node is missing
     */
    static public function buildFromArray($array, $autoSetDisplayOrder = true){
        $collection = new \Navinator\Collection();
        foreach($array as $arrayItem){
            $displayOrder = null;
            if(isset($arrayItem['display_order'])){
                $displayOrder = $arrayItem['display_order'];
            }
            $node = new \Navinator\Node($arrayItem);
            $collection->addNode($node, $displayOrder, $autoSetDisplayOrder);
        }
        return $collection;
    }

    /**
     * Add a Drive\Nav\Node object to this collection
     * @param \Navinator\Node $node The node object to add to this collection
     * @param int $displayOrderOverride The display order of the node in relation to it's siblings, overrrides the $node->display_order that would be used instead
     * @param bool $autoSetDisplayOrder If true and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available number of those siblings.
     * @throws \Exception If a node with the new node's path is already set
     */
    public function addNode(\Navinator\Node $node, $displayOrderOverride = null, $autoSetDisplayOrder = true){
        $nodePath = $node->getPath();
        if($this->hasNode($nodePath)){
            throw new \Navinator\Exception(sprintf('A Node Object with the nodePath "%s" is already assigned to this %s use addNodeIfNotExists(), removeNode() or setNode() to change it.', $nodePath, get_class($this)));
        }
        $this->nodes[$nodePath] = $node;
        $this->setNodeDisplayOrder($node, $displayOrderOverride, $autoSetDisplayOrder);
    }

    /**
     * Add a Drive\Nav\Node object to this collection
     * @param \Navinator\Node $node The node object to add to this collection
     * @param int $displayOrderOverride The display order of the node in relation to it's siblings, overrrides the $node->display_order that would be used instead
     * @param bool $autoSetDisplayOrder If true and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available number of those siblings.
     */
    public function addNodeIfNotExists(\Navinator\Node $node, $displayOrderOverride = null, $autoSetDisplayOrder = true){
        $nodePath = $node->getPath();
        if(!$this->hasNode($nodePath)){
            $this->nodes[$nodePath] = $node;
            $this->setNodeDisplayOrder($node, $displayOrderOverride, $autoSetDisplayOrder);
        }
    }

    /**
     * Setter for specific node, overwrites existing node path
     * @param \Drive\Data\Nav\Node $node the node object to set
     * @param int $displayOrderOverride The display order of the node in relation to it's siblings, overrrides the $node->display_order that would be used instead
     * @param bool $autoSetDisplayOrder If true and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available display order number of those siblings.
     */
    public function setNode(\Navinator\Node $node, $displayOrderOverride = null, $autoSetDisplayOrder = true){
        $this->nodes[$node->getPath()] = $node;
        if($displayOrderOverride){
            $displayOrder = $displayOrderOverride;
        } else{
            $displayOrder = $node->display_order;
        }
        $this->setNodeDisplayOrder($node, $displayOrder, $autoSetDisplayOrder);
    }

    /**
     * Set the display order for a node in this collection, overriding $node->display_order
     * @param string|\Navinator\Node $obj Node object or path string
     * @param int $displayOrderOverride The display order of the node in relation to it's siblings, overrrides the $node->display_order that would be used instead
     * @param bool $autoSet If true and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available number of those siblings.
     */
    public function setNodeDisplayOrder($obj, $displayOrderOverride = null, $autoSet = true){
        $node = $this->getNodeFromVar($obj);

        if(!$this->hasNode($node)){
            throw new \Navinator\Exception(sprintf('Attempting to set the collection display order override of a Node Object with the nodePath "%s" that was not found in collection %s.', $node->getPath(), get_class($this)));
        }

        if($displayOrderOverride){
            $displayOrder = $displayOrderOverride;
        } else{
            $displayOrder = $node->display_order;
        }

        if($autoSet){
            $this->autoSetNodeDisplayOrder($node, $displayOrder);
        } else{
            $path = $node->getPath();
            $this->node_display_order[$path] = $displayOrder;
        }
    }

    /**
     * If node does not have a display order or a sibling node has the same display order value the display order is set to the next available number.
     * @param \Navinator\Node $node the node to set the display order of
     * @param int $displayOrder The display order of the node in relation to it's siblings
     */
    protected function autoSetNodeDisplayOrder(\Navinator\Node $node, $displayOrder = null){
        // keep the same by default
        $newNodeDisplayOrder = $displayOrder;

        $siblings = $node->getSiblings($this);
        if($displayOrder == null){
            if(empty($siblings)){
                $newNodeDisplayOrder = 1;
            } else{
                $newNodeDisplayOrder = max($this->getNodeDisplayOrders($siblings)) + 1;
            }
        } else{
            $siblingDisplayOrders = $this->getNodeDisplayOrders($siblings);
            // if the desired display order is already taken
            if(in_array($displayOrder, $siblingDisplayOrders)){
                //find first gap in sort order list after desired position
                $start = $displayOrder;
                sort($siblingDisplayOrders);
                if($displayOrder == 1 && count($siblings) == 1){
                    $newNodeDisplayOrder = 2;
                } else{
                    foreach($siblingDisplayOrders as $v){
                        if($v <= $displayOrder){
                            continue;
                        }

                        if($start + 1 != $v){
                            $newNodeDisplayOrder = $start + 1;
                            break;
                        }
                        $start = $v;
                    }
                }
            }
        }
        $this->node_display_order[$node->getPath()] = $newNodeDisplayOrder;
    }

    /**
     * Getter for all or specific Drive\Data\Nav\Node
     * @param string $nodePath unique identifier for a node object
     * @exception throws \Exception When A \Drive\Data\Nav\Node with the $nodePath is not found.
     * @return mixed Array|Navinator\Node  array of or single Drive\Admin\Nav\Item
     */
    public function getNode($nodePath = null){
        if($nodePath === null){
            return $this->nodes;
        }
        if(!$this->hasNode($nodePath)){
            throw new \Navinator\Exception(sprintf('A Node Object with the nodePath "%s" was not found in %s.', $nodePath, get_class($this)));
        }
        return $this->nodes[$nodePath];
    }

    /**
     * Retrieves a node from a path if $var is a string or returns $var if it is a node object
     * @param \Navinator\Node|string $var Node object or path string
     * @return \Navinator\Node
     */
    public function getNodeFromVar($var){
        if($var instanceof \Navinator\Node){
            return $var;
        }
        return $this->getNode($var);
    }

    /**
     * Retrieves a node path from a node object or string
     * @param \Navinator\Node|string $var Node object or path string
     * @return \Navinator\Node
     */
    public function getPathFromVar($var){
        if($var instanceof \Navinator\Node){
            return $var->getPath();
        }
        return $var;
    }

    /**
     * Removes a \Navinator\Node from this nav object
     * @param string|\Navinator\Node $obj Node object or path string
     */
    public function removeNode($obj){
        $path = $this->getPathFromVar($obj);
        unset($this->nodes[$path]);
        unset($this->node_display_order[$path]);
    }

    /**
     * Checks if node exitst in this nav object
     * @param string $obj Node object or path string
     * @return boolean
     */
    public function hasNode($obj){
        $path = $this->getPathFromVar($obj);
        return isset($this->nodes[$path]);
    }

    /**
     * Retrieves the nodes in this collection that have no parents ($node->getDepth() == 1)
     * @return type
     */
    public function getRootNodes(){
        $rootNodes = array();
        foreach($this->nodes as $path => $node){
            if($node->getDepth() == 1){
                $rootNodes[$path] = $node;
            }
        }
        return $rootNodes;
    }

    /**
     * Retrieves display order of a node
     * @param string|\Navinator\Node $obj Node object or path string
     * */
    public function getNodeDisplayOrder($obj){
        $path = $this->getPathFromVar($obj);
        return $this->node_display_order[$path];
    }

    /**
     * Retrieves display orders as array
     * @param array $nodes
     */
    public function getNodeDisplayOrders($nodes){
        $displayOrders = array();
        foreach($nodes as $node){
            $displayOrders[] = $this->node_display_order[$node->getPath()];
        }
        return $displayOrders;
    }

    /**
     * Retrieves the node with $node->url best matching the current url.
     * @param string $url The current url to match against, uses $_SERVER['REQUEST_URI'] if not set
     * @param bool $exactMatchOnly When true only a node with a url matching exactly will be returned. When false the node with the closest matchin url will be returned. (nodes with $node->current_only_on_exact_url_match = true will always only be matched on an exact match)
     */
    public function getNodeMatchingUrl($url = null, $exactMatchOnly = false){
        if($url === null && isset($_SERVER['REQUEST_URI'])){
            $url = $_SERVER['REQUEST_URI'];
        }
        $currentNode = null;
        foreach($this->nodes as $node){
            // exact match beats all others
            if($node->url == $url){
                $currentNode = $node;
                break;
            }

            // set current and find current root parent
            if(!$exactMatchOnly && !$node->current_only_on_exact_url_match && StringHelper::StrStartsWith($node->url, $url)){
                if(!empty($currentNode)){
                    $currentNodeSegments = $currentNode->getPathArray();
                    $nodeSegments = $node->getPathArray();
                    if(count($nodeSegments) < count($currentNodeSegments)){
                        continue;
                    }
                }
                $currentNode = $node;
            }
        }
        return $currentNode;
    }

    /**
     * Sort an array of nodes by their display order
     * @param array $array Array of nodes
     * @return array Sorted Array of nodes
     */
    public function sortNodeArray($array){
        $nodes = array();
        foreach($array as $node){
            $nodes[] = array(
                'node'          => $node,
                'display_order' => $this->getNodeDisplayOrder($node),
            );
        }

        usort($nodes, function($a, $b){
                if($a['display_order'] == $b['display_order']){
                    return 0;
                }
                return ($a['display_order'] < $b['display_order']) ? -1 : 1;
            });

        $output = array();
        foreach($nodes as $item){
            $output[] = $item['node'];
        }

        return $output;
    }

    /**
     *  Retrieves node data ready to be used by view
     *
     * The $filter callback method signature should include the follow parameters:
     *
     *  - **`$node`**:       The node to be filtered
     *  - **`$nodeArrayData`**: Node array data to be returned for template
     *  - **`$collection`**: this collection object
     *  - **`$currentNode`**: the currently navigated to node
     *  - **`$currentNodeAncestorPaths`**: the currently navigated to node ancestor path
     *
     * @param string $currentUrl The current url, used to set the current node, $_SERVER['REQUEST_URI'] is used by default
     * @param \Navinator\Node|string $currentNode The node to treat as the currently navigated to node, determines current node ancestors. If not set the best matching node will be used
     * @param callback $filter Function to filter nodes - see the method description for details about the method signature
     * @return array
     */
    public function prepareForNavTemplate($currentUrl = null, $currentNode = null, $filter = null){
        $this->validateNodes();
        if($currentUrl === null && isset($_SERVER['REQUEST_URI'])){
            $currentUrl = $_SERVER['REQUEST_URI'];
        }

        if($currentNode === null){
            $currentNode = $this->getNodeMatchingUrl($currentUrl);
        } else{
            $currentNode = $this->getNodeFromVar($currentNode);
        }

        $currentNodeAncestorPaths = array();
        if($currentNode){
            $currentNodeAncestorPaths = $currentNode->getAncestorPaths($this);
        }

        $rootNodes = $this->getRootNodes();
        $sortedRootNodes = $this->sortNodeArray($rootNodes);
        $output = array();

        foreach($sortedRootNodes as $node){
            $output[] = $node->prepareForTemplate($this, $sortedRootNodes, $currentNode, $currentNodeAncestorPaths, $sortedRootNodes, $filter);
        }

        return $output;
    }

    /**
     * Prepares collection node data ready to be used by view
     * @param string $currentUrl The current url, used to set the current node, $_SERVER['REQUEST_URI'] is used by default
     * @param \Navinator\Node|string $currentNode The node to treat as the currently navigated to node, determines current node ancestors. If not set the best matching node will be used
     * @return type
     */
    public function prepareForBreadcrumbTemplate($currentUrl = null, $currentNode = null){

        $filter = function($node, $nodeTemplateData, $collection, $sortedSiblings, $currentNode, $currentNodeAncestorPaths){
                return $nodeTemplateData['is_current'] || $nodeTemplateData['is_current_root'] || $nodeTemplateData['is_current_ancestor'];
            };
        $this->validateNodes();
        if($currentUrl === null){
            $currentUrl = $_SERVER['REQUEST_URI'];
        }

        if($currentNode === null){
            $currentNode = $this->getNodeMatchingUrl($currentUrl);
        } else{
            $currentNode = $this->getNodeFromVar($currentNode);
        }

        $currentNodeAncestorPaths = array();
        if($currentNode){
            if($currentNode->getDepth() === 1){
                $rootParent = $currentNode;
                $sortedSiblings = array($currentNode);
            } else {
                $currentNodeAncestorPaths = $currentNode->getAncestorPaths();
                $rootParent = $currentNode->getRootParent($this);
                $sortedSiblings = array($rootParent);
            }

            if($rootParent){
                $breadcrumbData = array($rootParent->prepareForTemplate($this, array($rootParent), $currentNode, $currentNodeAncestorPaths, $filter));
                return $this->flattenBreadcrumbData($breadcrumbData);
            }
        }
    }

    /**
     * Converts nested node array data into flattened node array data
     * @param array $breadcrumbData
     * @param array $output
     * @return array
     */
    protected function flattenBreadcrumbData($breadcrumbData, $output = array()){
        foreach($breadcrumbData as $node){
            $children = $node['children'];
            $node['children'] = array();
            $output[] = $node;
            if($children){
                return $this->flattenBreadcrumbData($children, $output);
            }
        }
        return $output;
    }

    /**
     * Returns a new collection filtered by the $filterFunc callback
     *
     * The callback method signature should include the follow parameters:
     *
     *  - **`$node`**:       The node to be filtered
     *  - **`$collection`**: The collection being filtered (the original unfiltered collection)
     *
     * @param callback $filterFunc Function to filter nodes - see the method description for details about the method signature
     * @param bool $autoSetDisplayOrder If true, when nodes are added to the new filtered collection object and $displayOrder is empty or a sibling node has the same display order value, the display order is set to the next available number of those siblings
     * @param bool $removeDecendants if true decendants of nodes that filter as false are removed
     * @return \Navinator\Collection collection with the filtered results
     */
    public function filter($filterFunc, $autoSetDisplayOrder = true, $removeDecendants = true){
        // decendants of removed nodes
        $blackList = array();
        $filteredCollection = new \Navinator\Collection;
        foreach($this->nodes as $path => $node){
            if(in_array($path, $blackList)){
                continue;
            }
            $displayOrder = $this->getNodeDisplayOrder($path);
            if($filterFunc($node, $this)){
                $filteredCollection->addNode($node, $displayOrder, $autoSetDisplayOrder);
            } else{
                // add decendant paths to the black list
                if($removeDecendants){
                    $decendants = $node->getDescendants($this);
                    if(!empty($decendants)){
                        foreach($decendants as $desNode){
                            $blackList[] = $desNode->getPath();
                        }
                    }
                }
            }
        }

        foreach($blackList as $nodePath){
            $filteredCollection->removeNode($nodePath);
        }
        return $filteredCollection;
    }

    /**
     * Check for orphaned nodes
     * @throws \Navinator\Exception If orphaned nodes are found throw an exception listing them
     */
    public function validateNodes(){
        $orphans = $this->getOrphanNodes();
        $orphanPaths = array();
        foreach($orphans as $path => $node){
            $orphanPaths[] = "'" . $path . "'";
        }
        if(!empty($orphanPaths)){
            throw new \Navinator\Exception(sprintf('The following node(s) do not have a parent node in this collection : %s', implode(', ', $orphanPaths)));
        }
    }

    /**
     * Retrieves an array of orphaned nodes
     * @return array
     */
    public function getOrphanNodes(){
        $orphans = array();
        foreach($this->nodes as $path => $node){
            $parentPath = $node->getParentPath();
            if($node->getDepth() > 1 && !$this->hasNode($parentPath)){
                $orphans[$path] = $node;
            }
        }
        return $orphans;
    }

    /**
     * Set offset to value
     * Implements ArrayAccess
     * @see set
     * @param integer $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value){
        $this->addNode($value);
    }

    /**
     * Unset offset
     * Implements ArrayAccess
     * @see remove
     * @param integer $offset
     */
    public function offsetUnset($offset){
        unset($this->nodes[$offset]);
    }

    /**
     * Get an offset's value
     * Implements ArrayAccess
     * @see get
     * @param integer $offset
     * @return mixed
     */
    public function offsetGet($offset){
        return $this->nodes[$offset];
    }

    /**
     * Determine if offset exists
     * Implements ArrayAccess
     * @see exists
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset){
        return isset($this->nodes[$offset]);
    }

    /**
     * Return count of items in collection
     * Implements countable
     * @return integer
     */
    public function count(){
        return count($this->nodes);
    }

    public function rewind(){
        reset($this->nodes);
    }

    public function current(){
        return current($this->nodes);
    }

    public function key(){
        return key($this->nodes);
    }

    public function next(){
        return next($this->nodes);
    }

    public function valid(){
        $key = key($this->nodes);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * Calls a specific method on each object, returning an array of the results
     *
     * @param  string $method     The method to call
     * @param  mixed  $parameter  A parameter to pass for each call to the method
     * @param  mixed  ...
     * @return array  An array the size of the record set with one result from each record/method
     */
    public function call($method){
        $parameters = array_slice(func_get_args(), 1);
        $output = array();
        foreach($this->nodes as $node){
            if(method_exists($node, $method)){
                $output[] = call_user_func_array(
                    array($node, $method), $parameters
                );
            }
        }
        return $output;
    }

}

