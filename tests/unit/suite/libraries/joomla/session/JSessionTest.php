<?php
require_once 'PHPUnit/Framework.php';


/**
 * Test class for JSession.
 * Generated by PHPUnit on 2009-10-26 at 22:57:34.
 */
class JSessionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JSession
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		include_once JPATH_BASE . '/libraries/joomla/session/session.php';
		include_once JPATH_BASE . '/libraries/joomla/application/application.php';

		$this->object = JSession::getInstance('none', array('expire' => 20, 'force_ssl' => true, 'name' => 'name', 'id' => 'id', 'security' => 'security'));
	}

	public function testGetId()
	{
		$session = $this->object;
		$this->assertEquals(session_id(), $session->getId(), 'Line: '.__LINE__.' Session id should be set');

		// Destroy and try again
		$session->destroy();
		$this->assertEquals(null, $session->getId(), 'Line: '.__LINE__.' Session id should be null');
		$session->restart();
	}

	public function testIsNew()
	{
		$session = $this->object;
		$session->restart();
		$this->assertTrue($session->isNew(), 'Line: '.__LINE__.' restarted session should be new');
		$session->set('session.counter', 2);
		$this->assertFalse($session->isNew(), 'Line: '.__LINE__.' session should not be new');

	}

	public function testGet()
	{
		$session = $this->object;
		$expected = $_SESSION['__default']['session.counter'];
		$this->assertEquals($expected, $session->get('session.counter'), 'Line: '.__LINE__.' values should match for active session');
		$session->destroy();
		$this->assertEquals(null, $session->get('session.counter'), 'Line: '.__LINE__.' Always return null for destroyed session');
		$session->restart();
	}

	public function testSet()
	{
		$session = $this->object;
		$session->clear('my.property', 'my_namespace');
		$this->assertNull($session->set('my.property', 'my_value', 'my_namespace'), 'Line: '.__LINE__.' Old value should be null');
		$this->assertEquals('my_value', $session->get('my.property', null, 'my_namespace'), 'Line: '.__LINE__.' New value should be set');
		$session->destroy();
		$this->assertNull($session->set('my.property', 'my_new_value', 'my_namespace'), 'Line: '.__LINE__.' Destroyed session set should return null');
		$this->assertFalse(isset($_SESSION['__my_namespace']['my.property']), 'Line: '.__LINE__.' Destroyed session set should not write to $_SESSION');
		$session->restart();
	}

	public function testHas()
	{
		$session = $this->object;
		$session->set('my.property', 'my_value', 'my_namespace');
		$this->assertTrue($session->has('my.property', 'my_namespace'), 'Line: '.__LINE__.' Property should exist');
		$session->destroy();
		$this->assertEquals(null, $session->has('my.property', 'my_namespace'), 'Line: '.__LINE__.' Property should not exist for destroyed session');
		$session->restart();
	}

	public function testClear()
	{
		$session = $this->object;
		$session->destroy();
		$this->assertNull($session->clear('my.property', 'my_namespace'), 'Line: '.__LINE__.' Always return null for non-active session');
		$session->restart();
		// Set a property
		$session->set('my.property', 'my_testclear_value', 'my_namespace');
		// Make sure it is set correctly
		$this->assertEquals('my_testclear_value', $session->get('my.property', null, 'my_namespace'));
		// Clear and test result
		$this->assertEquals('my_testclear_value', $session->clear('my.property', 'my_namespace'), 'Line: '.__LINE__.' Old value should be returned');
		$this->assertNull($session->get('my.property', null, 'my_namespace'), 'Line: '.__LINE__.' Property should now be null after clear');
	}

	public function testDestroy()
	{
		$session = $this->object;
		// Set up cookie for test
		$_COOKIE[session_name()]= 'test cookie';

		$this->assertEquals('active', $session->getState(), 'Line: '.__LINE__.' Starting state is active');
		$this->assertTrue(count($_SESSION) > 0, 'Line: '.__LINE__.' $_SESSION has content');
		$this->assertTrue($session->destroy());
		$this->assertEquals('destroyed', $session->getState(), 'Line: '.__LINE__.' State is now destroyed');
		$this->assertTrue(count($_SESSION) == 0, 'Line: '.__LINE__.' $_SESSION has no content');
		$session->restart();
	}

	public function testRestart()
	{
		// Test starting state
		$session = $this->object;
		$this->assertEquals('active', $session->getState(), 'Line: '.__LINE__.' Starting state should be active');
		$oldId = $session->getId();
		// Restart and test
		$this->assertTrue($session->restart(), 'Line: '.__LINE__.' Restart should succeed');
		$this->assertTrue($oldId != $session->getId(), 'Line: '.__LINE__.' Restart should change id');
		$this->assertEquals(1, $session->get('session.counter'), 'Line: '.__LINE__.' Counter should be reset');
	}

	public function testFork()
	{
		$session = $this->object;
		$session->set('my.property', 'my_testfork_value', 'my_namespace');
		$oldId = $session->getId();
		$this->assertTrue($session->fork(), 'Line: '.__LINE__.' fork() should succeed');
		$this->assertNotEquals($oldId, $session->getId(), 'Line: '.__LINE__.' id should have changed');
		$this->assertNotEquals('my_testfork_value', $session->get('my.property', null, 'my_namespace'), 'Line: '.__LINE__.' Property should be preserved');

		// Test with destroyed session
		$session->destroy();
		$this->assertFalse($session->fork(), 'Line: '.__LINE__.' fork() should fail for destroyed session');
		$session->restart();
	}
}
