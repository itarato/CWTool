<?php
/**
 * @file
 */
use CW\Test\TestCase;
use CW\Util\ArrayUtil;
use CW\Util\Functional;

/**
 * Class FunctionalTest
 */
class FunctionalTest extends TestCase {

  /**
   * Test memoization.
   */
  public function testMemoize() {
    $f = function () {
      static $count = 0;

      $count++;

      return $count;
    };

    $this->assertEquals($f(), 1);
    $this->assertEquals($f(), 2);
    $this->assertEquals($f(), 3);

    $f_cached = Functional::memoize($f);
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
    $this->assertEquals(4, $f_cached());
  }

  public function testApply() {
    $tenTimes = function (&$x) {
      return $x * 10;
    };
    $arr = $arrOrig = [1, 2, 3];
    Functional::apply($arr, $tenTimes);

    foreach ($arrOrig as $idx => $elem) {
      $this->assertEquals($tenTimes($elem), $arr[$idx]);
    }
  }

  public function testAny() {
    $pass = [1, 2, 3, 4, 100];
    $noPass = [1, 2, 3, 4];

    $this->assertTrue(Functional::any($pass, function ($item) {return $item > 10;}));
    $this->assertFalse(Functional::any($noPass, function ($item) {return $item > 10;}));
  }

  public function testAll() {
    $pass = [1, 2, 3, 4, 100];
    $noPass = [1, 2, 3, 4];

    $this->assertFalse(Functional::all($pass, function ($item) {return $item < 10;}));
    $this->assertTrue(Functional::all($noPass, function ($item) {return $item < 10;}));
  }

  public function testFirstPass() {
    $list = [1, 2, 'foo', 4, 'bar', 6];
    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    });
    $this->assertEquals($string, 'foo');
  }

  public function testFirstNotPass() {
    $list = [1, 2, 3, 4, 5, 6];

    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    }, 'boo');
    $this->assertEquals($string, 'boo');

    $string = Functional::first($list, function ($item) {
      return is_string($item) ? $item : NULL;
    });
    $this->assertEquals($string, NULL);
  }

  public function testDot() {
    $addOne = function ($val) { return $val + 1; };
    $double = function ($val) { return $val * 2; };

    $this->assertEquals(10, Functional::dot([], 10));
    $this->assertEquals(11, Functional::dot([$addOne], 10));
    $this->assertEquals(22, Functional::dot([$addOne, $double], 10));
    $this->assertEquals(21, Functional::dot([$double, $addOne], 10));
  }

  public function testTimes() {
    $count = 0;

    Functional::times(0, function () use (&$count) { $count += 1; });
    $this->assertEquals(0, $count);

    Functional::times(10, function () use (&$count) { $count += 1; });
    $this->assertEquals(10, $count);
  }

  public function testWalk() {
    $count = 0;
    Functional::walk([2, 10], function ($item) use (&$count) { $count += $item; });
    $this->assertEquals(12, $count);
  }

  public function testSelfCallFnMethodWithoutArgument() {
    $nums = ArrayUtil::range(-2, 2, function ($n) { return new FunctionalTest__SimpleClassDummy($n); });
    $this->assertEquals([
      -2 => -4,
      -1 => -2,
      0 => 0,
      1 => 2,
      2 => 4,
    ], array_map(Functional::selfCallFn('getDouble'), $nums));
  }

  public function testSelfCallFnProperty() {
    $names = ['Steve', 'John', 'Phil'];
    $objects = [(object) ['name' => 'Steve'], (object) ['name' => 'John'], (object) ['name' => 'Phil']];
    $namesExtracted = array_map(Functional::selfCallFn('name'), $objects);
    $this->assertEquals($namesExtracted, $names);
  }

  public function testSelfCallFnMethodWithArgument() {
    $nums = ArrayUtil::range(-2, 2, function ($n) { return new FunctionalTest__SimpleClassDummy($n); });
    $this->assertEquals([
      -2 => -20,
      -1 => -10,
      0 => 0,
      1 => 10,
      2 => 20,
    ], array_map(Functional::selfCallFn('getMultiple', 10), $nums));
  }

  public function testSelfCallFnMethodArray() {
    $names = ['Steve', 'John', 'Phil'];
    $arr = [
      ['name' => 'Steve', 'bar' => '<S>', 12 => 3],
      ['name' => 'John', 'bar' => '<J>'],
      ['name' => 'Phil', 'bar' => '<P>', 0 => []],
    ];
    $this->assertEquals(array_map(Functional::selfCallFn('name'), $arr), $names);
  }

  public function testSelfCallFnMissingAttribute() {
    $fn = Functional::selfCallFn('name');

    $obj = new stdClass();
    $this->assertEquals($fn($obj), $obj);

    $str = "123";
    $this->assertEquals($fn($str), $str);
  }

  public function testIdentity() {
    $fnID = Functional::id();
    $this->assertEquals(0, $fnID(0));
    $this->assertEquals(1, $fnID(1));
    $this->assertEquals(123, $fnID(123));
    $this->assertEquals("", $fnID(""));
    $this->assertEquals("hello", $fnID("hello"));
    $this->assertEquals([1, 'foo' => 'bar'], $fnID([1, 'foo' => 'bar']));

    $o = (object) ['foo' => [1, 2, 3], 'bar' => 'baz'];
    $this->assertEquals($o, $fnID($o));
  }

  public function testCurry() {
    $add = function ($a, $b) {
      return $a + $b;
    };

    $addOrig = Functional::curry($add);
    $this->assertEquals(10, $addOrig(2, 8));

    $add10 = Functional::curry($add, 10);
    $this->assertEquals(14, $add10(4));

    $staticSum = Functional::curry($add, 10, 12);
    $this->assertEquals(22, $staticSum());
  }

  public function testNotSimple() {
    $fnNotString = Functional::fnNot('is_string');

    $this->assertTrue($fnNotString(1));
    $this->assertTrue($fnNotString(NULL));
    $this->assertTrue($fnNotString(FALSE));
    $this->assertTrue($fnNotString(TRUE));

    $this->assertFalse($fnNotString("hello"));
    $this->assertFalse($fnNotString(''));
  }

  public function testNotComplex() {
    $fnIsMod = function ($mod, $n) {
      return $n % $mod === 0;
    };

    $fnNotIsMod10 = Functional::fnNot($fnIsMod, 10);

    $this->assertEquals(TRUE, $fnNotIsMod10(1));
    $this->assertEquals(TRUE, $fnNotIsMod10(3));
    $this->assertEquals(FALSE, $fnNotIsMod10(10));
    $this->assertEquals(TRUE, $fnNotIsMod10(1043));
    $this->assertEquals(FALSE, $fnNotIsMod10(35298320));
  }

  public function testWalkKeyValue() {
    $arr = [
      1 => 2,
      3 => 4,
      5 => 6,
    ];

    $count = 0;

    Functional::walkKeyValue($arr, function ($key, $item) use (&$count) {
      $count += $key + $item * 10;
    });

    $this->assertEquals(129, $count);
  }

}

class FunctionalTest__SimpleClassDummy {
  public $n;
  public function __construct($n) { $this->n = $n; }
  public function getMultiple($mul) { return $this->n * $mul; }
  public function getDouble() { return $this->n * 2; }
}
