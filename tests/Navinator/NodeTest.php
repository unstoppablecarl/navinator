<?php

namespace Navinator;

class NodeTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass(){

    }

    public static function tearDownAfterClass(){

    }

    static public function testConstructProvider(){
        return array(
            array(
                'alpha',
                array('alpha'),
                array(),
                false,
                1,
                'alpha'
            ),
            array(
                'alpha/beta',
                array('alpha', 'beta'),
                array('alpha'),
                'alpha',
                2,
                'beta'
            ),
            array(
                'alpha/beta/gamma',
                array('alpha', 'beta', 'gamma'),
                array('alpha', 'alpha/beta'),
                'alpha/beta',
                3,
                'gamma'
            ),
            array(
                'alpha/beta/gamma/delta',
                array('alpha', 'beta', 'gamma', 'delta'),
                array('alpha', 'alpha/beta', 'alpha/beta/gamma'),
                'alpha/beta/gamma',
                4,
                'delta'
            )
        );
    }

    /**
     *
     * @dataProvider testConstructProvider
     */
    public function testConstruct($path, $pathArray, $ancestorPathArray, $parentPath, $depth, $displayName){
        $n = new Node($path);
        $this->assertEquals($path, $n->getPath());
        $this->assertEquals($pathArray, $n->getPathArray());
        $this->assertEquals($parentPath, $n->getParentPath());
        $this->assertEquals($depth, $n->getDepth());
        $this->assertEquals($ancestorPathArray, $n->getAncestorPaths());
        $this->assertEquals($displayName, $n->display_name);
    }

    public function testConstructFromArrayProvider(){
        return array(
            //test item
            array(
                // constructor array
                array(
                    'path'          => 'alpha',
                    'display_name'  => 'alpha',
                    'url'           => 'alpha',
                    'template_data' => array('foo'),
                ),
                // expected values
                'alpha',
                'alpha',
                'alpha',
                array('foo'),
            ),
            array(
                // constructor array
                array(
                    'path'          => 'alpha/beta',
                    'display_name'  => 'beta',
                    'url'           => '/foo/bar/',
                    'template_data' => array('foo', 'bar'),
                ),
                // expected values
                'alpha/beta',
                'beta',
                '/foo/bar/',
                array('foo', 'bar'),
            )
        );
    }

    /**
     *
     * @dataProvider testConstructFromArrayProvider
     */
    public function testConstructFromArray($arrData, $path, $displayName, $url, $templateData){
        $n = new Node($arrData);

        $this->assertEquals($path, $n->getPath());
        $this->assertEquals($url, $n->url);
        $this->assertEquals($templateData, $n->template_data);
        $this->assertEquals($displayName, $n->display_name);
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

    public function testGetNodeNameProvider(){
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
     * @dataProvider testGetNodeNameProvider
     */
    public function testGetNodeName($path, $nodeName){
        $n = new Node($path);
        $this->assertEquals($nodeName, $n->getNodeName());
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
            'alpha-2',
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

        $expected = array();
        $expected = array(
            'url'                 => '/alpha/',
            'path'                => 'alpha',
            'display_name'        => 'alpha',
            'template_data'       => Array(),
            'depth'               => 1,
            'is_first_child'      => false,
            'is_last_child'       => false,
            'is_current_root'     => false,
            'is_current'          => false,
            'is_current_ancestor' => false,
            'children'            => Array(),
        );
        $expected['children'][0] = array(
            'url'                 => '/alpha/beta/',
            'path'                => 'alpha/beta',
            'display_name'        => 'beta',
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
            'display_name'        => 'beta-2',
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
            'display_name'        => 'gamma',
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
            'display_name'        => 'gamma-2',
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
            'display_name'        => 'no-siblings',
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
            'display_name'        => 'delta',
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
            'display_name'        => 'delta-2',
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

        $this->assertEquals($expected, $nodes['alpha']->prepareForTemplate($c));
    }

}