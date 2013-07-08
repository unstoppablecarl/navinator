<?php


namespace Navinator;

class CollectionTest extends \PHPUnit_Framework_TestCase{

    public static function setUpBeforeClass(){

    }

    public static function tearDownAfterClass(){

    }

    public function testStartsWithProvider(){
        return array(
            array('foo', 'foo-bar', true),
            array('00', 'foo-bar', false),
            array('', 'foo-bar', true),
            array('-', '-bar', true),
        );
    }

    /**
     *
     * @dataProvider testStartsWithProvider
     */
    public function testStartsWith($prefix, $string, $outcome){
        $this->assertEquals($outcome, \Navinator\Collection::strStartsWith($prefix, $string));
    }

    public function testEndsWithProvider(){
        return array(
            array('bar', 'foo-bar', true),
            array('ba', 'foo-bar', false),
            array('', 'foo-bar', true),
            array('-', 'foo-', true),
        );
    }

    /**
     *
     * @dataProvider testEndsWithProvider
     */
    public function testEndsWith($suffix, $string, $outcome){
        $this->assertEquals($outcome, \Navinator\Collection::strEndsWith($suffix, $string));
    }

    public function testRemoveFromBeginningProvider(){
        return array(
            array('foo', 'foo-bar', '-bar'),
            array('', 'foo-bar', 'foo-bar'),
            array('foo-bar', 'foo-bar', ''),
            array('foo-', 'foo-bar', 'bar'),
        );
    }

    /**
     *
     * @dataProvider testRemoveFromBeginningProvider
     */
    public function testRemoveFromBeginning($prefix, $string, $outcome){
        $this->assertEquals($outcome, \Navinator\Collection::strRemoveFromBeginning($prefix, $string));
    }

    public function testRemoveFromEndProvider(){
        return array(
            array('bar', 'foo-bar', 'foo-'),
            array('', 'foo-bar', 'foo-bar'),
            array('foo-bar', 'foo-bar', ''),
            array('-bar', 'foo-bar', 'foo'),
        );
    }

    /**
     *
     * @dataProvider testRemoveFromEndProvider
     */
    public function testRemoveFromEnd($suffix, $string, $outcome){
        $this->assertEquals($outcome, \Navinator\Collection::strRemoveFromEnd($suffix, $string));
    }

    public function testAdd(){
        $nodeData = array(
            'alpha/beta/gamma/delta' => 1,
            'alpha/beta' => 1,
            'alpha/beta-2' => 2,
            'alpha' => 1,
            'alpha/beta/gamma' => 1,
            'alpha-3' => 2,
            'alpha-2' => 3,
        );

        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path => $expectedDisplayOrder){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n);
        }

        foreach($nodeData as $path => $expectedDisplayOrder){
            $this->assertSame($c->getNode($path), $nodes[$path]);
            $this->assertSame($c->getNodeDisplayOrder($path), $expectedDisplayOrder);
        }
    }

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

        $n = new Node('alpha/beta');

        $c->setNode($n);
        $this->assertSame($c->getNode('alpha/beta'), $n);

    }

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
        $this->assertFalse(isset($c['alpha/beta/gamma']));
        $this->assertEquals(3, count($c));




        $n = new Node('blah');
        //add value by array index ignore the index
        $c['zzzzzz'] = $n;
        $this->assertSame($c['blah'], $n);
        $this->assertSame($c->getNode('blah'), $n);

        foreach($c as $path => $node){
            $this->assertSame($nodes[$path], $node);

        }

    }

    public function testDisplayOrder(){
        $nodeData = array(
            'beta' => 2,
            'alpha' => 1,
            'delta' => 4,
            'gamma' => 3,

        );


        $nodes = array();
        $c = new Collection();

        foreach($nodeData as $path => $expectedDisplayOrder){
            $n = new Node($path);
            $nodes[$path] = $n;
            $c->addNode($n, $expectedDisplayOrder);
        }
//        error_log(print_r($c, true));

        foreach($nodeData as $path => $expectedDisplayOrder){
            $this->assertSame($c->getNode($path), $nodes[$path]);
            $this->assertSame($c->getNodeDisplayOrder($path), $expectedDisplayOrder);
        }

        $templateOutput = $c->prepareForNavTemplate();
        error_log(print_r($templateOutput, true));

//        foreach($templateOutput as $index => $item){
//            $this->assertSame($nodes[$index]->getPath(), $item['path']);
//        }



    }


}