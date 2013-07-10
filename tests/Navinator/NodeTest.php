<?php

namespace Navinator;

class NodeTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass(){

    }

    public static function tearDownAfterClass(){

    }


    static public function testHumanizeStrProvider(){
        return array(
            array(
                'alpha',
                'Alpha',
            ),
            array(
                'alpha-beta',
                'Alpha Beta',
            ),
            array(
                'alpha_beta',
                'Alpha Beta',
            ),
        );
    }

    /**
     *
     * @dataProvider testHumanizeStrProvider
     */
    public function testHumanizeStr($str, $expected){
        $n = new Node('x');
        $this->assertEquals($expected, $n->humanizeString($str));
    }

    static public function testConstructProvider(){
        return array(
            array(
                array(
                    'path'                => 'alpha',
                    'path_array'          => array('alpha'),
                    'ancestor_path_array' => array(),
                    'parent_path'         => false,
                    'depth'               => 1,
                    'display_name'        => 'Alpha',
                    'last_path_segment'   => 'alpha',
                ),),
            array(
                array(
                    'path'                => 'alpha/beta',
                    'path_array'          => array('alpha', 'beta'),
                    'ancestor_path_array' => array('alpha'),
                    'parent_path'         => 'alpha',
                    'depth'               => 2,
                    'display_name'        => 'Beta',
                    'last_path_segment'   => 'beta',
                ),),
            array(
                array(
                    'path'                => 'alpha/beta/gamma',
                    'path_array'          => array('alpha', 'beta', 'gamma'),
                    'ancestor_path_array' => array('alpha', 'alpha/beta'),
                    'parent_path'         => 'alpha/beta',
                    'depth'               => 3,
                    'display_name'        => 'Gamma',
                    'last_path_segment'   => 'gamma',
                ),),
            array(
                array(
                    'path'                => 'alpha/beta/gamma/delta',
                    'path_array'          => array('alpha', 'beta', 'gamma', 'delta'),
                    'ancestor_path_array' => array('alpha', 'alpha/beta', 'alpha/beta/gamma'),
                    'parent_path'         => 'alpha/beta/gamma',
                    'depth'               => 4,
                    'display_name'        => 'Delta',
                    'last_path_segment'   => 'delta',
                ),
            ),
        );
    }

    /**
     *
     * @dataProvider testConstructProvider
     */
    public function testConstruct($params){

        extract($params);
        $n = new Node($path);
        $this->assertEquals($path, $n->getPath());
        // test url
        $this->assertEquals('/' . $path . '/', $n->url, 'Cannot covert path to url correctly');

        $this->assertEquals($path_array, $n->getPathArray());
        $this->assertEquals($parent_path, $n->getParentPath());
        $this->assertEquals($depth, $n->getDepth());
        $this->assertEquals($ancestor_path_array, $n->getAncestorPaths());
        $this->assertEquals($display_name, $n->display_name);
    }

    public function testConstructFromArrayProvider(){
        return array(
            //test item
            array(
                // constructor array
                array(
                    'path' => 'alpha',
                ),
                // expected values
                array(
                    'path'          => 'alpha',
                    'display_name'  => 'Alpha',
                    'url'           => '/alpha/',
                    'template_data' => array(),
                ),
            ),
            array(
                // constructor array
                array(
                    'path'          => 'alpha',
                    'display_name'  => 'alpha-display-name',
                    'url'           => '/alpha-url/',
                    'template_data' => array('foo'),
                ),
                // expected values
                array(
                    'path'          => 'alpha',
                    'display_name'  => 'alpha-display-name',
                    'url'           => '/alpha-url/',
                    'template_data' => array('foo'),
                )
            ),
            array(
                // constructor array
                array(
                    'path' => 0,
                ),
                // expected values
                array(
                    'path'          => '0',
                    'display_name'  => '0',
                    'url'           => '/0/',
                    'template_data' => array(),
                )
            ),
            array(
                // constructor array
                array(
                    'path' => 90,
                ),
                // expected values
                array(
                    'path'          => '90',
                    'display_name'  => '90',
                    'url'           => '/90/',
                    'template_data' => array(),
                )
            )
        );
    }

    /**
     *
     * @dataProvider testConstructFromArrayProvider
     */
    public function testConstructFromArray($testData, $expectedData){
        $n = new Node($testData);

        extract($expectedData);
        $this->assertEquals($path, $n->getPath());
        $this->assertEquals($url, $n->url);
        $this->assertEquals($template_data, $n->template_data);
        $this->assertEquals($display_name, $n->display_name);
    }

    public function testConstructException(){
        $this->setExpectedException(
            'Navinator\Exception', 'Attempting to set an invalid node path "". A node path must be a non-empty string.'
        );

        $n = new Node('');
    }

    public function testConstructException2(){

        $this->setExpectedException(
            'Navinator\Exception', 'Attempting to set an invalid node path "". A node path must be a non-empty string.'
        );

        $n = new Node(false);
    }

    public function testConstructException3(){
        $nodeArr = array(
            'foo' => 'bar'
        );

        $this->setExpectedException(
            'Navinator\Exception', 'Attempting to create Navinator\Node from invalid array. The required array key(s) were not found: path'
        );

        $n = new Node($nodeArr);
    }

    public function testGetLastPathSegmentProvider(){
        return array(
            array(
                'alpha',
                'alpha'
            ),
            array(
                'alpha/beta',
                'beta'
            ),
            array(
                'alpha/beta/gamma',
                'gamma'
            )
        );
    }

    /**
     *
     * @dataProvider testGetLastPathSegmentProvider
     */
    public function testGetLastPathSegment($path, $nodeName){
        $n = new Node($path);
        $this->assertEquals($nodeName, $n->getLastPathSegment());
    }

    /**
     * @covers Navinator\Node::getParent
     */
    public function testGetParent(){

        $nodeData = array(
            'alpha',
            'alpha-2',
            'alpha/beta',
            'alpha/beta-2',
            'alpha/beta/gamma',
            'alpha/beta/gamma-2',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'foo',
            'foo-2',
            'foo/bar',
            'foo/bar-2',
            'foo/bar/baz',
            'foo/bar/baz-2',
            'foo/bar/baz/blam',
            'foo/bar/baz/blam-2',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        // assert false == parent of alpha
        $this->assertEquals(false, $nodes['alpha']->getParent($c));
        // assert alpha == parent of alpha/beta
        $this->assertEquals($nodes['alpha'], $nodes['alpha/beta']->getParent($c));
        // assert alpha/beta == parent of alpha/beta/gamma
        $this->assertEquals($nodes['alpha/beta'], $nodes['alpha/beta/gamma']->getParent($c));
        // assert alpha/beta/gamma == parent of alpha/beta/gamma/delta
        $this->assertEquals($nodes['alpha/beta/gamma'], $nodes['alpha/beta/gamma/delta']->getParent($c));

        // assert false == parent of foo
        $this->assertEquals(false, $nodes['foo']->getParent($c));
        // assert foo == parent of foo/bar
        $this->assertEquals($nodes['foo'], $nodes['foo/bar']->getParent($c));
        // assert foo/bar == parent of foo/bar/baz
        $this->assertEquals($nodes['foo/bar'], $nodes['foo/bar/baz']->getParent($c));
        // assert foo/bar/baz == parent of foo/bar/baz/blam
        $this->assertEquals($nodes['foo/bar/baz'], $nodes['foo/bar/baz/blam']->getParent($c));
    }

    /**
     * @covers Navinator\Node::getRootParent
     */
    public function testGetRootParent(){

        $nodeData = array(
            'alpha',
            'alpha-2',
            'alpha/beta',
            'alpha/beta-2',
            'alpha/beta/gamma',
            'alpha/beta/gamma-2',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'foo',
            'foo-2',
            'foo/bar',
            'foo/bar-2',
            'foo/bar/baz',
            'foo/bar/baz-2',
            'foo/bar/baz/blam',
            'foo/bar/baz/blam-2',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        // assert false == root parent of alpha
        $this->assertEquals(false, $nodes['alpha']->getRootParent($c));
        // assert alpha == root parent of alpha/beta
        $this->assertEquals($nodes['alpha'], $nodes['alpha/beta']->getRootParent($c));
        // assert alpha == root parent of alpha/beta/gamma
        $this->assertEquals($nodes['alpha'], $nodes['alpha/beta/gamma']->getRootParent($c));
        // assert alpha == root parent of alpha/beta/gamma/delta
        $this->assertEquals($nodes['alpha'], $nodes['alpha/beta/gamma/delta']->getRootParent($c));

        // assert false == root parent of foo
        $this->assertEquals(false, $nodes['foo']->getRootParent($c));
        // assert foo == root parent of foo/bar
        $this->assertEquals($nodes['foo'], $nodes['foo/bar']->getRootParent($c));
        // assert foo == root parent of foo/bar/baz
        $this->assertEquals($nodes['foo'], $nodes['foo/bar/baz']->getRootParent($c));
        // assert foo == root parent of foo/bar/baz/blam
        $this->assertEquals($nodes['foo'], $nodes['foo/bar/baz/blam']->getRootParent($c));
    }

    /**
     * @covers Navinator\Node::hasChildren
     */
    public function testHasChildren(){

        $nodeData = array(
            'alpha',
            'alpha-2',
            'alpha/beta',
            'alpha/beta-2',
            'alpha/beta/gamma',
            'alpha/beta/gamma-2',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'foo',
            'foo-2',
            'foo/bar',
            'foo/bar-2',
            'foo/bar/baz',
            'foo/bar/baz-2',
            'foo/bar/baz/blam',
            'foo/bar/baz/blam-2',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->assertEquals(true, $nodes['alpha']->hasChildren($c));
        $this->assertEquals(true, $nodes['alpha/beta']->hasChildren($c));
        $this->assertEquals(true, $nodes['alpha/beta/gamma']->hasChildren($c));
        $this->assertEquals(false, $nodes['alpha/beta/gamma/delta']->hasChildren($c));

        $this->assertEquals(true, $nodes['foo']->hasChildren($c));
        $this->assertEquals(true, $nodes['foo/bar']->hasChildren($c));
        $this->assertEquals(true, $nodes['foo/bar/baz']->hasChildren($c));
        $this->assertEquals(false, $nodes['foo/bar/baz/blam']->hasChildren($c));
    }

    /**
     * @covers Navinator\Node::getChildren
     */
    public function testGetChildren(){

        $nodeData = array(
            'alpha',
            'alpha-2',
            'alpha/beta',
            'alpha/beta-2',
            'alpha/beta/gamma',
            'alpha/beta/gamma-2',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'foo',
            'foo-2',
            'foo/bar',
            'foo/bar-2',
            'foo/bar/baz',
            'foo/bar/baz-2',
            'foo/bar/baz/blam',
            'foo/bar/baz/blam-2',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->assertEquals(
            array(
            'alpha/beta'   => $nodes['alpha/beta'],
            'alpha/beta-2' => $nodes['alpha/beta-2'],
            ), $nodes['alpha']->getChildren($c)
        );

        $this->assertEquals(
            array(
            'alpha/beta/gamma'   => $nodes['alpha/beta/gamma'],
            'alpha/beta/gamma-2' => $nodes['alpha/beta/gamma-2'],
            ), $nodes['alpha/beta']->getChildren($c)
        );

        $this->assertEquals(
            array(
            'alpha/beta/gamma/delta'   => $nodes['alpha/beta/gamma/delta'],
            'alpha/beta/gamma/delta-2' => $nodes['alpha/beta/gamma/delta-2'],
            ), $nodes['alpha/beta/gamma']->getChildren($c)
        );

        $this->assertEquals(
            array(
            'foo/bar'   => $nodes['foo/bar'],
            'foo/bar-2' => $nodes['foo/bar-2'],
            ), $nodes['foo']->getChildren($c)
        );

        $this->assertEquals(
            array(
            'foo/bar/baz'   => $nodes['foo/bar/baz'],
            'foo/bar/baz-2' => $nodes['foo/bar/baz-2'],
            ), $nodes['foo/bar']->getChildren($c)
        );

        $this->assertEquals(
            array(
            'foo/bar/baz/blam'   => $nodes['foo/bar/baz/blam'],
            'foo/bar/baz/blam-2' => $nodes['foo/bar/baz/blam-2'],
            ), $nodes['foo/bar/baz']->getChildren($c)
        );
    }

    /**
     * @covers Navinator\Node::getSiblings
     */
    public function testGetSiblings(){
        $nodeData = array(
            'alpha',
            'alpha/beta',
            'alpha/beta/gamma',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'alpha/beta/gamma/blam',
            'alpha/beta/gamma/blam-2',
            'alpha/beta/gamma-2',
            'alpha/beta/baz',
            'alpha/beta/baz-2',
            'alpha/beta-2',
            'alpha/bar',
            'alpha/bar-2',
            'foo',
            'foo-2',
            'foo/bar',
            'foo/bar-2',
            'foo/bar/baz',
            'foo/bar/baz-2',
            'foo/bar/baz/blam',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->assertEquals(
            array(
            'alpha/beta-2' => $nodes['alpha/beta-2'],
            'alpha/bar'    => $nodes['alpha/bar'],
            'alpha/bar-2'  => $nodes['alpha/bar-2'],
            ), $nodes['alpha/beta']->getSiblings($c)
        );

        $this->assertEquals(
            array(
            'foo/bar-2' => $nodes['foo/bar-2'],
            ), $nodes['foo/bar']->getSiblings($c)
        );

        $this->assertEquals(
            array(
            'foo/bar'   => $nodes['foo/bar'],
            'foo/bar-2' => $nodes['foo/bar-2'],
            ), $nodes['foo/bar']->getSiblings($c, true)
        );

        $this->assertEquals(
            array(), $nodes['foo/bar/baz/blam']->getSiblings($c)
        );

        $this->assertEquals(
            array(
            'foo/bar/baz/blam' => $nodes['foo/bar/baz/blam'],
            ), $nodes['foo/bar/baz/blam']->getSiblings($c, true)
        );
    }

    /**
     * @covers Navinator\Node::getAncestors
     */
    public function testGetAncestors(){
        $nodeData = array(
            'alpha',
            'alpha/beta',
            'alpha/beta/gamma',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'alpha/beta/gamma/blam',
            'alpha/beta/gamma/blam-2',
            'alpha/beta/gamma-2',
            'alpha/beta/baz',
            'alpha/beta/baz-2',
            'alpha/beta-2',
            'alpha/bar',
            'alpha/bar-2',
            'foo',
            'foo-2',
            'foo/beta',
            'foo/beta-2',
            'foo/beta/gamma',
            'foo/beta/gamma-2',
            'foo/beta/gamma/delta',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->assertEquals(
            array(
            'alpha' => $nodes['alpha'],
            ), $nodes['alpha/beta']->getAncestors($c)
        );


        $this->assertEquals(
            array(
            'alpha'      => $nodes['alpha'],
            'alpha/beta' => $nodes['alpha/beta'],
            ), $nodes['alpha/beta/gamma']->getAncestors($c)
        );


        $this->assertEquals(
            array(
            'alpha'            => $nodes['alpha'],
            'alpha/beta'       => $nodes['alpha/beta'],
            'alpha/beta/gamma' => $nodes['alpha/beta/gamma'],
            ), $nodes['alpha/beta/gamma/delta']->getAncestors($c)
        );
    }

    /**
     * @covers Navinator\Node::getDescendants
     */
    public function testGetDescendants(){
        $nodeData = array(
            'alpha',
            'alpha/beta',
            'alpha/beta/gamma',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
            'alpha/beta/gamma/blam',
            'alpha/beta/gamma/blam-2',
            'alpha/beta/gamma-2',
            'alpha/beta/baz',
            'alpha/beta/baz-2',
            'alpha/beta-2',
            'alpha/bar',
            'alpha/bar-2',
            'foo',
            'foo-2',
            'foo/beta',
            'foo/beta-2',
            'foo/beta/gamma',
            'foo/beta/gamma-2',
            'foo/beta/gamma/delta',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->assertEquals(
            array(
            'alpha/beta/gamma/delta'   => $nodes['alpha/beta/gamma/delta'],
            'alpha/beta/gamma/delta-2' => $nodes['alpha/beta/gamma/delta-2'],
            'alpha/beta/gamma/blam'    => $nodes['alpha/beta/gamma/blam'],
            'alpha/beta/gamma/blam-2'  => $nodes['alpha/beta/gamma/blam-2'],
            ), $nodes['alpha/beta/gamma']->getDescendants($c)
        );

        $this->assertEquals(
            array(
            'alpha/beta/gamma'         => $nodes['alpha/beta/gamma'],
            'alpha/beta/gamma-2'       => $nodes['alpha/beta/gamma-2'],
            'alpha/beta/baz'           => $nodes['alpha/beta/baz'],
            'alpha/beta/baz-2'         => $nodes['alpha/beta/baz-2'],
            'alpha/beta/gamma/delta'   => $nodes['alpha/beta/gamma/delta'],
            'alpha/beta/gamma/delta-2' => $nodes['alpha/beta/gamma/delta-2'],
            'alpha/beta/gamma/blam'    => $nodes['alpha/beta/gamma/blam'],
            'alpha/beta/gamma/blam-2'  => $nodes['alpha/beta/gamma/blam-2'],
            ), $nodes['alpha/beta']->getDescendants($c)
        );

        $this->assertEquals(
            array(
            'alpha/beta'               => $nodes['alpha/beta'],
            'alpha/beta-2'             => $nodes['alpha/beta-2'],
            'alpha/bar'                => $nodes['alpha/bar'],
            'alpha/bar-2'              => $nodes['alpha/bar-2'],
            'alpha/beta/gamma'         => $nodes['alpha/beta/gamma'],
            'alpha/beta/gamma-2'       => $nodes['alpha/beta/gamma-2'],
            'alpha/beta/baz'           => $nodes['alpha/beta/baz'],
            'alpha/beta/baz-2'         => $nodes['alpha/beta/baz-2'],
            'alpha/beta/gamma/delta'   => $nodes['alpha/beta/gamma/delta'],
            'alpha/beta/gamma/delta-2' => $nodes['alpha/beta/gamma/delta-2'],
            'alpha/beta/gamma/blam'    => $nodes['alpha/beta/gamma/blam'],
            'alpha/beta/gamma/blam-2'  => $nodes['alpha/beta/gamma/blam-2'],
            ), $nodes['alpha']->getDescendants($c)
        );
    }

    /**
     * @covers Navinator\Node::prepareForTemplate
     */
    public function testPrepareForTemplate(){

        $nodeData = array(
            'alpha',
            'alpha/beta',
            'alpha/beta-2',
            'alpha/beta/gamma',
            'alpha/beta/gamma-2',
            'alpha/beta/gamma-2/no-siblings',
            'alpha/beta/gamma/delta',
            'alpha/beta/gamma/delta-2',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }


        $expected = array(
            'url'                 => '/alpha/',
            'path'                => 'alpha',
            'display_name'        => 'Alpha',
            'template_data'       => Array(),
            'depth'               => 1,
            'is_first_child'      => true,
            'is_last_child'       => true,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 1,
        );
        $expected['children'][0] = array(
            'url'                 => '/alpha/beta/',
            'path'                => 'alpha/beta',
            'display_name'        => 'Beta',
            'template_data'       => Array(),
            'depth'               => 2,
            'is_first_child'      => true,
            'is_last_child'       => false,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 1,
        );
        $expected['children'][1] = array(
            'url'                 => '/alpha/beta-2/',
            'path'                => 'alpha/beta-2',
            'display_name'        => 'Beta 2',
            'template_data'       => Array(),
            'depth'               => 2,
            'is_first_child'      => false,
            'is_last_child'       => true,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 2,
        );

        $expected['children'][0]['children'][0] = array(
            'url'                 => '/alpha/beta/gamma/',
            'path'                => 'alpha/beta/gamma',
            'display_name'        => 'Gamma',
            'template_data'       => Array(),
            'depth'               => 3,
            'is_first_child'      => true,
            'is_last_child'       => false,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 1,
        );

        $expected['children'][0]['children'][1] = array(
            'url'                 => '/alpha/beta/gamma-2/',
            'path'                => 'alpha/beta/gamma-2',
            'display_name'        => 'Gamma 2',
            'template_data'       => Array(),
            'depth'               => 3,
            'is_first_child'      => false,
            'is_last_child'       => true,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 2,
        );
        $expected['children'][0]['children'][1]['children'][0] = array(
            'url'                 => '/alpha/beta/gamma-2/no-siblings/',
            'path'                => 'alpha/beta/gamma-2/no-siblings',
            'display_name'        => 'No Siblings',
            'template_data'       => Array(),
            'depth'               => 4,
            'is_first_child'      => true,
            'is_last_child'       => true,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 1,
        );

        $expected['children'][0]['children'][0]['children'][0] = array(
            'url'                 => '/alpha/beta/gamma/delta/',
            'path'                => 'alpha/beta/gamma/delta',
            'display_name'        => 'Delta',
            'template_data'       => Array(),
            'depth'               => 4,
            'is_first_child'      => true,
            'is_last_child'       => false,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 1,
        );

        $expected['children'][0]['children'][0]['children'][1] = array(
            'url'                 => '/alpha/beta/gamma/delta-2/',
            'path'                => 'alpha/beta/gamma/delta-2',
            'display_name'        => 'Delta 2',
            'template_data'       => Array(),
            'depth'               => 4,
            'is_first_child'      => false,
            'is_last_child'       => true,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
            'display_order'       => 2,
        );
        $output = $nodes['alpha']->prepareForTemplate($c, array($nodes['alpha']));
        $this->assertEquals($expected, $output);
    }

    static public function testFilterCallbackProvider(){
        return array(
            // test group 1
            // testing single node
            array(
                // first param
                array(
                    'nodes' => array(
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha'
                            ),
                            'current_node_path'           => null,
                            'current_node_ancestor_paths' => array(),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha'
                                ),
                                'current_node_path'           => null,
                                'current_node_ancestor_paths' => array(),
                                'output'                      => array(
                                    'url'                 => '/alpha/',
                                    'path'                => 'alpha',
                                    'display_name'        => 'Alpha',
                                    'template_data'       => Array(),
                                    'depth'               => 1,
                                    'is_first_child'      => true,
                                    'is_last_child'       => true,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 1,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            // test group 2
            // testing first/last child and display_order
            array(
                array(
                    'nodes' => array(
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha-1'
                            ),
                            'current_node_path'           => null,
                            'current_node_ancestor_paths' => array(),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha-1',
                                    'alpha-2',
                                    'alpha-3',
                                ),
                                'current_node_path'           => null,
                                'current_node_ancestor_paths' => array(),
                                'output'                      => array(
                                    'url'                 => '/alpha-1/',
                                    'path'                => 'alpha-1',
                                    'display_name'        => 'Alpha 1',
                                    'template_data'       => Array(),
                                    'depth'               => 1,
                                    'is_first_child'      => true,
                                    'is_last_child'       => false,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 1,
                                ),
                            ),
                        ),
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha-2'
                            ),
                            'current_node_path'           => null,
                            'current_node_ancestor_paths' => array(),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha-1',
                                    'alpha-2',
                                    'alpha-3',
                                ),
                                'current_node_path'           => null,
                                'current_node_ancestor_paths' => array(),
                                'output'                      => array(
                                    'url'                 => '/alpha-2/',
                                    'path'                => 'alpha-2',
                                    'display_name'        => 'Alpha 2',
                                    'template_data'       => Array(),
                                    'depth'               => 1,
                                    'is_first_child'      => false,
                                    'is_last_child'       => false,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 2,
                                ),
                            ),
                        ),
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha-3'
                            ),
                            'current_node_path'           => null,
                            'current_node_ancestor_paths' => array(),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha-1',
                                    'alpha-2',
                                    'alpha-3',
                                ),
                                'current_node_path'           => null,
                                'current_node_ancestor_paths' => array(),
                                'output'                      => array(
                                    'url'                 => '/alpha-3/',
                                    'path'                => 'alpha-3',
                                    'display_name'        => 'Alpha 3',
                                    'template_data'       => Array(),
                                    'depth'               => 1,
                                    'is_first_child'      => false,
                                    'is_last_child'       => true,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 3,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            // test group 3
            // testing first/last child
            // display_order of child nodes
            // current node and ancestors
            array(
                array(
                    'nodes' => array(
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha'
                            ),
                            'current_node_path'           => 'alpha/beta-2',
                            'current_node_ancestor_paths' => array('alpha'),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha',
                                ),
                                'current_node_path'           => 'alpha/beta-2',
                                'current_node_ancestor_paths' => array('alpha'),
                                'output'                      => array(
                                    'url'                 => '/alpha/',
                                    'path'                => 'alpha',
                                    'display_name'        => 'Alpha',
                                    'template_data'       => Array(),
                                    'depth'               => 1,
                                    'is_first_child'      => true,
                                    'is_last_child'       => true,
                                    'is_current_root'     => true,
                                    'is_current'          => false,
                                    'is_current_ancestor' => true,
                                    'display_order'       => 1,
                                ),
                            ),
                        ),
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha/beta-1'
                            ),
                            'current_node_path'           => 'alpha/beta-2',
                            'current_node_ancestor_paths' => array('alpha'),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha/beta-1',
                                    'alpha/beta-2',
                                    'alpha/beta-3',
                                ),
                                'current_node_path'           => 'alpha/beta-2',
                                'current_node_ancestor_paths' => array('alpha'),
                                'output'                      => array(
                                    'url'                 => '/alpha/beta-1/',
                                    'path'                => 'alpha/beta-1',
                                    'display_name'        => 'Beta 1',
                                    'template_data'       => Array(),
                                    'depth'               => 2,
                                    'is_first_child'      => true,
                                    'is_last_child'       => false,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 1,
                                ),
                            ),
                        ),
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha/beta-2'
                            ),
                            'current_node_path'           => 'alpha/beta-2',
                            'current_node_ancestor_paths' => array('alpha'),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha/beta-1',
                                    'alpha/beta-2',
                                    'alpha/beta-3',
                                ),
                                'current_node_path'           => 'alpha/beta-2',
                                'current_node_ancestor_paths' => array('alpha'),
                                'output'                      => array(
                                    'url'                 => '/alpha/beta-2/',
                                    'path'                => 'alpha/beta-2',
                                    'display_name'        => 'Beta 2',
                                    'template_data'       => Array(),
                                    'depth'               => 2,
                                    'is_first_child'      => false,
                                    'is_last_child'       => false,
                                    'is_current_root'     => false,
                                    'is_current'          => true,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 2,
                                ),
                            ),
                        ),
                        array(
                            // make node with this data
                            'constructor_array'           => array(
                                'path' => 'alpha/beta-3',
                            ),
                            'current_node_path'           => 'alpha/beta-2',
                            'current_node_ancestor_paths' => array('alpha'),
                            'expected'                    => array(
                                'sorted_sibling_paths'        => array(
                                    'alpha/beta-1',
                                    'alpha/beta-2',
                                    'alpha/beta-3',
                                ),
                                'current_node_path'           => 'alpha/beta-2',
                                'current_node_ancestor_paths' => array('alpha'),
                                'output'                      => array(
                                    'url'                 => '/alpha/beta-3/',
                                    'path'                => 'alpha/beta-3',
                                    'display_name'        => 'Beta 3',
                                    'template_data'       => Array(),
                                    'depth'               => 2,
                                    'is_first_child'      => false,
                                    'is_last_child'       => true,
                                    'is_current_root'     => false,
                                    'is_current'          => false,
                                    'is_current_ancestor' => false,
                                    'display_order'       => 3,
                                ),
                            ),
                        ),
                    ),
                ),
                // test group 3
                // testing first/last child
                // reversed display_order of child nodes
                // current node and ancestors
                array(
                    array(
                        'nodes' => array(
                            array(
                                // make node with this data
                                'constructor_array'           => array(
                                    'path' => 'alpha'
                                ),
                                'current_node_path'           => 'alpha/beta-1',
                                'current_node_ancestor_paths' => array('alpha'),
                                'expected'                    => array(
                                    'sorted_sibling_paths'        => array(
                                        'alpha',
                                    ),
                                    'current_node_path'           => 'alpha/beta-1',
                                    'current_node_ancestor_paths' => array('alpha'),
                                    'output'                      => array(
                                        'url'                 => '/alpha/',
                                        'path'                => 'alpha',
                                        'display_name'        => 'Alpha',
                                        'template_data'       => Array(),
                                        'depth'               => 1,
                                        'is_first_child'      => true,
                                        'is_last_child'       => true,
                                        'is_current_root'     => true,
                                        'is_current'          => false,
                                        'is_current_ancestor' => true,
                                        'display_order'       => 1,
                                    ),
                                ),
                            ),
                            array(
                                // make node with this data
                                'constructor_array'           => array(
                                    'path' => 'alpha/beta-1'
                                ),
                                'current_node_path'           => 'alpha/beta-1',
                                'current_node_ancestor_paths' => array('alpha'),
                                'expected'                    => array(
                                    'sorted_sibling_paths'        => array(
                                        'alpha/beta-3',
                                        'alpha/beta-2',
                                        'alpha/beta-1',
                                    ),
                                    'current_node_path'           => 'alpha/beta-1',
                                    'current_node_ancestor_paths' => array('alpha'),
                                    'output'                      => array(
                                        'url'                 => '/alpha/beta-1/',
                                        'path'                => 'alpha/beta-1',
                                        'display_name'        => 'Beta 1',
                                        'template_data'       => Array(),
                                        'depth'               => 2,
                                        'is_first_child'      => true,
                                        'is_last_child'       => false,
                                        'is_current_root'     => false,
                                        'is_current'          => false,
                                        'is_current_ancestor' => false,
                                        'display_order'       => 3,
                                    ),
                                ),
                            ),
                            array(
                                // make node with this data
                                'constructor_array'           => array(
                                    'path' => 'alpha/beta-2'
                                ),
                                'current_node_path'           => 'alpha/beta-1',
                                'current_node_ancestor_paths' => array('alpha'),
                                'expected'                    => array(
                                    'sorted_sibling_paths'        => array(
                                        'alpha/beta-3',
                                        'alpha/beta-2',
                                        'alpha/beta-1',
                                    ),
                                    'current_node_path'           => 'alpha/beta-1',
                                    'current_node_ancestor_paths' => array('alpha'),
                                    'output'                      => array(
                                        'url'                 => '/alpha/beta-2/',
                                        'path'                => 'alpha/beta-2',
                                        'display_name'        => 'Beta 2',
                                        'template_data'       => Array(),
                                        'depth'               => 2,
                                        'is_first_child'      => false,
                                        'is_last_child'       => false,
                                        'is_current_root'     => false,
                                        'is_current'          => true,
                                        'is_current_ancestor' => false,
                                        'display_order'       => null,
                                    ),
                                ),
                            ),
                            array(
                                // make node with this data
                                'constructor_array'           => array(
                                    'path' => 'alpha/beta-3',
                                ),
                                'current_node_path'           => 'alpha/beta-1',
                                'current_node_ancestor_paths' => array('alpha'),
                                'expected'                    => array(
                                    'sorted_sibling_paths'        => array(
                                        'alpha/beta-3',
                                        'alpha/beta-2',
                                        'alpha/beta-1',
                                    ),
                                    'current_node_path'           => 'alpha/beta-1',
                                    'current_node_ancestor_paths' => array('alpha'),
                                    'output'                      => array(
                                        'url'                 => '/alpha/beta-3/',
                                        'path'                => 'alpha/beta-3',
                                        'display_name'        => 'Beta 3',
                                        'template_data'       => Array(),
                                        'depth'               => 2,
                                        'is_first_child'      => false,
                                        'is_last_child'       => true,
                                        'is_current_root'     => false,
                                        'is_current'          => false,
                                        'is_current_ancestor' => false,
                                        'display_order'       => 1,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     *
     * @dataProvider testFilterCallbackProvider
     * @group latest
     */
    public function testFilterCallback($testData){

        $testNodeArr = $testData['nodes'];
        $nodes = array();
        $c = new Collection();

        foreach($testNodeArr as $arr){
            $contructorArr = $arr['constructor_array'];
            $path = $contructorArr['path'];
            $n = new Node($contructorArr);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $testObj = $this;

        foreach($testNodeArr as $testNode){

            $path = $testNode['constructor_array']['path'];
            $expectedNode = $nodes[$path];
            $expectedArr = $testNode['expected'];
            $expectedCurrentNodePath = $expectedArr['current_node_path'];
            $expectedCurrentNode = null;
            if($expectedCurrentNodePath){
                $expectedCurrentNode = $nodes[$expectedCurrentNodePath];
            }
            $expectedCurrentNodeAncestorPaths = $expectedArr['current_node_ancestor_paths'];
            $expectedOutput = $expectedArr['output'];

            $expectedSortedSiblings = array();

            foreach($expectedArr['sorted_sibling_paths'] as $path){
                $expectedSortedSiblings[] = $nodes[$path];
            }

            // make sure passed params are correct
            $filter = function($node, $output, $collection, $sortedSiblings, $currentNode, $currentNodeAncestorPaths) use($expectedNode, $testObj, $c, $expectedSortedSiblings, $expectedCurrentNode, $expectedCurrentNodeAncestorPaths, $expectedOutput){
                    $testObj->assertEquals($expectedNode, $node, '!= Expected $node');
                    $testObj->assertEquals($expectedOutput, $output, '!= Expected $output');
                    $testObj->assertEquals($c, $collection, '!= Expected $collection');
                    $testObj->assertEquals($expectedCurrentNode, $currentNode, '!= Expected $currentNode');
                    $testObj->assertEquals($expectedCurrentNodeAncestorPaths, $currentNodeAncestorPaths, '!= Expected $currentNodeAncestorPaths');
                    $testObj->assertEquals($expectedSortedSiblings, $sortedSiblings);
                };

            $currentNodePath = $testNode['current_node_path'];
            $currentNode = null;
            if($currentNodePath){
                $currentNode = $nodes[$currentNodePath];
            }
            $currentNodeAncestorPaths = $testNode['current_node_ancestor_paths'];
            $expectedNode->prepareForTemplate($c, $expectedSortedSiblings, $currentNode, $currentNodeAncestorPaths, $filter);
        }
    }

}