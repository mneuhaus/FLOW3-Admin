<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(__DIR__ . '/../FLOW3.php');

/**
 *
 * @version $Id: FilesTest.php 2981 2009-08-03 15:57:18Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class PHPCRAdapterTest extends \F3\Testing\BaseTestCase {
    /**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 */
	protected $objectManager;

    /**
     * 
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function setUp() {
        $this->objectManager = \F3\Admin\Register::get("objectManager");
	}

    public function getAdapter(){
        $a = $this->objectManager->get("F3\Admin\Adapters\PHPCRAdapter");
        $a->init();
        return $a;
    }

    public function tearDown(){
    }
    
    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testCreateObjectWithTagModel() {
        $a = $this->getAdapter();
        $r = $a->createObject("F3\Admin\Domain\Model\Tag", array(
            "name" => "UnitTest"
        ));

        $this->assertTrue(empty($r["errors"]));

        $o = $r["object"];
        $this->assertEquals("UnitTest", $o->getName());

        $id = $a->getId($o);
        $this->assertNotNull($id);

        \F3\Admin\Register::set("tag",$o);
    }

    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testUpdateObjectWithTagModel() {
        $o = \F3\Admin\Register::get("tag");
        $a = $this->getAdapter();
        $id = $a->getId($o);
        
        $r = $a->updateObject("F3\Admin\Domain\Model\Tag", $id, array(
            "name" => "UnitTestUpdated"
        ));

        $this->assertTrue(empty($r["errors"]));

        $o = $r["object"];
        $this->assertEquals("UnitTestUpdated", $o->getName());
    }

    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testDeleteObjectWithTagModel() {
        $o = \F3\Admin\Register::get("tag");
        $a = $this->getAdapter();
        $id = $a->getId($o);

        $a->deleteObject("F3\Admin\Domain\Model\Tag", $id);

        $this->assertNull($a->getObject("F3\Admin\Domain\Model\Tag", $id));
    }

    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testCreateWidgetModel() {
        $a = $this->getAdapter();
        $r = $a->createObject("F3\Admin\Domain\Model\Tag", array(
            "name" => "FLOW3"
        ));
        $tagId = $a->getId($r["object"]);

        $r = $a->createObject("F3\Admin\Domain\Model\Widgets", array(
            "string" 	=> "testCreateWidgetModel",
            "integer" 	=> "13",
            "float" 	=> "12.12",
            "boolean" 	=> "true",
            "tag" 		=> $tagId,
            "tags" 		=> array($tagId)
        ));

        $this->assertTrue(empty($r["errors"]));

        $o = $r["object"];

        $this->assertEquals("testCreateWidgetModel", $o->getString());
        $this->assertEquals(13, $o->getInteger());
        $this->assertEquals(12.12, $o->getFloat());
        $this->assertEquals(true, $o->getBoolean());
        $this->assertEquals("FLOW3", $o->getTag()->getName());
        $this->assertEquals(1,$o->getTags()->count());

       #\F3\Admin\Register::add("cleanup",null,$tagId);
       #\F3\Admin\Register::add("cleanup",null,$a->getId($o));
    }

    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testCreateRelationModel() {
        $a = $this->getAdapter();
        $r = $a->createObject("F3\Admin\Domain\Model\Tag", array("name" => "FLOW3"));
        $tagId = $a->getId($r["object"]);
        $r = $a->createObject("F3\Admin\Domain\Model\Tag", array("name" => "Hello"));
        $tagId2 = $a->getId($r["object"]);
        
        $r = $a->createObject("F3\Admin\Domain\Model\Relation", array(
            "tag" 	=> $tagId,
            "tags"	=> array($tagId),
            "inlineWidget" => array(
                "string" 	=> "testCreateRelationModel",
                "integer" 	=> "13",
                "float" 	=> "12.12",
                "boolean" 	=> "true",
                "tag" 		=> $tagId2,
                "tags" 		=> array($tagId2)
            ),
            "inlineWidgets"  => array(
                array(
                    "string" 	=> "testCreateRelationModel",
                    "integer" 	=> "13",
                    "float" 	=> "12.12",
                    "boolean" 	=> "true",
                    "tag" 		=> $tagId2,
                    "tags" 		=> array($tagId2)
                ),
                array(
                    "string" 	=> "testCreateRelationModel",
                    "integer" 	=> "13",
                    "float" 	=> "12.12",
                    "boolean" 	=> "true",
                    "tag" 		=> $tagId2,
                    "tags" 		=> array($tagId2)
                )
            )
        ));

        $this->assertTrue(empty($r["errors"]));
        
        $o = $r["object"];
        
        \F3\Admin\Register::set("widget",$o);
    }

    /**
     * @test
     * @author Marc Neuhaus <mneuhaus@famelo.com
     */
    public function testUpdateRelationModel() {
        $a = $this->getAdapter();
        $relation = \F3\Admin\Register::get("widget");
        $relationId = $a->getId($relation);
        $tagId = $a->getId($relation->getTag());
        $tagId2 = $a->getId($relation->getInlineWidget()->getTag());
        $inlineWidgetId = $a->getId($relation->getInlineWidget());
        
        $r = $a->createObject("F3\Admin\Domain\Model\Relation", array(
            "__identity" => $relationId,
            "tag" 	=> $tagId,
            "tags"	=> array($tagId),
            "inlineWidget" => array(
                "__identity"=> $inlineWidgetId,
                "string" 	=> "testUpdateRelationModel",
                "integer" 	=> "5",
                "float" 	=> "0.12",
                "boolean" 	=> "false",
                "tag" 		=> $tagId2,
                "tags" 		=> array($tagId2)
            )
        ));

        $this->assertTrue(empty($r["errors"]));

        $o = $r["object"];
    }
}
?>