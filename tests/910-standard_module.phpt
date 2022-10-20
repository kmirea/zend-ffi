--TEST--
Check/Test Standard Module
--SKIPIF--
<?php if (!extension_loaded("ffi")) print "skip"; ?>
--FILE--
<?php
require 'vendor/autoload.php';

final class SimpleCountersModule extends \StandardModule
{
    protected ?string $module_version = '0.4';
    protected ?string $global_type = 'unsigned int[10]';
    protected bool $m_startup = true;

    public function module_startup(int $type, int $module_number): int
    {
        echo 'module_startup' . \PHP_EOL;
        return \ZE::SUCCESS;
    }

    public function global_startup(\FFI\CData $memory): void
    {
        if (\PHP_ZTS) {
            \tsrmls_activate();
        }

        echo 'global_startup' . \PHP_EOL;
        \FFI::memset($this->get_globals(), 0, $this->globals_size());
    }
}

$module = new SimpleCountersModule();
if (!$module->is_registered()) {
    $module->register();
    $module->startup();
}

$data = $module->get_globals();
$data[0] = 5;
$data[9] = 15;
var_dump($data);
--EXPECTF--
global_startup
module_startup
object(FFI\CData:uint32_t[10])#16 (10) {
  [0]=>
  int(5)
  [1]=>
  int(0)
  [2]=>
  int(0)
  [3]=>
  int(0)
  [4]=>
  int(0)
  [5]=>
  int(0)
  [6]=>
  int(0)
  [7]=>
  int(0)
  [8]=>
  int(0)
  [9]=>
  int(15)
}
