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
     * The display order of the node in relation to it's siblings
     * Used to initially set the display order of a node in a collection. After being added to a collection this property has no effect on the ordering of a node
     * @var int
     */
    public $display_order;

    /**
     * When false this node will be considered when trying to find the node with url most mathcing the requested url
     * When true this node will only match as the "current" node if the requested url matches this node's ->url exactly
     * @var bool
     */
    public $current_only_on_exact_url_match = false;

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
     * @param string $displayName ignored when $obj is an array
     */
    public function __construct($obj, $displayName = null){

        if(!is_array($obj)){
            $path = (string)$obj;
            $this->setPath($path);

            if($displayName === null){
                $displayName = StringHelper::humanizeString($this->getLastPathSegment());
            }

            $this->display_name = $displayName;
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

            // use ->setPath to trim and validate the path
            $this->setPath($mergedArray['path']);
            unset($mergedArray['path']);

            foreach($mergedArray as $key => $val){
                $this->{$key} = $val;
            }
            if(!$this->display_name){
                $this->display_name = StringHelper::humanizeString($this->getLastPathSegment());
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
     * Retrieves this node's root parent path or false if it has none
     * @param \Navinator\Collection $collection Collection context to get the root parent node from
     * @return \Drive\Data\Nav\Node|bool
     */
    public function getRootParentPath(){
        if($this->getDepth() == 1){
            return false;
        }
        $pathArray = $this->getPathArray();
        return $pathArray[0];
    }

    /**
     * Retrieves this node's root parent node object or false if it has none
     * @param \Navinator\Collection $collection Collection context to get the root parent node from
     * @return \Drive\Data\Nav\Node|bool
     */
    public function getRootParent(\Navinator\Collection $collection){
        $rootParentPath = $this->getRootParentPath();
        if($rootParentPath){
            return $collection->getNode($rootParentPath);
        }
        return false;
    }

    /**
     * Checks if this node has children
     * @param \Navinator\Collection $collection Collection context to check if this node has children from
     * @return bool
     */
    public function hasChildren(\Navinator\Collection $collection){
        foreach($collection->getNode() as $node){
            if(StringHelper::strStartsWith($this->getPath() . '/', $node->getPath()) && $node->getDepth() == $this->getDepth() + 1){
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
            if(StringHelper::strStartsWith($this->getPath() . '/', $node->getPath()) && $node->getDepth() == $this->getDepth() + 1){
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
                $segment = $prevPath . '/' . $segment;
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
            $prefix = $this->getPath() . '/';
            if($node->getDepth() > $this->getDepth() && StringHelper::strStartsWith($prefix, $node->getPath())){
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
                if(StringHelper::strStartsWith($parentPath . '/', $node->getPath()) && $node->getDepth() == $depth){
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
    public function getLastPathSegment(){
        $path = $this->path;
        if(strrpos($path, '/') === false){
            return $path;
        }
        return substr($path, strrpos($path, '/') + 1, strlen($path));
    }

    /**
     * Convert this node to an array ready to be used by the template
     *
     * The $filter callback method signature should include the follow parameters:
     *
     *  - **`$node`**:       The node to be filtered
     *  - **`$nodeArrayData`**: Node array data to be returned for template
     *  - **`$collection`**:
     *  - **`$sortedSiblings`**:
     *  - **`$currentNode`**:
     *  - **`$currentNodeAncestorPaths`**:
     *
     * @param \Navinator\Collection $collection Collection context used to convert this node to an array
     * @param array $sortedSiblings sorted siblings of this node includes this node (passed to avoid fetching them constantly)
     * @param \Navinator\Node $currentNode The node to treat as the currently navigated to node
     * @param array $currentNodeAncestorPaths Ancestor path of the current node (passed to avoid fetching them constantly)
     * @param callback $filter Function to filter nodes - see the method description for details about the method signature
     */
    public function prepareForTemplate(\Navinator\Collection $collection,  $sortedSiblings = array(), \Navinator\Node $currentNode = null, $currentNodeAncestorPaths = array(), $filter = null){
        $isCurrentNodeAncestor = !empty($currentNodeAncestorPaths) && in_array($this->path, $currentNodeAncestorPaths);
        $isCurrentNode = !empty($currentNode) && $this->path == $currentNode->getPath();
        $isCurrentRoot = ($this->getDepth() == 1 && $isCurrentNodeAncestor);

        $isFirstChild = false;
        $isLastChild = false;

        if($sortedSiblings){
            reset($sortedSiblings);
            $firstKey = key($sortedSiblings);
            if($this === $sortedSiblings[$firstKey]){
                $isFirstChild = true;
            }

            end($sortedSiblings);
            $lastKey = key($sortedSiblings);
            if($this === $sortedSiblings[$lastKey]){
                $isLastChild = true;
            }
        }

        $output = array(
            'url'                 => $this->url,
            'path'                => $this->path,
            'display_name'        => $this->display_name,
            'template_data'       => $this->template_data,
            'depth'               => $this->getDepth(),
            // if this node is the first or last child of it's siblings
            'is_first_child'      => $isFirstChild,
            'is_last_child'       => $isLastChild,
            'is_current_root'     => $isCurrentRoot,
            'is_current'          => $isCurrentNode,
            'is_current_ancestor' => $isCurrentNodeAncestor,
            'display_order' => $collection->getNodeDisplayOrder($this),
        );

        // if this node filters to false, return and do not handle child nodes
        if($filter && $filter instanceof \Closure && !$filter($this, $output, $collection, $sortedSiblings, $currentNode, $currentNodeAncestorPaths)){
            return;
        }

        $children = $this->getChildren($collection);
        $childArr = array();
        if($children){
            $children = $collection->sortNodeArray($children);
            foreach($children as $child){
                $childItem = $child->prepareForTemplate($collection, $children, $currentNode, $currentNodeAncestorPaths, $filter);
                if(!empty($childItem)){
                    $childArr[] = $childItem;
                }
            }
        }

        $output['children'] = $childArr;

        return $output;
    }
}

