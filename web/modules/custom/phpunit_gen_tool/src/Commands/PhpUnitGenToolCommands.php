<?php

namespace Drupal\phpunit_gen_tool\Commands;

use Drush\Commands\DrushCommands;

/**
 * Class for generate Test using Drush.
 */
class PhpUnitGenToolCommands extends DrushCommands {

  /**
   * Generate PHPUnit test for a given file.
   *
   * @param string $file_path
   *   The path to the file for which the test will be generated.
   *
   * @command phpunitgen:generate
   */
  public function generateTest($file_path) {
    $start_time = microtime(TRUE);
    $this->output()->writeln("<info>Generation is started...</info>");

    $drupal_root = \Drupal::root();
    $cleaned_file_path = preg_replace('/^\.\//', '', $file_path);
    $cleaned_file_path = preg_replace('/^.*?\//', '', $cleaned_file_path);
    $relative_path = $drupal_root . '/' . $cleaned_file_path;
    if (!file_exists($relative_path)) {
      $this->logger()->error(dt('File does not exist: @file_path', ['@file_path' => $relative_path]));
      return;
    }

    // Extract the class name and namespace from the file.
    $class_info = $this->getClassInfo($relative_path);

    if (!$class_info) {
      $this->logger()->error(dt('Unable to extract class information from the file: @file_path', ['@file_path' => $relative_path]));
      return;
    }

    $test_code = $this->generateTestCode($class_info['namespace'], $class_info['class_name'], $relative_path);

    $test_file_path = $this->getTestFilePath($class_info['class_name']);
    file_put_contents($test_file_path, $test_code);
    $end_time = microtime(TRUE);
    $execution_time = number_format(($end_time - $start_time), 3);
    $this->logger()->success(dt('Test generated successfully: @test_file_path', ['@test_file_path' => $test_file_path]));
    $this->output()->writeln("<info>Generation is finished!</info>");
    $this->output()->writeln("<info>1 source(s) identified</info>");
    $this->output()->writeln("<info>1 success(es)</info>");
    $this->output()->writeln("<info>0 warning(s)</info>");
    $this->output()->writeln("<info>0 error(s)</info>");
    $this->output()->writeln("<info>Execution time: $execution_time s</info>");
    $this->output()->writeln("<info>Memory usage: " . memory_get_peak_usage() / 1024 / 1024 . " MB</info>");
  }

  /**
   * Extract the class name and namespace from the file.
   *
   * @param string $file_path
   *   The path to the file.
   *
   * @return array|false
   *   An associative array with 'namespace' and 'class_name' keys, or FALSE on failure.
   */
  protected function getClassInfo($file_path) {
    $contents = file_get_contents($file_path);

    $namespace = '';
    $class_name = '';

    if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
      $namespace = $matches[1];
    }

    if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
      $class_name = $matches[1];
    }

    if ($namespace && $class_name) {
      return ['namespace' => $namespace, 'class_name' => $class_name];
    }

    return FALSE;
  }

  /**
   * Generate the PHPUnit test code.
   *
   * @param string $namespace
   *   The namespace of the class to be tested.
   * @param string $class_name
   *   The name of the class to be tested.
   * @param string $file_path
   *   The path to the file.
   *
   * @return string
   *   The generated test code.
   */
  protected function generateTestCode($namespace, $class_name, $file_path) {
    $test_namespace = $namespace . '\\Tests';
    $test_class_name = $class_name . 'Test';
    $test_methods = $this->generateTestMethods($file_path);

    return <<<EOT
<?php

namespace $test_namespace;

use PHPUnit\Framework\TestCase;

class $test_class_name extends TestCase {

  public function setUp(): void {
    parent::setUp();
    // Setup code here.
  }

$test_methods

}

EOT;
  }

  /**
   * Generate PHPUnit test methods for all functions/methods in the file.
   *
   * @param string $file_path
   *   The path to the file.
   *
   * @return string
   *   The generated test methods.
   */
  protected function generateTestMethods($file_path) {
    $contents = file_get_contents($file_path);
    preg_match_all('/(?:public|protected|private|static)?\s*function\s*(\w+)\s*\(/', $contents, $matches);

    $test_methods = '';
    foreach ($matches[1] as $method_name) {
      if ($method_name !== '__construct' && strpos($method_name, '__') !== 0) {
        $test_method_name = lcfirst($method_name);
        $test_methods .= $this->generateTestMethod($test_method_name);
      }
    }

    return $test_methods;
  }

  /**
   * Generate a PHPUnit test method for a function/method.
   *
   * @param string $method_name
   *   The name of the function/method.
   *
   * @return string
   *   The generated test method.
   */
  protected function generateTestMethod($method_name) {
    $testMethodName = ucfirst($method_name);
    return <<<EOT

  public function test$testMethodName() {
    // Implement test for $method_name here.
    \$this->assertTrue(true);
  }
EOT;
  }

  /**
   * Determine the test file path based on the original file path.
   *
   * @param string $class_name
   *   The name of the class to be tested.
   *
   * @return string
   *   The test file path.
   */
  protected function getTestFilePath($class_name) {
    $module_path = \Drupal::service('extension.list.module')->getPath('phpunit_gen_tool');

    $test_directory = $module_path . '/tests/src/Unit';
    if (!is_dir($test_directory)) {
      mkdir($test_directory, 0777, TRUE);
    }

    return $test_directory . '/' . $class_name . 'Test.php';
  }

}
