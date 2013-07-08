<?php

namespace Navinator;

/**
 * Nav node data object
 *
 * Manages node metadata
 *
 * @license MIT
 *
 * @package Navinator
 * @link https://github.com/unstoppablecarl/navinator
 * @author Carl Olsen <unstoppablecarlolsen@gmail.com>
 */
class Node{

    /**
     * Path to this nav node with NO beginning or trailing slash
     * **`articles/tags/news`**
     *
     * @var string
     */
    protected $path;

    /**
     * Display name of node
     *
     * Inteded to be the text within the <a> tag of this node when rendered
     *
     * @var string
     */
    public $display_name;

    /**
     * The url for the link tag
     *
     * Intended to be the value of the href attribute on the <a> tag in the view
     * If not set the path (with slashes added to the beginning and end) is used when prepareForTemplate() is called
     *
     * @var string
     */
    public $url;

    /**
     * Custom template data
     * @var array
     */
    public $template_data = array();

    /**
     * Array keys required to build a node from an array
     *
     * When constructing a node object with an associative array as the constructor paramater, an exception will be thrown if any of these array keys are not found
     * @var array
     */
    protected $required_constructor_array_keys = array('path');

    /**
     * Default array key value pairs when building a node from an array
     *
     * Also used as a list of keys to assign array values to
     * @var array
     */
    protected $default_constructor_array = array(
        'path'          => null,
        'display_name'  => null,
        'url'           => null,
        'template_data' => array(),
    );

    /**
     * Constructs a node object
     * @param string|array $obj If $obj is a string it is used as the path for the constructed node formatted like: articles/tags/news. If $obj is an array, key value pairs will be assigned to node object properties
     * @param int $displayOrder
     */
    public function __construct($obj){
        if(!is_array($obj)){
            $path = $obj;
            $this->setPath($path);
            $this->display_name = $this->getNodeName();
            $this->url = '/' . $this->path . '/';
        } else{
            $array = $obj;
            $arrayKeys = array_keys($array);
            $missingKeys = array();
            foreach($this->required_constructor_array_keys as $requiredKey){
                if(!in_array($requiredKey, $arrayKeys)){
                    $missingKeys[] = $requiredKey;
                }
            }
            if(count($missingKeys)){
                throw new \Navinator\Exception('Attempting to create ' . __CLASS__ . ' from invalid array. The required array key(s) were not found: ' . implode(', ', $missingKeys));
            }
            // remove keys in $array that are NOT in $this->default_constructor_array
            $filteredArray = array_intersect_key($array, $this->default_constructor_array);

            // merge with defaults to avoid undefined indexes
            $mergedArray = array_merge($this->default_constructor_array, $filteredArray);
            foreach($mergedArray as $key => $val){
                $this->{$key} = $val;
            }
            if(!$this->display_name){
                $this->display_name = $this->getNodeName();
            }
            if(!$this->url){
                $this->url = '/' . $this->path . '/';
            }
        }
    }

    /**
     * Getter for $path
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * Setter for $path
     *
     * Trims spaces and removes slashes from the beginning and end of the path
     *
     * @param string $path The path to be set
     * @throws \Exception If the value of the path is not a non-empty string
     */
    public function setPath($path){
        $pathString = trim((string)$path);
        if(!strlen($pathString)){
            throw new \Navinator\Exception(sprintf('Attempting to set an invalid node path "%s". A node path must be a non-empty string.', $path));
        }
        $this->path = trim($pathString, '/');
    }

    /**
     * Retrieves path as an array. A node with path "articles/tags/news" returns the path array: array('articles', 'tags', 'news')
     * @return array
     */
    public function getPathArray(){
        return explode('/', $this->path);
    }

    /**
     * Retrieves the depth of this node. A node with path "articles/tags/news" has a depth of 3
     * @return int
     */
    public function getDepth(){
        return count($this->getPathArray());
    }

    /**
     * Retrieves this node's parent node path.  A node with path "articles/tags/news" returns the parent path "articles/tags"
     * @return boolean|string If the node does not have a parent false is returned
     */
    public function getParentPath(){
        $parentPath = dirname($this->path);
        if($parentPath == '.'){
            return false;
        }
        return $parentPath;
    }

    /**
     * Retrieves this node's parent node object or false if it does not have a parent
     * @param \Navinator\Collection $collection Collection context to get parent node from
     * @return \Drive\Data\Nav\Node|bool
     */
    public function getParent(\Navinator\Collection $collection){
        if($this->getDepth() == 1){
            return false;
        }
        $parentPath = $this->getParentPath();
        return $collection->getNode($parentPath);
    }

    /**
     * Retrieves this node's root parent node object or false if it has none
     * @param \Navinator\Collection $collection Collection context to get the root parent node from
     * @return \Drive\Data\Nav\Node|bool
     */
    public function getRootParent(\Navinator\Collection $collection){
        if($this->getDepth() == 1){
            return false;
        }
        $pathArray = $this->getPathArray();
        $rootParentPath = $pathArray[0];
        return $collection->getNode($rootParentPath);
    }

