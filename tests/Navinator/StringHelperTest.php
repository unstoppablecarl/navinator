<?php

namespace Navinator;

class StringHelperTest extends \PHPUnit_Framework_TestCase{

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
        $this->assertEquals($outcome, StringHelper::strStartsWith($prefix, $string));
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
        $this->assertEquals($outcome, StringHelper::strEndsWith($suffix, $string));
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
        $this->assertEquals($outcome, StringHelper::strRemoveFromBeginning($prefix, $string));
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
        $this->assertEquals($outcome, StringHelper::strRemoveFromEnd($suffix, $string));
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

        $this->assertEquals($expected, StringHelper::humanizeString($str));
    }


}