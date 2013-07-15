<?php

namespace Navinator;

class CollectionTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass(){

    }

    public static function tearDownAfterClass(){

    }

//    public static function callProtectedMethod($object, $method, array $args=array()) {
//        $class = new ReflectionClass(get_class($object));
//        $method = $class->getMethod($method);
//        $method->setAccessible(true);
//        return $method->invokeArgs($object, $args);
//    }

    /**
     * @covers Navinator\Collection::getNodeFromVar
     * @covers Navinator\Collection::getPathFromVar
     */
    public function testGetFromVar(){
        $nodeData = array(
            'alpha/beta/gamma/delta',
            'alpha/beta',
            'alpha',
            'alpha/beta/gamma',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($nodeData as $path){
            $node = $nodes[$path];
            $this->assertSame($c->getNodeFromVar($path), $nodes[$path]);
            $this->assertSame($c->getNodeFromVar($node), $nodes[$path]);

            $this->assertSame($c->getPathFromVar($path), $path);
            $this->assertSame($c->getPathFromVar($node), $path);
        }
    }

    /**
     * @group add
     * @covers Navinator\Collection::addNode
     * @covers Navinator\Collection::getNode
     * @covers Navinator\Collection::addNodeIfNotExists
     * @covers Navinator\Collection::setNodeDisplayOrder
     * @covers Navinator\Collection::autoSetNodeDisplayOrder
     *
     */
    public function testAdd(){

        // randomly ordered
        $nodeData = array(
            'alpha/beta-3' => array(
                'input'    => array(
                    'node_display_order'     => 2,
                    'override_display_order' => 3,
                ),
                'expected' => array(
                    'auto'                   => 1,
                    'node_display_order'     => 2,
                    'override_display_order' => 3,
                ),
            ),
            'alpha-3'      => array(
                'input'    => array(
                    'node_display_order'     => 3,
                    'override_display_order' => 1,
                ),
                'expected' => array(
                    'auto'                   => 1,
                    'node_display_order'     => 3,
                    'override_display_order' => 1,
                ),
            ),
            'alpha-2'      => array(
                'input'    => array(
                    'node_display_order'     => 2,
                    'override_display_order' => 1,
                ),
                'expected' => array(
                    'auto'                   => 2,
                    'node_display_order'     => 2,
                    'override_display_order' => 2,
                ),
            ),
            'alpha/beta-2' => array(
                'input'    => array(
                    'node_display_order'     => 5,
                    'override_display_order' => 1,
                ),
                'expected' => array(
                    'auto'                   => 2,
                    'node_display_order'     => 5,
                    'override_display_order' => 1,
                ),
            ),
            'alpha/beta-1' => array(
                'input'    => array(
                    'node_display_order'     => 2,
                    'override_display_order' => 5,
                ),
                'expected' => array(
                    'auto'                   => 3,
                    // should auto increment to next available
                    'node_display_order'     => 3,
                    'override_display_order' => 5,
                ),
            ),
            'alpha-1'      => array(
                'input'    => array(
                    'node_display_order'     => 1,
                    'override_display_order' => 1,
                ),
                'expected' => array(
                    'auto'                   => 3,
                    'node_display_order'     => 1,
                    'override_display_order' => 1,
                ),
            ),
        );

        $nodes = array();
        $c = new Collection();

        // test when autoset with no initial setting
        foreach($nodeData as $path => $expectedDisplayOrder){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['expected']['auto'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }

        // test when set to node
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $testData){
            $n = new Node($path);
            $n->display_order = $testData['input']['node_display_order'];
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['expected']['node_display_order'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }

        // test when overridden
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $testData){
            $n = new Node($path);
            $n->display_order = $testData['input']['node_display_order'];
            $nodes[$path] = $n;
            $displayOrderOverride = $testData['input']['override_display_order'];
            $c->addNode($n, $displayOrderOverride);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['expected']['override_display_order'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }

        // test when overridden without autoset = true
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $testData){
            $n = new Node($path);
            $n->display_order = $testData['input']['node_display_order'];
            $nodes[$path] = $n;
            $displayOrderOverride = $testData['input']['override_display_order'];
            $c->addNode($n, $displayOrderOverride, false);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['input']['override_display_order'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }

        // test when overridden using setNodeDisplayOrder
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $testData){
            $n = new Node($path);
            $n->display_order = $testData['input']['node_display_order'];
            $nodes[$path] = $n;
            $displayOrderOverride = $testData['input']['override_display_order'];
            $c->addNode($n);
            $c->setNodeDisplayOrder($n, $displayOrderOverride);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['expected']['override_display_order'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }

        // test when overridden using setNodeDisplayOrder without autoset = true
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $testData){
            $n = new Node($path);
            $n->display_order = $testData['input']['node_display_order'];
            $nodes[$path] = $n;
            $displayOrderOverride = $testData['input']['override_display_order'];
            $c->addNode($n);
            $c->setNodeDisplayOrder($n, $displayOrderOverride, false);
        }

        foreach($nodeData as $path => $testData){
            $node = $nodes[$path];
            $returnedDisplayOrder = $c->getNodeDisplayOrder($path);
            $expectedDisplayOrder = $testData['input']['override_display_order'];
            $this->assertSame($node, $c->getNode($path));
            $this->assertSame($node, $c[$path]);
            $this->assertSame($returnedDisplayOrder, $expectedDisplayOrder, sprintf('expected %s display order to be %s found %s instead', $path, $expectedDisplayOrder, $returnedDisplayOrder));
        }
    }

    /**
     * @covers Navinator\Collection::setNodeDisplayOrder
     */
    public function testSetNodeDisplayOrderException(){
        $a = new Node('alpha');
        $b = new Node('beta');
        $c = new Collection();
        $c->addNode($a);

        $this->setExpectedException(
            'Navinator\Exception', 'Attempting to set the collection display order override of a Node Object with the nodePath "beta" that was not found in collection Navinator\Collection.'
        );
        $c->setNodeDisplayOrder($b, 10);
    }

    /**
     * @covers Navinator\Collection::addNodeIfNotExists
     */
    public function testAddNodeIfNotExists(){
        $nodeData = array(
            'alpha',
        );

        $n = new Node('alpha');
        $c = new Collection();
        $c->addNodeIfNotExists($n);
        $this->assertSame($n, $c->getNode('alpha'));

        $c->addNodeIfNotExists($n);
        $this->assertSame($n, $c->getNode('alpha'));


        $b = new Node('alpha/beta');

        $c->addNodeIfNotExists($b);
        $this->assertSame($b, $c->getNode('alpha/beta'));

        $c->addNodeIfNotExists($b);
        $this->assertSame($b, $c->getNode('alpha/beta'));
    }

    /**
     * @covers Navinator\Collection::getNode
     */
    public function testGetNodeException(){
        $nodeData = array(
            'alpha/beta',
            'alpha',
        );

        $this->setExpectedException(
            'Navinator\Exception', 'A Node Object with the nodePath "alpha/beta/gamma" was not found in Navinator\Collection.'
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }
        $c->getNode('alpha/beta/gamma');
    }

    /**
     * @covers Navinator\Collection::addNodeIfNotExists
     */
    public function testAddIfNotExists(){
        $nodeData = array(
            'alpha/beta/gamma/delta',
            'alpha/beta',
            'alpha',
            'alpha/beta/gamma',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNodeIfNotExists($n);
        }

        $n = new Node('alpha/beta');

        $c->addNodeIfNotExists($n);

        foreach($nodeData as $path){
            $this->assertSame($c->getNode($path), $nodes[$path]);
        }
    }

    /**
     * @covers Navinator\Collection::addNode
     */
    public function testAddException(){
        $nodeData = array(
            'alpha/beta/gamma/delta',
            'alpha/beta',
            'alpha/beta',
            'alpha',
            'alpha/beta/gamma',
        );

        $this->setExpectedException(
            'Navinator\Exception', 'A Node Object with the nodePath "alpha/beta" is already assigned to this Navinator\Collection use addNodeIfNotExists(), removeNode() or setNode() to change it.'
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }
    }

    /**
     * @covers Navinator\Collection::setNode
     */
    public function testSetNode(){
        $nodeData = array(
            'alpha/beta/gamma/delta' => 1,
            'alpha/beta'             => 1,
            'alpha/beta-2'           => 2,
            'alpha/beta-3'           => 3,
            'alpha'                  => 1,
            'alpha/beta/gamma'       => 1,
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path => $displayOrder){
            $n = new Node($path);
            $n->display_order = $displayOrder;
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $n = new Node('alpha/beta');
        $c->setNode($n);
        $this->assertSame($c->getNode('alpha/beta'), $n);


        $n = new Node('alpha/beta/test');
        $c->setNode($n, 99);
        $this->assertSame($c->getNodeDisplayOrder($n), 99);
    }

    /**
     * @covers Navinator\Collection::offsetGet
     * @covers Navinator\Collection::offsetSet
     * @covers Navinator\Collection::offsetExists
     * @covers Navinator\Collection::offsetUnset
     */
    public function testOffset(){
        $nodeData = array(
            'alpha/beta/gamma/delta',
            'alpha/beta',
            'alpha',
            'alpha/beta/gamma',
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }


        $this->assertSame($c['alpha/beta'], $nodes['alpha/beta']);
        $this->assertTrue(isset($c['alpha/beta/gamma']));
        $this->assertEquals(4, count($c));
        unset($c['alpha/beta/gamma']);
        unset($nodes['alpha/beta/gamma']);
        $this->assertFalse(isset($c['alpha/beta/gamma']));
        $this->assertEquals(3, count($c));




        $n = new Node('blah');
        //add value by array index ignore the index
        $c['zzzzzz'] = $n;
        $this->assertSame($c['blah'], $n);
        $nodes['blah'] = $n;
        $this->assertSame($c->getNode('blah'), $n);


        foreach($c as $path => $node){
            $this->assertSame($nodes[$path], $node);
        }
    }

    /**
     * @covers Navinator\Collection::removeNode
     * @covers Navinator\Collection::offsetExists
     * @covers Navinator\Collection::hasNode
     */
    public function testRemoveNode(){
        $nodeData = array(
            'beta',
            'alpha',
            'alpha/beta',
            'delta',
            'gamma',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }
        foreach($nodes as $path => $node){
            $c->removeNode($node);
            $this->assertFalse($c->hasNode($path));
            $this->assertFalse(isset($c[$path]));
        }
    }

    /**
     * @covers Navinator\Collection::count
     */
    public function testCountNode(){
        $nodeData = array(
            'alpha',
            'beta',
            'gamma',
            'delta',
        );
        $nodes = array();
        $c = new Collection();
        $count = 0;
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $count++;
            $c->addNode($n);
            $this->assertEquals($count, count($c));
        }

        $this->assertEquals(4, count($c));

        $count = 4;
        foreach($nodes as $path => $node){
            $c->removeNode($node);
            $count--;
            $this->assertEquals($count, count($c));
        }
    }

    /**
     * @covers Navinator\Collection::sortNodeArray
     */
//    public function testSortNodeArray(){
//        $nodeData = array(
//            'beta' => 2,
//            'alpha' => 1,
//            'delta' => 4,
//            'gamma' => 3,
//
//        );
//        $nodes = array();
//        $c = new Collection();
//
//        foreach($nodeData as $path => $expectedDisplayOrder){
//            $n = new Node($path);
//            $n->display_order = $expectedDisplayOrder;
//            $nodes[$path] = $n;
//            $c->addNode($n);
//        }
//
//        foreach($nodeData as $path => $expectedDisplayOrder){
//            $node = $c->getNode($path);
//            $this->assertSame($node, $nodes[$path]);
//            $this->assertSame($c->getNodeDisplayOrder($path), $expectedDisplayOrder);
//            $this->assertSame($c->getNodeDisplayOrder($node), $expectedDisplayOrder);
//        }
//    }

    /**
     * @covers Navinator\Collection::getNodeDisplayOrders
     * @covers Navinator\Collection::getNodeDisplayOrder
     */
    public function testDisplayOrder(){
        $nodeData = array(
            'beta'  => 2,
            'alpha' => 1,
            'delta' => 4,
            'gamma' => 3,
        );
        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path => $expectedDisplayOrder){
            $n = new Node($path);
            $n->display_order = $expectedDisplayOrder;
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($nodeData as $path => $expectedDisplayOrder){
            $node = $c->getNode($path);
            $this->assertSame($node, $nodes[$path]);
            $this->assertSame($c->getNodeDisplayOrder($path), $expectedDisplayOrder);
            $this->assertSame($c->getNodeDisplayOrder($node), $expectedDisplayOrder);
        }
    }

    /**
     * @covers Navinator\Collection::call
     */
    public function testCall(){
        $nodeData = array(
            'alpha/beta/gamma/delta' => 4,
            'alpha/beta'             => 2,
            'alpha'                  => 1,
            'alpha/beta/gamma'       => 3,
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $expectedDepth){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $pathArray = $c->call('getPath');
        $this->assertEquals($pathArray, array_keys($nodeData));

        $depthArray = $c->call('getDepth');
        $this->assertEquals($depthArray, array_values($nodeData));
    }

    /**
     * @covers Navinator\Collection::getOrphanNodes
     */
    public function testGetOrphanNodes(){
        $nodeData = array(
            'alpha/beta',
            'alpha/beta/gama',
            'alpha/beta-2/gama',
            'test',
            'test/beta',
            'test/beta/gama',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $orphans = $c->getOrphanNodes();
        $expected = array(
            'alpha/beta'        => $nodes['alpha/beta'],
            'alpha/beta-2/gama' => $nodes['alpha/beta-2/gama'],
        );
        $this->assertEquals($expected, $orphans);
    }

    /**
     * @covers Navinator\Collection::validateNodes
     */
    public function testValidateNodes(){

        $nodeData = array(
            'alpha',
            'alpha/a',
            'alpha/a/b',
            'alpha/a/b/c',
            'beta',
            'beta/a',
            'beta/a/b',
            'beta/a/b/c',
            'gama',
            'gama/a',
            'gama/a/b',
            'gama/a/b/c',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $c->validateNodes();

        $nodeData = array(
            'alpha/beta',
            'alpha/beta/gama',
            'alpha/beta-2/gama',
            'test',
            'test/beta',
            'test/beta/gama',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $this->setExpectedException(
            'Navinator\Exception', "The following node(s) do not have a parent node in this collection : 'alpha/beta', 'alpha/beta-2/gama'"
        );

        $c->validateNodes();
    }

    /**
     * @covers Navinator\Collection::getRootNodes
     */
    public function testGetRootNodes(){
        $nodeData = array(
            'alpha',
            'alpha/a',
            'alpha/a/b',
            'alpha/a/b/c',
            'beta',
            'beta/a',
            'beta/a/b',
            'beta/a/b/c',
            'gama',
            'gama/a',
            'gama/a/b',
            'gama/a/b/c',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $orphans = $c->getRootNodes();
        $expected = array(
            'alpha' => $nodes['alpha'],
            'beta'  => $nodes['beta'],
            'gama'  => $nodes['gama'],
        );
        $this->assertEquals($expected, $orphans);
    }

    /**
     * @covers Navinator\Collection::sortNodeArray
     */
    public function testSortNodeArray(){
        $nodeData = array(
            'alpha-3'        => 3,
            'alpha-2'        => 2,
            'alpha-1'        => 1,
            'alpha-4'        => 4,
            'alpha-1/beta-3' => 3,
            'alpha-1/beta-1' => 1,
            'alpha-1/beta-2' => 2,
            'alpha-1/beta-4' => 4,
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path => $displayOrder){
            $n = new Node($path);
            $nodes[$path] = $n;

            $c->addNode($n, $displayOrder);
        }


        $rootNodes = $c->getRootNodes();
        $expected = array(
            $nodes['alpha-1'],
            $nodes['alpha-2'],
            $nodes['alpha-3'],
            $nodes['alpha-4'],
        );


        $result = $c->sortNodeArray($rootNodes);
        $this->assertEquals($expected, $result);

        // check for samse display orders
        $c->setNodeDisplayOrder($c->getNode('alpha-1/beta-4'), 3, false);

        $expected = array(
            $nodes['alpha-1/beta-1'],
            $nodes['alpha-1/beta-2'],
            $nodes['alpha-1/beta-4'],
            $nodes['alpha-1/beta-3'],
        );

        $result = $c->sortNodeArray($c->getNode('alpha-1')->getChildren($c));
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Navinator\Collection::buildFromArray
     */
    public function testBuildFromArray(){

        $testData = array(
            'alpha'            => array(
                'path'                   => 'alpha',
                'path_array'             => array('alpha'),
                'ancestor_path_array'    => array(),
                'parent_path'            => false,
                'depth'                  => 1,
                'display_name'           => 'Alpha',
                'display_order'          => 1,
                'expected_display_order' => 1,
                'last_path_segment'      => 'alpha',
            ),
            'alpha/beta'       => array(
                'path'                   => 'alpha/beta',
                'path_array'             => array('alpha', 'beta'),
                'ancestor_path_array'    => array('alpha'),
                'parent_path'            => 'alpha',
                'depth'                  => 2,
                'display_name'           => 'Beta',
                'display_order'          => 1,
                'expected_display_order' => 1,
                'last_path_segment'      => 'beta',
            ),
            'alpha/beta-2'     => array(
                'path'                   => 'alpha/beta-2',
                'path_array'             => array('alpha', 'beta-2'),
                'ancestor_path_array'    => array('alpha'),
                'parent_path'            => 'alpha',
                'depth'                  => 2,
                'display_name'           => 'Beta 2',
                'display_order'          => 1,
                'expected_display_order' => 2,
                'last_path_segment'      => 'beta-2',
            ),
            'alpha/beta/gamma' => array(
                'path'                   => 'alpha/beta/gamma',
                'path_array'             => array('alpha', 'beta', 'gamma'),
                'ancestor_path_array'    => array('alpha', 'alpha/beta'),
                'parent_path'            => 'alpha/beta',
                'depth'                  => 3,
                'display_name'           => 'Gamma',
                'display_order'          => 2,
                'expected_display_order' => 2,
                'last_path_segment'      => 'gamma',
            ),
            'alpha-2'          => array(
                'path'                   => 'alpha-2',
                'path_array'             => array('alpha-2'),
                'ancestor_path_array'    => array(),
                'parent_path'            => false,
                'depth'                  => 1,
                'display_name'           => 'Delta',
                'display_order'          => 1,
                'expected_display_order' => 2,
                'last_path_segment'      => 'delta',
            ),
        );

        $c = Collection::buildFromArray($testData);

        foreach($c as $path => $n){
            extract($testData[$path]);
            $this->assertEquals($path, $n->getPath());
            // test url
            $this->assertEquals('/' . $path . '/', $n->url, 'Cannot covert path to url correctly');

            $this->assertEquals($path_array, $n->getPathArray());
            $this->assertEquals($parent_path, $n->getParentPath());
            $this->assertEquals($depth, $n->getDepth());
            $this->assertEquals($ancestor_path_array, $n->getAncestorPaths());
            $this->assertEquals($display_name, $n->display_name);
            $this->assertEquals($expected_display_order, $c->getNodeDisplayOrder($n), $path . ' display order ex: ' . $expected_display_order . ' act: ' . $c->getNodeDisplayOrder($n));
        }

        $c = Collection::buildFromArray($testData, false);

        foreach($c as $path => $n){
            extract($testData[$path]);
            $this->assertEquals($path, $n->getPath());
            // test url
            $this->assertEquals('/' . $path . '/', $n->url, 'Cannot covert path to url correctly');

            $this->assertEquals($path_array, $n->getPathArray());
            $this->assertEquals($parent_path, $n->getParentPath());
            $this->assertEquals($depth, $n->getDepth());
            $this->assertEquals($ancestor_path_array, $n->getAncestorPaths());
            $this->assertEquals($display_name, $n->display_name);
            $this->assertEquals($display_order, $c->getNodeDisplayOrder($n), $path . ' display order ex: ' . $display_order . ' act: ' . $c->getNodeDisplayOrder($n));
        }
    }

    public function testIterator(){
        $nodeData = array(
            'alpha',
            'alpha/a',
            'alpha/a/b',
            'alpha/a/b/c',
        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        $c->rewind();
        $this->assertEquals($nodes['alpha'], $c->current());
        $this->assertEquals('alpha', $c->key());
        $this->assertEquals(true, $c->valid());
        $c->next();

        $this->assertEquals($nodes['alpha/a'], $c->current());
        $this->assertEquals('alpha/a', $c->key());
        $this->assertEquals(true, $c->valid());
        $c->next();

        $this->assertEquals($nodes['alpha/a/b'], $c->current());
        $this->assertEquals('alpha/a/b', $c->key());
        $this->assertEquals(true, $c->valid());
        $c->next();

        $this->assertEquals($nodes['alpha/a/b/c'], $c->current());
        $this->assertEquals('alpha/a/b/c', $c->key());
        $this->assertEquals(true, $c->valid());
        $c->next();

        $this->assertEquals(false, $c->valid());

        $c->rewind();
        $this->assertEquals($nodes['alpha'], $c->current());
        $this->assertEquals('alpha', $c->key());
        $this->assertEquals(true, $c->valid());
    }

    /**
     * @covers Navinator\Collection::getNodeMatchingUrl
     */
    public function testNodeMatchingUrl(){
        $nodeData = array(
            'alpha',

            'alpha-2/a',
            'alpha-2',
            'alpha-2/b',

            'alpha-3/a/b',
            'alpha-3/a',
            'alpha-3/a/b/c',
        );

        $testMatches = array(
            '/alpha/' => 'alpha',
            '/alpha-2/' => 'alpha-2',
            '/alpha-2/a/' => 'alpha-2/a',
            '/alpha-2/b/' => 'alpha-2/b',
            '/alpha-3/a/' => 'alpha-3/a',
            '/alpha-3/a/b/' => 'alpha-3/a/b',
            '/alpha-3/a/b/ddddd/' => 'alpha-3/a/b',
            '/alpha-3/a/b/' => 'alpha-3/a/b',
            '/alpha-3/a/b/c/' => 'alpha-3/a/b/c',

            '/alpha-3/a/b/c/ddddddd/' => 'alpha-3/a/b/c',

        );
        $nodes = array();
        $c = new Collection();
        foreach($nodeData as $path){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($testMatches as $url => $nodePath){
            $this->assertSame($nodes[$nodePath], $c->getNodeMatchingUrl($url));
        }

        foreach($testMatches as $url => $nodePath){
            $_SERVER['REQUEST_URI'] = $url;
            $this->assertSame($nodes[$nodePath], $c->getNodeMatchingUrl());
        }

    }
}