    /**
     * Checks if this node has children
     * @param \Navinator\Collection $collection Collection context to check if this node has children from
     * @return bool
     */
    public function hasChildren(\Navinator\Collection $collection){
        foreach($collection->getNode() as $node){
            if(\Navinator\Collection::strStartsWith($this->getPath() . '/', $node->getPath()) && $node->getDepth() == $this->getDepth() + 1){
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves an array of this node's children
     * @param \Navinator\Collection $collection Collection context get children from
     * @return array of child nodes
     */
    public function getChildren(\Navinator\Collection $collection){
        $childNodes = array();
        foreach($collection->getNode() as $node){
            if(\Navinator\Collection::strStartsWith($this->getPath() . '/', $node->getPath()) && $node->getDepth() == $this->getDepth() + 1){
                $childNodes[$node->getPath()] = $node;
            }
        }
        return $childNodes;
    }

    /**
     * Retrieves an array of ancestor nodes
     * @param \Navinator\Collection $collection Collection context get ancestors from
     * @return array of ancestor nodes
     */
    public function getAncestors(\Navinator\Collection $collection){
        $ancestorsArray = array();
        $node = $this;
        while($node->getParent($collection)){
            $parent = $node->getParent($collection);
            $ancestorsArray[$parent->getPath()] = $parent;
            $node = $parent;
        }
        return $ancestorsArray;
    }

    /**
     * Retrieves an array of ancestor paths
     * @return array of ancestor paths
     */
    public function getAncestorPaths(){
        $output = array();
        $pathArray = $this->getPathArray();
        $prevPath = '';
        foreach($pathArray as $segment){
            if($prevPath){
                $segment =  $prevPath . '/' . $segment;
            }
            if($segment != $this->path){
                $output[] = $segment;
                $prevPath = $segment;
            }
        }
        return $output;
    }

    /**
     * Retrieves an array of decendants
     * @param \Navinator\Collection $collection Collection context get decendants from
     * @return array array of decendants
     */
    public function getDescendants(\Navinator\Collection $collection){
        $childNodes = array();
        foreach($collection->getNode() as $node){
            if(\Navinator\Collection::strStartsWith($this->getPath() .'/', $node->getPath()) && $node->getDepth() > $this->getDepth()){
                $childNodes[$node->getPath()] = $node;
            }
        }
        return $childNodes;
    }

    /**
     * Retrieves an array of siblings
     * @param \Navinator\Collection $collection Collection context get siblings from
     * @param bool $includeSelf If true this node will be included in the list
     */
    public function getSiblings(\Navinator\Collection $collection, $includeSelf = false){
        $siblings = array();
        $depth = $this->getDepth();
        if($depth == 1){
            $siblings = $collection->getRootNodes();
        } else{
            $parentPath = $this->getParentPath();
            foreach($collection->getNode() as $node){
                if(\Navinator\Collection::strStartsWith($parentPath . '/', $node->getPath()) && $node->getDepth() == $depth){
                    $siblings[$node->getPath()] = $node;
                }
            }
        }
        if(!$includeSelf){
            unset($siblings[$this->getPath()]);
        }
        return $siblings;
    }

    /**
     * Retrieves the last segment in the path. "articles/categories/news" would return "news"
     * @return string
     */
    public function getNodeName(){
        $pathArray = $this->getPathArray();
        return end($pathArray);
    }

    /**
     * Convert this node to an array ready to be used by the template
     *
     * The $filter callback method signature should include the follow parameters:
	 *
	 *  - **`$node`**:       The node to be filtered
	 *  - **`$nodeArrayData`**: Node array data to be returned for template
	 *  - **`$collection`**:
     *  - **`$currentNode`**:
     *  - **`$currentNodeAncestorPaths`**:
     *
     * @param \Navinator\Collection $collection Collection context used to convert this node to an array
     * @param \Navinator\Node $currentNode The node to treat as the currently navigated to node
     * @param array $currentNodeAncestorPaths Ancestor path of the current node
     * @param callback $filter Function to filter nodes - see the method description for details about the method signature
     */
    public function prepareForTemplate(\Navinator\Collection $collection, \Navinator\Node $currentNode = null, $currentNodeAncestorPaths = array(), $filter = null){

        $url = $this->url;
        if(empty($url)){
            $url = '/' . $this->path . '/';
        }

        $isCurrentNodeAncestor = !empty($currentNodeAncestorPaths) && in_array($this->path, $currentNodeAncestorPaths);
        $isCurrentNode = !empty($currentNode) && $this->path == $currentNode->getPath();
        $isCurrentRoot = ($this->getDepth() == 1 && $isCurrentNodeAncestor);

        $output = array(
            'url'                      => $url,
            'path'                     => $this->path,
            'display_name'             => $this->display_name,

            'template_data'            => $this->template_data,
            'depth'                    => $this->getDepth(),

            // if this node is the first or last child of it's siblings
            'is_first_child'           => false,
            'is_last_child'            => false,
            'is_current_root'          => $isCurrentRoot,
            'is_current'               => $isCurrentNode,
            'is_current_ancestor'      => $isCurrentNodeAncestor,
        );

        // if this node filters to false, return and do not handle child nodes
        if($filter !== null && !$filter($this, $output, $collection, $currentNode, $currentNodeAncestorPaths)){
            return;
        }

        $children = $this->getChildren($collection);
        $childArr = array();
        if($children){
            foreach($children as $child){
                $childItem = $child->prepareForTemplate($collection, $currentNode, $currentNodeAncestorPaths, $filter);
                if(!empty($childItem)){
                    $childItem['display_order'] = $collection->getNodeDisplayOrder($child->getPath());
                    $childArr[] = $childItem;
                }
            }
        }

        if(!empty($childArr)){
            usort($childArr, function($a, $b){
                    if($a['display_order'] == $b['display_order']){
                        return 0;
                    }
                    return ($a['display_order'] < $b['display_order']) ? -1 : 1;
                });
            reset($childArr);
            $firstKey = key($childArr);
            $childArr[$firstKey]['is_first_child'] = true;

            end($childArr);
            $lastKey = key($childArr);
            $childArr[$lastKey]['is_last_child'] = true;
        }
        $output['children'] = $childArr;

        return $output;
    }
}

